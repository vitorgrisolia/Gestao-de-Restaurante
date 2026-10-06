<?php

namespace Database\Factories;

use App\Models\Ingrediente;
use App\Models\UnidadeMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ingrediente> */
class IngredienteFactory extends Factory
{
    public function definition(): array
    {
        return ['unidade_medida_id' => UnidadeMedida::factory(), 'nome' => fake()->unique()->words(2, true), 'estoque_atual' => fake()->randomFloat(3, 5, 100), 'estoque_minimo' => fake()->randomFloat(3, 0, 5), 'custo_medio_centavos' => fake()->numberBetween(100, 5000), 'ativo' => true];
    }
}
