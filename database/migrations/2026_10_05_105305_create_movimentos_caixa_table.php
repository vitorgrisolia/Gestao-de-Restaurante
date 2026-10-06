<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagamentos', fn (Blueprint $table) => $table->foreignId('caixa_id')->nullable()->after('comanda_id')->constrained('caixas')->restrictOnDelete());
        Schema::create('movimentos_caixa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixa_id')->constrained('caixas')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pagamento_id')->nullable()->constrained('pagamentos')->restrictOnDelete();
            $table->string('tipo', 20);
            $table->integer('valor_centavos');
            $table->text('descricao');
            $table->timestamp('registrado_em');
            $table->timestamps();
            $table->index(['caixa_id', 'registrado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentos_caixa');
        Schema::table('pagamentos', fn (Blueprint $table) => $table->dropConstrainedForeignId('caixa_id'));
    }
};
