<?php

namespace Database\Factories;

use App\Models\Caixa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Caixa> */ class CaixaFactory extends Factory
{
    public function definition(): array
    {
        return ['aberto_por_id' => User::factory(), 'fechado_por_id' => null, 'aberto' => true, 'valor_abertura_centavos' => 10_000, 'valor_esperado_centavos' => null, 'valor_informado_centavos' => null, 'diferenca_centavos' => null, 'aberto_em' => now(), 'fechado_em' => null];
    }
}
