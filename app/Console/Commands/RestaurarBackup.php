<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use RuntimeException;

class RestaurarBackup extends Command
{
    protected $signature = 'backup:restaurar {arquivo : Nome do arquivo dentro do diretório de backups} {--force : Confirma a substituição do banco atual}';

    protected $description = 'Restaura um backup SQLite após conferir seu checksum';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('Use --force depois de confirmar o arquivo. O banco atual será substituído.');

            return self::FAILURE;
        }if (config('database.default') !== 'sqlite') {
            $this->error('Restauração local disponível somente para SQLite.');

            return self::FAILURE;
        }$nome = basename((string) $this->argument('arquivo'));
        $backup = storage_path('app/private/backups/'.$nome);
        $metadados = $backup.'.json';
        if (! is_file($backup) || ! is_file($metadados)) {
            throw new RuntimeException('Backup ou arquivo de verificação não encontrado.');
        }$dados = json_decode(File::get($metadados), true, flags: JSON_THROW_ON_ERROR);
        $checksumAtual = hash_file('sha256', $backup);
        if ($checksumAtual === false || ! hash_equals((string) $dados['sha256'], $checksumAtual)) {
            throw new RuntimeException('Checksum inválido. O backup pode estar corrompido.');
        }$destino = (string) config('database.connections.sqlite.database');
        Artisan::call('down');
        try {
            if (! copy($backup, $destino)) {
                throw new RuntimeException('Não foi possível restaurar o banco.');
            }
        } finally {
            Artisan::call('up');
        }$this->info('Backup restaurado e aplicação reativada.');

        return self::SUCCESS;
    }
}
