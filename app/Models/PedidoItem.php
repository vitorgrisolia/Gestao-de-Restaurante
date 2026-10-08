<?php

namespace App\Models;

use App\StatusItemPedido;
use App\TipoVenda;
use Database\Factories\PedidoItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pedido_id
 * @property int $item_cardapio_id
 * @property int|null $setor_producao_id
 * @property string $nome_item
 * @property int $quantidade
 * @property int $preco_unitario_centavos
 * @property TipoVenda $tipo_venda
 * @property int|null $preco_referencia_centavos
 * @property int|null $peso_gramas
 * @property bool $permite_excesso_carne
 * @property bool $cobrar_excesso_carne
 * @property int $adicional_carne_centavos
 * @property string|null $observacao
 * @property StatusItemPedido $status
 * @property Carbon|null $enviado_em
 * @property Carbon|null $iniciado_em
 * @property int|null $iniciado_por_id
 * @property Carbon|null $pronto_em
 * @property int|null $pronto_por_id
 * @property Carbon|null $entregue_em
 * @property int|null $entregue_por_id
 * @property int|null $cancelado_por_id
 * @property string|null $motivo_cancelamento
 * @property Carbon|null $cancelado_em
 */
#[Fillable(['pedido_id', 'item_cardapio_id', 'setor_producao_id', 'nome_item', 'quantidade', 'preco_unitario_centavos', 'observacao', 'status', 'enviado_em', 'iniciado_em', 'iniciado_por_id', 'pronto_em', 'pronto_por_id', 'entregue_em', 'entregue_por_id', 'cancelado_por_id', 'motivo_cancelamento', 'cancelado_em', 'tipo_venda', 'preco_referencia_centavos', 'peso_gramas', 'permite_excesso_carne', 'cobrar_excesso_carne', 'adicional_carne_centavos'])]
class PedidoItem extends Model
{
    /** @use HasFactory<PedidoItemFactory> */
    use HasFactory;

    protected $table = 'pedido_itens';

    protected $attributes = ['tipo_venda' => 'unidade', 'permite_excesso_carne' => false, 'cobrar_excesso_carne' => false, 'adicional_carne_centavos' => 0];

    /** @return BelongsTo<Pedido, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    /** @return BelongsTo<ItemCardapio, $this> */
    public function itemCardapio(): BelongsTo
    {
        return $this->belongsTo(ItemCardapio::class);
    }

    /** @return BelongsTo<SetorProducao, $this> */
    public function setorProducao(): BelongsTo
    {
        return $this->belongsTo(SetorProducao::class);
    }

    /** @return BelongsTo<User, $this> */
    public function iniciadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iniciado_por_id');
    }

    /** @return BelongsTo<User, $this> */
    public function prontoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pronto_por_id');
    }

    /** @return BelongsTo<User, $this> */
    public function entreguePor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entregue_por_id');
    }

    /** @return BelongsTo<User, $this> */
    public function canceladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelado_por_id');
    }

    public function subtotalCentavos(): int
    {
        return $this->quantidade * $this->preco_unitario_centavos;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'preco_unitario_centavos' => 'integer',
            'tipo_venda' => TipoVenda::class,
            'preco_referencia_centavos' => 'integer',
            'peso_gramas' => 'integer',
            'permite_excesso_carne' => 'boolean',
            'cobrar_excesso_carne' => 'boolean',
            'adicional_carne_centavos' => 'integer',
            'status' => StatusItemPedido::class,
            'enviado_em' => 'datetime',
            'iniciado_em' => 'datetime',
            'pronto_em' => 'datetime',
            'entregue_em' => 'datetime',
            'cancelado_em' => 'datetime',
        ];
    }
}
