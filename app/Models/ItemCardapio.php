<?php

namespace App\Models;

use App\TipoVenda;
use Database\Factories\ItemCardapioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $categoria_cardapio_id
 * @property int|null $setor_producao_id
 * @property string $nome
 * @property string|null $descricao
 * @property int $preco_centavos
 * @property string|null $imagem
 * @property bool $disponivel
 * @property int $ordem
 * @property TipoVenda $tipo_venda
 * @property bool $permite_excesso_carne
 */
#[Fillable(['categoria_cardapio_id', 'setor_producao_id', 'nome', 'descricao', 'preco_centavos', 'imagem', 'disponivel', 'ordem', 'tipo_venda', 'permite_excesso_carne'])]
class ItemCardapio extends Model
{
    /** @use HasFactory<ItemCardapioFactory> */
    use HasFactory;

    protected $table = 'itens_cardapio';

    protected $attributes = ['tipo_venda' => 'unidade', 'permite_excesso_carne' => false];

    /** @return BelongsTo<CategoriaCardapio, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaCardapio::class, 'categoria_cardapio_id');
    }

    /** @return BelongsTo<SetorProducao, $this> */
    public function setorProducao(): BelongsTo
    {
        return $this->belongsTo(SetorProducao::class, 'setor_producao_id');
    }

    /** @return HasMany<PedidoItem, $this> */
    public function pedidoItens(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    /** @return HasMany<FichaTecnicaItem, $this> */
    public function fichaTecnica(): HasMany
    {
        return $this->hasMany(FichaTecnicaItem::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'preco_centavos' => 'integer',
            'disponivel' => 'boolean',
            'ordem' => 'integer',
            'tipo_venda' => TipoVenda::class,
            'permite_excesso_carne' => 'boolean',
        ];
    }
}
