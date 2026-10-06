<?php

namespace App\Http\Controllers;

use App\Models\MovimentacaoEstoque;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioEstoqueController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->papel->podeOperarEstoque() ?? false, 403);
        $movimentos = MovimentacaoEstoque::query()->with(['ingrediente.unidadeMedida', 'usuario'])->when($request->date('inicio'), fn ($q, $data) => $q->whereDate('registrada_em', '>=', $data))->when($request->date('fim'), fn ($q, $data) => $q->whereDate('registrada_em', '<=', $data))->oldest('registrada_em')->get();

        return response()->streamDownload(function () use ($movimentos): void {
            $arquivo = fopen('php://output', 'w');
            assert(is_resource($arquivo));
            fwrite($arquivo, "\xEF\xBB\xBF");
            fputcsv($arquivo, ['Data', 'Ingrediente', 'Tipo', 'Quantidade', 'Unidade', 'Saldo anterior', 'Saldo posterior', 'Custo unitário', 'Responsável', 'Motivo'], ';');
            foreach ($movimentos as $m) {
                fputcsv($arquivo, [Carbon::parse($m->registrada_em)->format('d/m/Y H:i'), $m->ingrediente->nome, $m->tipo, $m->quantidade, $m->ingrediente->unidadeMedida->sigla, $m->saldo_anterior, $m->saldo_posterior, number_format($m->custo_unitario_centavos / 100, 2, ',', '.'), $m->usuario->name, $m->motivo], ';');
            }fclose($arquivo);
        }, 'relatorio-estoque-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
