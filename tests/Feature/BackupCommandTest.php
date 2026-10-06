<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cria_backup_com_checksum_verificavel(): void
    {
        $diretorio = storage_path('app/private/backups');
        $origem = storage_path('framework/testing/backup-source.sqlite');
        File::ensureDirectoryExists(dirname($origem));
        File::put($origem, 'banco de teste');
        config()->set('database.connections.sqlite.database', $origem);
        File::deleteDirectory($diretorio);
        $this->artisan('backup:criar', ['--manter' => 2])->assertSuccessful();
        $arquivo = collect(File::glob($diretorio.'/*.sqlite'))->first();
        $this->assertNotNull($arquivo);
        $this->assertFileExists($arquivo.'.json');
        $dados = json_decode(File::get($arquivo.'.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(hash_file('sha256', $arquivo), $dados['sha256']);
    }

    public function test_restauracao_exige_confirmacao_explicita(): void
    {
        $this->artisan('backup:restaurar', ['arquivo' => 'inexistente.sqlite'])->assertFailed()->expectsOutputToContain('Use --force');
    }
}
