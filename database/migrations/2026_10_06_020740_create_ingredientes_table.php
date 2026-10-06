<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_medida_id')->constrained('unidades_medida')->restrictOnDelete();
            $table->string('nome', 120)->unique();
            $table->decimal('estoque_atual', 14, 3)->default(0);
            $table->decimal('estoque_minimo', 14, 3)->default(0);
            $table->unsignedInteger('custo_medio_centavos')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->index(['ativo', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredientes');
    }
};
