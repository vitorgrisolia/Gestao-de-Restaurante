<?php

namespace Database\Factories;

use App\Models\Caixa;
use App\Models\MovimentoCaixa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MovimentoCaixa> */ class MovimentoCaixaFactory extends Factory
{
    public function definition(): array
    {
        return ['caixa_id' => Caixa::factory(), 'usuario_id' => User::factory(), 'pagamento_id' => null, 'tipo' => 'abertura', 'valor_centavos' => 10_000, 'descricao' => 'Abertura do caixa', 'registrado_em' => now()];
    }
}
