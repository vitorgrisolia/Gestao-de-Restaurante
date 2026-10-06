<?php

namespace App\Models;

use Database\Factories\SetorProducaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property bool $ativo
 * @property int $ordem
 */
#[Fillable(['nome', 'descricao', 'ativo', 'ordem'])]
class SetorProducao extends Model
{
    /** @use HasFactory<SetorProducaoFactory> */
    use HasFactory;

    protected $table = 'setores_producao';

    /** @return HasMany<ItemCardapio, $this> */
    public function itensCardapio(): HasMany
    {
        return $this->hasMany(ItemCardapio::class);
    }

    /** @return HasMany<PedidoItem, $this> */
    public function itensPedido(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    /** @return HasMany<ImpressaoProducao, $this> */
    public function impressoes(): HasMany
    {
        return $this->hasMany(ImpressaoProducao::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'ordem' => 'integer',
        ];
    }
}
