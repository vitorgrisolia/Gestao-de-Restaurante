<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_tecnica_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_cardapio_id')->constrained('itens_cardapio')->cascadeOnDelete();
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->restrictOnDelete();
            $table->decimal('quantidade', 14, 3);
            $table->timestamps();
            $table->unique(['item_cardapio_id', 'ingrediente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_tecnica_itens');
    }
};
