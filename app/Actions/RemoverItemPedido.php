<?php

namespace App\Actions;

use App\Models\Comanda;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\StatusComanda;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoverItemPedido
{
    public function handle(PedidoItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $itemBloqueado = PedidoItem::query()->lockForUpdate()->findOrFail($item->id);
            $pedido = Pedido::query()->lockForUpdate()->findOrFail($itemBloqueado->pedido_id);
            $comanda = Comanda::query()->lockForUpdate()->findOrFail($pedido->comanda_id);

            if (
                ! $comanda->ativa
                || $comanda->status !== StatusComanda::Aberta
                || $pedido->status !== StatusPedido::Rascunho
                || $itemBloqueado->status !== StatusItemPedido::Rascunho
            ) {
                throw ValidationException::withMessages([
                    'item' => 'Somente itens de pedidos em rascunho podem ser removidos.',
                ]);
            }

            $itemBloqueado->delete();

            if (! $pedido->itens()->exists()) {
                $pedido->delete();
            }
        });
    }
}
