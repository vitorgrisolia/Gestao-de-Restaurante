<?php

namespace App\Console\Commands;

use App\Actions\ConsultarBackupVerificado;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class VerificarSaude extends Command
{
    protected $signature = 'sistema:verificar';

    protected $description = 'Verifica banco, armazenamento, fila e atualização dos backups';

    public function handle(ConsultarBackupVerificado $consultarBackup): int
    {
        $falhas = [];
        try {
            DB::select('select 1');
        } catch (Throwable) {
            $falhas[] = 'Banco de dados indisponível';
        }

        if (! is_writable(storage_path())) {
            $falhas[] = 'Diretório storage sem permissão de escrita';
        }

        if (config('database.default') === 'sqlite') {
            $ultimo = $consultarBackup->handle();
            if (! $ultimo || filemtime($ultimo) < now()->subDay()->timestamp) {
                $falhas[] = 'Nenhum backup criado nas últimas 24 horas';
            }
        }

        if (config('database.default') !== 'sqlite') {
            $falhas[] = 'Backup externo não verificado. Configure a verificação do banco utilizado.';
        }

        $falhasFila = DB::getSchemaBuilder()->hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        if ($falhasFila > 0) {
            $falhas[] = "{$falhasFila} trabalho(s) com falha na fila";
        }

        if ($falhas !== []) {
            foreach ($falhas as $falha) {
                $this->error($falha);
            }

            return self::FAILURE;
        }

        $this->info('Todos os componentes verificados estão saudáveis.');

        return self::SUCCESS;
    }
}
