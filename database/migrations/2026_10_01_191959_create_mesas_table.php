<?php

use App\EstadoMesa;
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
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setor_salao_id')->constrained('setores_salao')->restrictOnDelete();
            $table->string('numero', 20);
            $table->unsignedSmallInteger('capacidade');
            $table->string('estado')->default(EstadoMesa::Livre->value);
            $table->boolean('ativa')->default(true);
            $table->timestamps();

            $table->unique(['setor_salao_id', 'numero']);
            $table->index(['ativa', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
