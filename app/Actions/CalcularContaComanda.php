<?php

namespace App\Actions;

use App\Models\Comanda;
use App\Models\PedidoItem;
use App\StatusItemPedido;

class CalcularContaComanda
{
    /** @return array{subtotal:int,servico:int,couvert:int,desconto:int,acrescimo:int,total:int,pago:int,saldo:int} */
    public function handle(Comanda $comanda): array
    {
        $itens = $comanda->relationLoaded('pedidos')
            ? $comanda->pedidos->flatMap(fn ($pedido) => $pedido->itens)->filter(fn (PedidoItem $item): bool => $item->status !== StatusItemPedido::Cancelado)
            : PedidoItem::query()->whereHas('pedido', fn ($q) => $q->where('comanda_id', $comanda->id))->where('status', '!=', StatusItemPedido::Cancelado)->get();
        $subtotal = (int) $itens->sum(fn (PedidoItem $item): int => $item->subtotalCentavos());
        $servico = (int) round($subtotal * $comanda->servico_percentual / 100);
        $couvert = $comanda->couvert_por_pessoa_centavos * $comanda->quantidade_pessoas;
        $total = max(0, $subtotal + $servico + $couvert + $comanda->acrescimo_centavos - $comanda->desconto_centavos);
        $pago = (int) ($comanda->relationLoaded('pagamentos')
            ? $comanda->pagamentos->whereNull('estornado_em')->sum('valor_centavos')
            : $comanda->pagamentos()->whereNull('estornado_em')->sum('valor_centavos'));

        return ['subtotal' => $subtotal, 'servico' => $servico, 'couvert' => $couvert, 'desconto' => $comanda->desconto_centavos, 'acrescimo' => $comanda->acrescimo_centavos, 'total' => $total, 'pago' => $pago, 'saldo' => max(0, $total - $pago)];
    }
}
