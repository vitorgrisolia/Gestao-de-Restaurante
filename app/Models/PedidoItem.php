<?php

namespace App\Models;

use App\StatusItemPedido;
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
 * @property string $nome_item
 * @property int $quantidade
 * @property int $preco_unitario_centavos
 * @property string|null $observacao
 * @property StatusItemPedido $status
 * @property Carbon|null $enviado_em
 * @property Carbon|null $iniciado_em
 * @property Carbon|null $pronto_em
 * @property Carbon|null $entregue_em
 * @property int|null $cancelado_por_id
 * @property string|null $motivo_cancelamento
 * @property Carbon|null $cancelado_em
 */
#[Fillable(['pedido_id', 'item_cardapio_id', 'nome_item', 'quantidade', 'preco_unitario_centavos', 'observacao', 'status', 'enviado_em', 'iniciado_em', 'pronto_em', 'entregue_em', 'cancelado_por_id', 'motivo_cancelamento', 'cancelado_em'])]
class PedidoItem extends Model
{
    /** @use HasFactory<PedidoItemFactory> */
    use HasFactory;

    protected $table = 'pedido_itens';

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
            'status' => StatusItemPedido::class,
            'enviado_em' => 'datetime',
            'iniciado_em' => 'datetime',
            'pronto_em' => 'datetime',
            'entregue_em' => 'datetime',
            'cancelado_em' => 'datetime',
        ];
    }
}
