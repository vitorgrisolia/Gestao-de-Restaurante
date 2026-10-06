<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impressoes_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->restrictOnDelete();
            $table->foreignId('setor_producao_id')->constrained('setores_producao')->restrictOnDelete();
            $table->foreignId('solicitada_por_id')->constrained('users')->restrictOnDelete();
            $table->uuid('chave_idempotencia')->unique();
            $table->unsignedSmallInteger('sequencia');
            $table->string('tipo', 20);
            $table->timestamp('solicitada_em');
            $table->timestamp('impressa_em')->nullable();
            $table->timestamps();

            $table->unique(['pedido_id', 'setor_producao_id', 'sequencia'], 'impressoes_producao_sequencia_unique');
            $table->index(['setor_producao_id', 'impressa_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impressoes_producao');
    }
};
