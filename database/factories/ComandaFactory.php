<?php

namespace Database\Factories;

use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\User;
use App\StatusComanda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comanda>
 */
class ComandaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mesa_id' => Mesa::factory(),
            'aberta_por_id' => User::factory(),
            'quantidade_pessoas' => fake()->numberBetween(1, 8),
            'status' => StatusComanda::Aberta,
            'ativa' => true,
            'aberta_em' => now(),
            'fechada_em' => null,
        ];
    }
}
