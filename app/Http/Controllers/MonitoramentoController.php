<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class MonitoramentoController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->papel->podeAdministrarCadastros() ?? false, 403);
        $banco = true;
        try {
            DB::select('select 1');
        } catch (Throwable) {
            $banco = false;
        }

        $usaSqlite = config('database.default') === 'sqlite';
        $ultimo = $usaSqlite
            ? collect(File::glob(storage_path('app/private/backups/*.sqlite')))->sortDesc()->first()
            : null;
        $dataUltimoBackup = $ultimo ? filemtime($ultimo) : false;
        $dados = [
            'banco' => $banco ? 'ok' : 'falha',
            'armazenamento' => is_writable(storage_path()) ? 'ok' : 'falha',
            'fila_falhas' => DB::getSchemaBuilder()->hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
            'backup' => $usaSqlite ? 'local' : 'gerenciado_pelo_provedor',
            'ultimo_backup' => $dataUltimoBackup !== false ? date(DATE_ATOM, $dataUltimoBackup) : null,
            'ambiente' => app()->environment(),
        ];
        $backupRecente = ! $usaSqlite || ($dataUltimoBackup !== false && $dataUltimoBackup >= now()->subDay()->timestamp);
        $dados['backup_recente'] = $usaSqlite ? $backupRecente : null;
        $saudavel = $dados['banco'] === 'ok' && $dados['armazenamento'] === 'ok' && $dados['fila_falhas'] === 0 && $backupRecente;

        return response()->json(['status' => $saudavel ? 'ok' : 'atencao', 'componentes' => $dados], $saudavel ? 200 : 503);
    }
}
