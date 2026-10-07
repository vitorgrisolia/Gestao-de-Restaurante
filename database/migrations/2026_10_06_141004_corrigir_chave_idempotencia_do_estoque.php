<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacoes_estoque', function (Blueprint $table): void {
            $table->string('chave_idempotencia', 150)->nullable()->change();
        });
        Schema::table('unidades_medida', function (Blueprint $table): void {
            $table->string('nome', 80)->change();
        });
    }

    public function down(): void
    {
        // Valores existentes podem exceder os tipos antigos; mantenha o formato compatível.
    }
};
