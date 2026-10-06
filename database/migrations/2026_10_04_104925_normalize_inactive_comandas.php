<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('comandas')
            ->where('ativa', false)
            ->update(['ativa' => null]);
    }

    public function down(): void
    {
        // Uma reversão não é segura: várias comandas encerradas podem pertencer à mesma mesa.
    }
};
