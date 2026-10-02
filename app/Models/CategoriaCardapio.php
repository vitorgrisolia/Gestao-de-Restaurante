<?php

namespace App\Models;

use Database\Factories\CategoriaCardapioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property bool $ativa
 * @property int $ordem
 */
#[Fillable(['nome', 'descricao', 'ativa', 'ordem'])]
class CategoriaCardapio extends Model
{
    /** @use HasFactory<CategoriaCardapioFactory> */
    use HasFactory;

    protected $table = 'categorias_cardapio';

    /** @return HasMany<ItemCardapio, $this> */
    public function itens(): HasMany
    {
        return $this->hasMany(ItemCardapio::class, 'categoria_cardapio_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ativa' => 'boolean',
            'ordem' => 'integer',
        ];
    }
}
