<?php

namespace App\Http\Controllers;

use App\Models\ImpressaoProducao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CupomTermicoController extends Controller
{
    public function show(Request $request, ImpressaoProducao $impressao): View
    {
        abort_unless($request->user()?->papel->podeOperarProducao() ?? false, 403);
        $impressao->load(['pedido.comanda.mesa', 'pedido.itens' => fn ($q) => $q->where('setor_producao_id', $impressao->setor_producao_id), 'setorProducao', 'solicitadaPor']);

        return view('impressoes.cupom-termico', ['impressao' => $impressao]);
    }

    public function update(Request $request, ImpressaoProducao $impressao): RedirectResponse
    {
        abort_unless($request->user()?->papel->podeOperarProducao() ?? false, 403);
        $impressao->update(['impressa_em' => $impressao->impressa_em ?? now()]);

        return back()->with('success', 'Impressão confirmada.');
    }
}
