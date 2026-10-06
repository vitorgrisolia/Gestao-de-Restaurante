<?php

namespace App\Models;

use Database\Factories\InventarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['iniciado_por_id', 'concluido_por_id', 'status', 'observacao', 'iniciado_em', 'concluido_em'])]
class Inventario extends Model
{
    /** @use HasFactory<InventarioFactory> */
    use HasFactory;

    /** @return HasMany<InventarioItem, $this> */
    public function itens(): HasMany
    {
        return $this->hasMany(InventarioItem::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['iniciado_em' => 'datetime', 'concluido_em' => 'datetime'];
    }
}
