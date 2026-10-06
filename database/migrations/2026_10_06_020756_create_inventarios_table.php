<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iniciado_por_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('concluido_por_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('aberto');
            $table->text('observacao')->nullable();
            $table->timestamp('iniciado_em');
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
