<?php

namespace App\Models;

use Database\Factories\IngredienteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['unidade_medida_id', 'nome', 'estoque_atual', 'estoque_minimo', 'custo_medio_centavos', 'ativo'])]
class Ingrediente extends Model
{
    /** @use HasFactory<IngredienteFactory> */
    use HasFactory;

    /** @return BelongsTo<UnidadeMedida, $this> */
    public function unidadeMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadeMedida::class);
    }

    /** @return HasMany<MovimentacaoEstoque, $this> */
    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoEstoque::class);
    }

    /** @return HasMany<FichaTecnicaItem, $this> */
    public function fichasTecnicas(): HasMany
    {
        return $this->hasMany(FichaTecnicaItem::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['estoque_atual' => 'decimal:3', 'estoque_minimo' => 'decimal:3', 'custo_medio_centavos' => 'integer', 'ativo' => 'boolean'];
    }
}
