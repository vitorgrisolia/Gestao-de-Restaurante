<?php

namespace Database\Factories;

use App\Models\SetorProducao;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SetorProducao> */
class SetorProducaoFactory extends Factory
{
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
