<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itens_cardapio', function (Blueprint $table): void {
            $table->string('tipo_venda', 20)->default('unidade');
            $table->boolean('permite_excesso_carne')->default(false);
        });
        Schema::table('pedido_itens', function (Blueprint $table): void {
            $table->string('tipo_venda', 20)->default('unidade');
            $table->unsignedInteger('preco_referencia_centavos')->nullable();
            $table->unsignedInteger('peso_gramas')->nullable();
            $table->boolean('permite_excesso_carne')->default(false);
            $table->boolean('cobrar_excesso_carne')->default(false);
            $table->unsignedInteger('adicional_carne_centavos')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('pedido_itens', function (Blueprint $table): void {
            $table->dropColumn(['tipo_venda', 'preco_referencia_centavos', 'peso_gramas', 'permite_excesso_carne', 'cobrar_excesso_carne', 'adicional_carne_centavos']);
        });
        Schema::table('itens_cardapio', function (Blueprint $table): void {
            $table->dropColumn(['tipo_venda', 'permite_excesso_carne']);
        });
    }
};
