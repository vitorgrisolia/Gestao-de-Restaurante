<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pedido_item_id')->nullable()->constrained('pedido_itens')->restrictOnDelete();
            $table->string('tipo', 20);
            $table->decimal('quantidade', 14, 3);
            $table->decimal('saldo_anterior', 14, 3);
            $table->decimal('saldo_posterior', 14, 3);
            $table->unsignedInteger('custo_unitario_centavos')->default(0);
            $table->text('motivo')->nullable();
            $table->uuid('chave_idempotencia')->nullable()->unique();
            $table->timestamp('registrada_em');
            $table->timestamps();
            $table->index(['ingrediente_id', 'registrada_em']);
            $table->index(['tipo', 'registrada_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_estoque');
    }
};
