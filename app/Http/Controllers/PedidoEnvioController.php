<?php

namespace App\Http\Controllers;

use App\Actions\EnviarPedido;
use App\Http\Requests\StorePedidoEnvioRequest;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;

class PedidoEnvioController extends Controller
{
    public function store(StorePedidoEnvioRequest $request, Pedido $pedido, EnviarPedido $enviarPedido): RedirectResponse
    {
        $enviarPedido->handle($pedido);

        return back()->with('success', 'Pedido enviado à produção.');
    }
}
