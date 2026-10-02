<?php

namespace Database\Seeders;

use App\Models\SetorSalao;
use Illuminate\Database\Seeder;

class SetorSalaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['nome' => 'Salão principal', 'descricao' => 'Área interna', 'ordem' => 1],
            ['nome' => 'Varanda', 'descricao' => 'Área externa coberta', 'ordem' => 2],
        ])->each(fn (array $setor) => SetorSalao::query()->firstOrCreate(
            ['nome' => $setor['nome']],
            $setor,
        ));
    }
}
