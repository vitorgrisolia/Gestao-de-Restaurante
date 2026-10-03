<?php

namespace App\Http\Controllers;

use App\Actions\CriarPedido;
use App\Http\Requests\StorePedidoRequest;
use App\Models\Comanda;
use Illuminate\Http\RedirectResponse;

class PedidoController extends Controller
{
    public function store(
        StorePedidoRequest $request,
        Comanda $comanda,
        CriarPedido $criarPedido,
    ): RedirectResponse {
        $criarPedido->handle(
            $comanda,
            $request->user(),
            $request->itens(),
            $request->observacaoPedido(),
        );

        return back()->with('success', 'Pedido registrado com sucesso.');
    }
}
