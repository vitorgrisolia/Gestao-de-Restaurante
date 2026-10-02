<?php

namespace Database\Seeders;

use App\Models\Mesa;
use App\Models\SetorSalao;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SetorSalao::query()->each(function (SetorSalao $setor): void {
            foreach (range(1, 4) as $numero) {
                Mesa::query()->firstOrCreate(
                    ['setor_salao_id' => $setor->id, 'numero' => (string) $numero],
                    ['capacidade' => $numero === 4 ? 6 : 4],
                );
            }
        });
    }
}
