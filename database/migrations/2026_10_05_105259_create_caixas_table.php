<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aberto_por_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('fechado_por_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->boolean('aberto')->default(true)->index();
            $table->unsignedInteger('valor_abertura_centavos');
            $table->unsignedInteger('valor_esperado_centavos')->nullable();
            $table->unsignedInteger('valor_informado_centavos')->nullable();
            $table->integer('diferenca_centavos')->nullable();
            $table->timestamp('aberto_em');
            $table->timestamp('fechado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
