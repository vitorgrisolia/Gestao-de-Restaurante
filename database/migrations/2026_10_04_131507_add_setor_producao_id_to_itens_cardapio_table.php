<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itens_cardapio', function (Blueprint $table) {
            $table->foreignId('setor_producao_id')
                ->nullable()
                ->after('categoria_cardapio_id')
                ->constrained('setores_producao')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('itens_cardapio', function (Blueprint $table) {
            $table->dropConstrainedForeignId('setor_producao_id');
        });
    }
};
