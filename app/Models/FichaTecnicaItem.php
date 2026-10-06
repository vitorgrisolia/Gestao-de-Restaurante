<?php

namespace App\Models;

use Database\Factories\FichaTecnicaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['item_cardapio_id', 'ingrediente_id', 'quantidade'])]
class FichaTecnicaItem extends Model
{
    /** @use HasFactory<FichaTecnicaItemFactory> */
    use HasFactory;

    protected $table = 'ficha_tecnica_itens';

    /** @return BelongsTo<ItemCardapio, $this> */
    public function itemCardapio(): BelongsTo
    {
        return $this->belongsTo(ItemCardapio::class);
    }

    /** @return BelongsTo<Ingrediente, $this> */
    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['quantidade' => 'decimal:3'];
    }
}
