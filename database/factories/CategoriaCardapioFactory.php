<?php

namespace Database\Factories;

use App\Models\CategoriaCardapio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoriaCardapio>
 */
class CategoriaCardapioFactory extends Factory
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
            'ativa' => true,
            'ordem' => fake()->numberBetween(0, 20),
        ];
    }

    public function inativa(): static
    {
        return $this->state(fn (): array => [
            'ativa' => false,
        ]);
    }
}
