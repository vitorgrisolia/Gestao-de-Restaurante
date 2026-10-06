<?php

namespace Database\Factories;

use App\Models\ImpressaoProducao;
use App\Models\Pedido;
use App\Models\SetorProducao;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ImpressaoProducao> */
class ImpressaoProducaoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(),
            'setor_producao_id' => SetorProducao::factory(),
            'solicitada_por_id' => User::factory(),
            'chave_idempotencia' => fake()->uuid(),
            'sequencia' => 1,
            'tipo' => 'inicial',
            'solicitada_em' => now(),
            'impressa_em' => null,
        ];
    }
}
