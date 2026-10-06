<?php

namespace App\Http\Controllers;

use App\Actions\AtualizarStatusItemProducao;
use App\Http\Requests\UpdatePedidoItemStatusRequest;
use App\Models\PedidoItem;
use Illuminate\Http\RedirectResponse;

class PedidoItemStatusController extends Controller
{
    public function update(UpdatePedidoItemStatusRequest $request, PedidoItem $pedidoItem, AtualizarStatusItemProducao $atualizarStatus): RedirectResponse
    {
        $atualizarStatus->handle($pedidoItem, $request->status(), $request->user());

        return back()->with('success', 'Status do item atualizado.');
    }
}
