<?php

namespace Database\Seeders;

use App\Models\CategoriaCardapio;
use Illuminate\Database\Seeder;

class CategoriaCardapioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['nome' => 'Entradas', 'descricao' => 'Porções e pratos para iniciar a refeição', 'ordem' => 1],
            ['nome' => 'Pratos', 'descricao' => 'Pratos principais do cardápio', 'ordem' => 2],
            ['nome' => 'Bebidas', 'descricao' => 'Bebidas geladas e quentes', 'ordem' => 3],
            ['nome' => 'Sobremesas', 'descricao' => 'Doces e sobremesas', 'ordem' => 4],
        ])->each(fn (array $categoria) => CategoriaCardapio::query()->updateOrCreate(
            ['nome' => $categoria['nome']],
            [...$categoria, 'ativa' => true],
        ));
    }
}
