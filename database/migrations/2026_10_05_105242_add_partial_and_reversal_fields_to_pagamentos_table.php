<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->unsignedInteger('valor_recebido_centavos')->nullable();
            $table->unsignedInteger('troco_centavos')->default(0);
            $table->string('tipo_divisao', 20)->default('integral');
            $table->json('referencia_divisao')->nullable();
            $table->foreignId('estornado_por_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('motivo_estorno')->nullable();
            $table->timestamp('estornado_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estornado_por_id');
            $table->dropColumn(['valor_recebido_centavos', 'troco_centavos', 'tipo_divisao', 'referencia_divisao', 'motivo_estorno', 'estornado_em']);
        });
    }
};
