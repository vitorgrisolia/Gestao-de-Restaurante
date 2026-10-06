<?php

namespace App\Http\Controllers;

use App\Actions\CancelarPedido;
use App\Http\Requests\StorePedidoCancelamentoRequest;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;

class PedidoCancelamentoController extends Controller
{
    public function store(StorePedidoCancelamentoRequest $request, Pedido $pedido, CancelarPedido $cancelarPedido): RedirectResponse
    {
        $cancelarPedido->handle($pedido, $request->user(), $request->motivo());

        return back()->with('success', 'Pedido cancelado com auditoria registrada.');
    }
}
