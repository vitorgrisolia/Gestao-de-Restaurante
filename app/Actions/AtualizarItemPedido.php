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

class AtualizarItemPedido
{
    public function __construct(private readonly CalcularPrecoItemPedido $calcularPreco) {}

    /** @param array{peso_gramas?: int|null, cobrar_excesso_carne?: bool|null, adicional_carne_centavos?: int} $venda */
    public function handle(PedidoItem $item, int $quantidade, ?string $observacao, array $venda = []): PedidoItem
    {
        return DB::transaction(function () use ($item, $quantidade, $observacao, $venda): PedidoItem {
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
                    'item' => 'Somente itens de pedidos em rascunho podem ser alterados.',
                ]);
            }

            $dadosVenda = array_replace([
                'peso_gramas' => $itemBloqueado->peso_gramas,
                'cobrar_excesso_carne' => $itemBloqueado->cobrar_excesso_carne,
                'adicional_carne_centavos' => $itemBloqueado->adicional_carne_centavos,
            ], $venda);
            $precoReferencia = $itemBloqueado->preco_referencia_centavos ?? $itemBloqueado->preco_unitario_centavos;
            $preco = $this->calcularPreco->handle($itemBloqueado->tipo_venda, $precoReferencia, $dadosVenda['peso_gramas'], $itemBloqueado->permite_excesso_carne, $dadosVenda['cobrar_excesso_carne'], $dadosVenda['adicional_carne_centavos']);

            $itemBloqueado->update([
                ...$dadosVenda,
                'cobrar_excesso_carne' => $dadosVenda['cobrar_excesso_carne'] ?? false,
                'preco_unitario_centavos' => $preco,
                'quantidade' => $quantidade,
                'observacao' => $observacao,
            ]);

            return $itemBloqueado;
        });
    }
}
