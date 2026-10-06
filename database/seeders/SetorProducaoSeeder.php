<?php

namespace Database\Seeders;

use App\Models\SetorProducao;
use Illuminate\Database\Seeder;

class SetorProducaoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nome' => 'Cozinha', 'descricao' => 'Pratos, entradas e sobremesas', 'ordem' => 1],
            ['nome' => 'Bar', 'descricao' => 'Bebidas e coquetéis', 'ordem' => 2],
        ] as $setor) {
            SetorProducao::query()->updateOrCreate(
                ['nome' => $setor['nome']],
                [...$setor, 'ativo' => true],
            );
        }
    }
}
