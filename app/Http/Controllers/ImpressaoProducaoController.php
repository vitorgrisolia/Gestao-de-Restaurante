<?php

namespace App\Http\Controllers;

use App\Actions\SolicitarImpressaoProducao;
use App\Http\Requests\StoreReimpressaoProducaoRequest;
use App\Models\Pedido;
use App\Models\SetorProducao;
use Illuminate\Http\RedirectResponse;

class ImpressaoProducaoController extends Controller
{
    public function store(StoreReimpressaoProducaoRequest $request, Pedido $pedido, SetorProducao $setorProducao, SolicitarImpressaoProducao $solicitarImpressao): RedirectResponse
    {
        $solicitarImpressao->handle($pedido, $setorProducao, $request->user(), $request->chaveIdempotencia());

        return back()->with('success', 'Impressão registrada. Use a impressão do navegador para concluir.');
    }
}
