<?php

namespace Tests\Feature;

use App\Console\Commands\RestaurarBackup;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cria_backup_com_checksum_verificavel(): void
    {
        $storageAnterior = app()->storagePath();
        app()->useStoragePath(sys_get_temp_dir().'/backup-test-'.bin2hex(random_bytes(8)));
        $diretorio = storage_path('app/private/backups');
        $origem = storage_path('framework/testing/backup-source.sqlite');
        File::ensureDirectoryExists(dirname($origem));
        File::delete($origem);
        $conexao = new \PDO('sqlite:'.$origem);
        $conexao->exec('CREATE TABLE teste (id INTEGER PRIMARY KEY, nome TEXT)');
        $conexao->exec("INSERT INTO teste (nome) VALUES ('Restaurante')");
        config()->set('database.connections.sqlite.database', $origem);
        $this->artisan('backup:criar', ['--manter' => 2])->assertSuccessful();
        $arquivo = collect(File::glob($diretorio.'/*.sqlite'))->first();
        $this->assertNotNull($arquivo);
        $this->assertFileExists($arquivo.'.json');
        $dados = json_decode(File::get($arquivo.'.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(hash_file('sha256', $arquivo), $dados['sha256']);
        $copia = new \PDO('sqlite:'.$arquivo);
        $this->assertSame('Restaurante', $copia->query('SELECT nome FROM teste')->fetchColumn());
        app()->useStoragePath($storageAnterior);
    }

    public function test_restauracao_preserva_manutencao_preexistente(): void
    {
        $storageAnterior = app()->storagePath();
        app()->useStoragePath(sys_get_temp_dir().'/restore-test-'.bin2hex(random_bytes(8)));
        $diretorio = storage_path('app/private/backups');
        File::ensureDirectoryExists($diretorio);
        $backup = $diretorio.'/teste.sqlite';
        File::put($backup, 'backup verificado');
        File::put($backup.'.json', json_encode(['sha256' => hash_file('sha256', $backup)], JSON_THROW_ON_ERROR));
        $destino = storage_path('original.sqlite');
        config()->set('database.connections.sqlite.database', $destino);
        $this->mock(MaintenanceMode::class)
            ->shouldReceive('active')->once()->andReturn(true);
        Artisan::shouldReceive('call')->never();

        $comando = app(RestaurarBackup::class);
        $comando->setLaravel(app());
        $this->assertSame(0, $comando->run(
            new ArrayInput(['arquivo' => 'teste.sqlite', '--force' => true]),
            new BufferedOutput,
        ));
        $this->assertSame('backup verificado', File::get($destino));
        app()->useStoragePath($storageAnterior);
    }

    public function test_restauracao_exige_confirmacao_explicita(): void
    {
        $this->artisan('backup:restaurar', ['arquivo' => 'inexistente.sqlite'])->assertFailed()->expectsOutputToContain('Use --force');
    }
}
