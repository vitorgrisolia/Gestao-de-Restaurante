<?php

namespace Database\Factories;

use App\EstadoMesa;
use App\Models\Mesa;
use App\Models\SetorSalao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mesa>
 */
class MesaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'setor_salao_id' => SetorSalao::factory(),
            'numero' => fake()->unique()->numberBetween(1, 200),
            'capacidade' => fake()->numberBetween(2, 8),
            'estado' => EstadoMesa::Livre,
            'ativa' => true,
        ];
    }
}
