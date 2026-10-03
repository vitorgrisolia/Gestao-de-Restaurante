<?php

namespace Database\Factories;

use App\Models\Comanda;
use App\Models\Pedido;
use App\Models\User;
use App\StatusPedido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comanda_id' => Comanda::factory(),
            'criado_por_id' => User::factory(),
            'status' => StatusPedido::Rascunho,
            'observacao' => fake()->optional()->sentence(),
            'enviado_em' => null,
            'iniciado_em' => null,
            'pronto_em' => null,
            'entregue_em' => null,
            'cancelado_em' => null,
        ];
    }

    public function enviado(): static
    {
        return $this->state(fn (): array => [
            'status' => StatusPedido::Enviado,
            'enviado_em' => now(),
        ]);
    }
}
