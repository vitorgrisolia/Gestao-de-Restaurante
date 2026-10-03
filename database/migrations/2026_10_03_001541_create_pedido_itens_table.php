<?php

use App\StatusItemPedido;
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
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->restrictOnDelete();
            $table->foreignId('item_cardapio_id')->constrained('itens_cardapio')->restrictOnDelete();
            $table->string('nome_item');
            $table->unsignedSmallInteger('quantidade');
            $table->unsignedInteger('preco_unitario_centavos');
            $table->text('observacao')->nullable();
            $table->string('status')->default(StatusItemPedido::Rascunho->value);
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('pronto_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamps();

            $table->index(['pedido_id', 'status']);
            $table->index(['status', 'enviado_em']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
    }
};
