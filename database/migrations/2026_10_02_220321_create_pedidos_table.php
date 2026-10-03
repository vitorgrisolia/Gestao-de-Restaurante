<?php

use App\StatusPedido;
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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comanda_id')->constrained('comandas')->restrictOnDelete();
            $table->foreignId('criado_por_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default(StatusPedido::Rascunho->value);
            $table->text('observacao')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('pronto_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamps();

            $table->index(['comanda_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
