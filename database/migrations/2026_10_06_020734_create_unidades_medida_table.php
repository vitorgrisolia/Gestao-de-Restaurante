<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 60)->unique();
            $table->string('sigla', 10)->unique();
            $table->unsignedTinyInteger('casas_decimais')->default(3);
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
};
