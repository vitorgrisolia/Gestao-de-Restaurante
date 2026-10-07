<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;

class CriarBackup extends Command
{
    protected $signature = 'backup:criar {--manter=15 : Quantidade de arquivos que serão mantidos}';

    protected $description = 'Cria uma cópia verificada do banco SQLite e remove backups antigos';

    public function handle(): int
    {
        if (config('database.default') !== 'sqlite') {
            $this->error('Este comando local suporta SQLite. Configure o backup nativo do banco em produção.');

            return self::FAILURE;
        } $origem = (string) config('database.connections.sqlite.database');
        if (! is_file($origem)) {
            throw new RuntimeException('Arquivo do banco de dados não encontrado.');
        }$diretorio = storage_path('app/private/backups');
        File::ensureDirectoryExists($diretorio);
        $destino = $diretorio.'/restaurante-'.now()->format('Ymd-His').'.sqlite';
        $conexao = new PDO('sqlite:'.$origem, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $conexao->exec('PRAGMA busy_timeout = 5000');
        $conexao->exec('VACUUM INTO '.$conexao->quote($destino));
        $verificacao = new PDO('sqlite:'.$destino);
        $resultado = $verificacao->query('PRAGMA integrity_check');
        if ($resultado === false || $resultado->fetchColumn() !== 'ok') {
            throw new RuntimeException('O backup falhou na verificação de integridade.');
        }
        $checksum = hash_file('sha256', $destino);
        File::put($destino.'.json', json_encode(['arquivo' => basename($destino), 'sha256' => $checksum, 'criado_em' => now()->toIso8601String()], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        $manter = max(1, (int) $this->option('manter'));
        collect(File::glob($diretorio.'/*.sqlite'))->sortDesc()->slice($manter)->each(function (string $arquivo): void {
            File::delete([$arquivo, $arquivo.'.json']);
        });
        $this->info("Backup criado: {$destino}");

        return self::SUCCESS;
    }
}
