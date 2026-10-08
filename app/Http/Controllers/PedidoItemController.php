<?php

namespace App\Http\Controllers;

use App\Actions\AtualizarItemPedido;
use App\Actions\RemoverItemPedido;
use App\Http\Requests\DestroyPedidoItemRequest;
use App\Http\Requests\UpdatePedidoItemRequest;
use App\Models\PedidoItem;
use Illuminate\Http\RedirectResponse;

class PedidoItemController extends Controller
{
    public function update(
        UpdatePedidoItemRequest $request,
        PedidoItem $pedidoItem,
        AtualizarItemPedido $atualizarItemPedido,
    ): RedirectResponse {
        $atualizarItemPedido->handle(
            $pedidoItem,
            $request->integer('quantidade'),
            $request->observacaoItem(),
            $request->dadosVenda(),
        );

        return back()->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(
        DestroyPedidoItemRequest $request,
        PedidoItem $pedidoItem,
        RemoverItemPedido $removerItemPedido,
    ): RedirectResponse {
        $removerItemPedido->handle($pedidoItem);

        return back()->with('success', 'Item removido com sucesso.');
    }
}
