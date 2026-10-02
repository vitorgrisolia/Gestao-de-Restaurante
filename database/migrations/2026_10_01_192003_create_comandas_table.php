<?php

use App\StatusComanda;
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
        Schema::create('comandas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->restrictOnDelete();
            $table->foreignId('aberta_por_id')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('quantidade_pessoas');
            $table->string('status')->default(StatusComanda::Aberta->value);
            $table->boolean('ativa')->nullable()->default(true);
            $table->timestamp('aberta_em');
            $table->timestamp('fechada_em')->nullable();
            $table->timestamps();

            $table->unique(['mesa_id', 'ativa']);
            $table->index(['status', 'aberta_em']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comandas');
    }
};
