<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->unsignedTinyInteger('servico_percentual')->default(0);
            $table->unsignedInteger('couvert_por_pessoa_centavos')->default(0);
            $table->unsignedInteger('desconto_centavos')->default(0);
            $table->unsignedInteger('acrescimo_centavos')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('comandas', fn (Blueprint $table) => $table->dropColumn(['servico_percentual', 'couvert_por_pessoa_centavos', 'desconto_centavos', 'acrescimo_centavos']));
    }
};
