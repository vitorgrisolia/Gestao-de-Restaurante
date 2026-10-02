<?php

namespace App\Models;

use Database\Factories\ItemCardapioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $categoria_cardapio_id
 * @property string $nome
 * @property string|null $descricao
 * @property int $preco_centavos
 * @property string|null $imagem
 * @property bool $disponivel
 * @property int $ordem
 */
#[Fillable(['categoria_cardapio_id', 'nome', 'descricao', 'preco_centavos', 'imagem', 'disponivel', 'ordem'])]
class ItemCardapio extends Model
{
    /** @use HasFactory<ItemCardapioFactory> */
    use HasFactory;

    protected $table = 'itens_cardapio';

    /** @return BelongsTo<CategoriaCardapio, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaCardapio::class, 'categoria_cardapio_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'preco_centavos' => 'integer',
            'disponivel' => 'boolean',
            'ordem' => 'integer',
        ];
    }
}
