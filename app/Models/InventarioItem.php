<?php

namespace App\Models;

use Database\Factories\InventarioItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inventario_id', 'ingrediente_id', 'quantidade_sistema', 'quantidade_contada', 'diferenca'])]
class InventarioItem extends Model
{
    /** @use HasFactory<InventarioItemFactory> */
    use HasFactory;

    protected $table = 'inventario_itens';

    /** @return BelongsTo<Ingrediente, $this> */
    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['quantidade_sistema' => 'decimal:3', 'quantidade_contada' => 'decimal:3', 'diferenca' => 'decimal:3'];
    }
}
