<?php

namespace App\Actions;

use App\Models\FichaTecnicaItem;
use App\Models\Ingrediente;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;

class BaixarEstoqueDoPedido
{
    public function __construct(private MovimentarEstoque $movimentarEstoque) {}

    public function handle(Pedido $pedido): void
    {
        $usuario = User::query()->findOrFail($pedido->criado_por_id);
        $itens = $pedido->itens()->get(['id', 'item_cardapio_id', 'quantidade']);

        foreach ($itens as $itemPedido) {
            $this->baixarItem($itemPedido, $usuario);
        }
    }

    private function baixarItem(PedidoItem $itemPedido, User $usuario): void
    {
        $fichas = FichaTecnicaItem::query()
            ->where('item_cardapio_id', $itemPedido->item_cardapio_id)
            ->with('ingrediente')
            ->get();

        foreach ($fichas as $ficha) {
            $ingrediente = $ficha->ingrediente;

            if (! $ingrediente instanceof Ingrediente) {
                continue;
            }

            $quantidade = (float) $ficha->quantidade * $itemPedido->quantidade;
            $this->movimentarEstoque->handle(
                $ingrediente,
                $usuario,
                'baixa',
                -$quantidade,
                "Baixa automática do pedido #{$itemPedido->pedido_id}",
                pedidoItemId: $itemPedido->id,
                chaveIdempotencia: "pedido-item:{$itemPedido->id}:ingrediente:{$ingrediente->id}",
            );
        }
    }
}
