<?php

namespace Database\Seeders;

use App\Models\User;
use App\PapelUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('Os dados de demonstração não podem ser carregados em produção.');
        }

        User::query()->updateOrCreate([
            'email' => 'proprietario@restaurante.test',
        ], [
            'name' => 'Proprietário',
            'password' => Hash::make('password'),
            'papel' => PapelUsuario::Proprietario,
            'email_verified_at' => now(),
        ]);

        $this->call([
            SetorSalaoSeeder::class,
            MesaSeeder::class,
            CategoriaCardapioSeeder::class,
            SetorProducaoSeeder::class,
            ItemCardapioSeeder::class,
        ]);
    }
}
