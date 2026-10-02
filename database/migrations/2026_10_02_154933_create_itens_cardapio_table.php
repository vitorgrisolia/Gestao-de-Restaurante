<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('itens_cardapio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_cardapio_id')
                ->constrained('categorias_cardapio')
                ->restrictOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->unsignedInteger('preco_centavos');
            $table->string('imagem')->nullable();
            $table->boolean('disponivel')->default(true);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();

            $table->unique(['categoria_cardapio_id', 'nome']);
            $table->index(['categoria_cardapio_id', 'disponivel', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_cardapio');
    }
};
