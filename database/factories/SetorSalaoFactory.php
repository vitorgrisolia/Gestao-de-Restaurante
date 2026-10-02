<?php

namespace Database\Factories;

use App\Models\SetorSalao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SetorSalao>
 */
class SetorSalaoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(2, true),
            'descricao' => fake()->optional()->sentence(),
            'ativo' => true,
            'ordem' => fake()->numberBetween(0, 20),
        ];
    }
}
