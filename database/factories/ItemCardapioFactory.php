<?php

namespace Database\Factories;

use App\Models\CategoriaCardapio;
use App\Models\ItemCardapio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemCardapio>
 */
class ItemCardapioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_cardapio_id' => CategoriaCardapio::factory(),
            'nome' => fake()->unique()->words(3, true),
            'descricao' => fake()->optional()->sentence(),
            'preco_centavos' => fake()->numberBetween(500, 15_000),
            'imagem' => null,
            'disponivel' => true,
            'ordem' => fake()->numberBetween(0, 20),
        ];
    }

    public function indisponivel(): static
    {
        return $this->state(fn (): array => [
            'disponivel' => false,
        ]);
    }
}
