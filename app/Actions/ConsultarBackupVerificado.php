<?php

namespace App\Actions;

use Illuminate\Support\Facades\File;
use PDO;
use Throwable;

class ConsultarBackupVerificado
{
    public function handle(): ?string
    {
        foreach (collect(File::glob(storage_path('app/private/backups/*.sqlite')))->sortDesc() as $arquivo) {
            try {
                if (! is_file($arquivo.'.json')) {
                    continue;
                }
                $metadados = json_decode(File::get($arquivo.'.json'), true, flags: JSON_THROW_ON_ERROR);
                $checksum = hash_file('sha256', $arquivo);
                if (is_array($metadados) && is_string($metadados['sha256'] ?? null)
                    && is_string($checksum) && hash_equals($metadados['sha256'], $checksum)) {
                    $conexao = new PDO('sqlite:'.$arquivo, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    $resultado = $conexao->query('PRAGMA integrity_check');
                    if ($resultado !== false && $resultado->fetchColumn() === 'ok') {
                        return $arquivo;
                    }
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }
}
