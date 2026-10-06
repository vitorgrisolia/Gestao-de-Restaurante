<?php

namespace Database\Factories;

use App\Models\UnidadeMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UnidadeMedida> */
class UnidadeMedidaFactory extends Factory
{
    public function definition(): array
    {
        $nome = fake()->unique()->word();

        return ['nome' => ucfirst($nome), 'sigla' => strtolower(substr($nome, 0, 5)).fake()->unique()->numberBetween(1, 999), 'casas_decimais' => 3, 'ativa' => true];
    }
}
