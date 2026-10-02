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

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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
        ]);
    }
}
