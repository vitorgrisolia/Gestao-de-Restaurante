<?php

namespace Database\Factories;

use App\Models\ItemCardapio;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\StatusItemPedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PedidoItem>
 */
class PedidoItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(),
            'item_cardapio_id' => ItemCardapio::factory(),
            'nome_item' => fake()->words(3, true),
            'quantidade' => fake()->numberBetween(1, 4),
            'preco_unitario_centavos' => fake()->numberBetween(500, 15_000),
            'observacao' => fake()->optional()->sentence(),
            'status' => StatusItemPedido::Rascunho,
            'enviado_em' => null,
            'iniciado_em' => null,
            'pronto_em' => null,
            'entregue_em' => null,
            'cancelado_por_id' => null,
            'motivo_cancelamento' => null,
            'cancelado_em' => null,
        ];
    }

    public function enviado(): static
    {
        return $this->state(fn (): array => [
            'status' => StatusItemPedido::Enviado,
            'enviado_em' => now(),
        ]);
    }
}
