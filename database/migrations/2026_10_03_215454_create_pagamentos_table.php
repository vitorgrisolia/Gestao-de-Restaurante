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
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comanda_id')->constrained('comandas')->restrictOnDelete();
            $table->foreignId('recebido_por_id')->constrained('users')->restrictOnDelete();
            $table->string('forma');
            $table->unsignedInteger('valor_centavos');
            $table->timestamp('pago_em');
            $table->timestamps();

            $table->index(['comanda_id', 'pago_em']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
