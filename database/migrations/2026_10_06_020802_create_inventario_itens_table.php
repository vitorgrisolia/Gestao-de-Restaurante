<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios')->cascadeOnDelete();
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->restrictOnDelete();
            $table->decimal('quantidade_sistema', 14, 3);
            $table->decimal('quantidade_contada', 14, 3)->nullable();
            $table->decimal('diferenca', 14, 3)->nullable();
            $table->timestamps();
            $table->unique(['inventario_id', 'ingrediente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_itens');
    }
};
