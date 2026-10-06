<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pedido_itens')
            ->select(['id', 'item_cardapio_id'])
            ->whereNull('setor_producao_id')
            ->orderBy('id')
            ->chunkById(200, function ($itens): void {
                foreach ($itens as $item) {
                    $setorId = DB::table('itens_cardapio')->where('id', $item->item_cardapio_id)->value('setor_producao_id');
                    DB::table('pedido_itens')->where('id', $item->id)->update(['setor_producao_id' => $setorId]);
                }
            });
    }

    /** O backfill é irreversível porque os vínculos podem ter sido alterados posteriormente. */
    public function down(): void {}
};
