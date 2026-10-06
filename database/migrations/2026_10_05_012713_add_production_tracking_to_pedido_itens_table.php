<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->foreignId('setor_producao_id')->nullable()->after('item_cardapio_id')->constrained('setores_producao')->restrictOnDelete();
            $table->foreignId('iniciado_por_id')->nullable()->after('iniciado_em')->constrained('users')->restrictOnDelete();
            $table->foreignId('pronto_por_id')->nullable()->after('pronto_em')->constrained('users')->restrictOnDelete();
            $table->foreignId('entregue_por_id')->nullable()->after('entregue_em')->constrained('users')->restrictOnDelete();
            $table->index(['setor_producao_id', 'status', 'enviado_em'], 'pedido_itens_painel_producao_index');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table) {
            $table->dropIndex('pedido_itens_painel_producao_index');
            $table->dropConstrainedForeignId('entregue_por_id');
            $table->dropConstrainedForeignId('pronto_por_id');
            $table->dropConstrainedForeignId('iniciado_por_id');
            $table->dropConstrainedForeignId('setor_producao_id');
        });
    }
};
