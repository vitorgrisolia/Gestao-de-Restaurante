<?php

namespace Database\Factories;

use App\FormaPagamento;
use App\Models\Comanda;
use App\Models\Pagamento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pagamento>
 */
class PagamentoFactory extends Factory
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
            'recebido_por_id' => User::factory(),
            'forma' => fake()->randomElement(FormaPagamento::cases()),
            'valor_centavos' => fake()->numberBetween(500, 50_000),
            'pago_em' => now(),
        ];
    }
}
