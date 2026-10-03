<?php

namespace App\Models;

use App\StatusPedido;
use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $comanda_id
 * @property int $criado_por_id
 * @property StatusPedido $status
 * @property string|null $observacao
 * @property Carbon|null $enviado_em
 * @property Carbon|null $iniciado_em
 * @property Carbon|null $pronto_em
 * @property Carbon|null $entregue_em
 * @property Carbon|null $cancelado_em
 */
#[Fillable(['comanda_id', 'criado_por_id', 'status', 'observacao', 'enviado_em', 'iniciado_em', 'pronto_em', 'entregue_em', 'cancelado_em'])]
class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    /** @return BelongsTo<Comanda, $this> */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class);
    }

    /** @return BelongsTo<User, $this> */
    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    /** @return HasMany<PedidoItem, $this> */
    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusPedido::class,
            'enviado_em' => 'datetime',
            'iniciado_em' => 'datetime',
            'pronto_em' => 'datetime',
            'entregue_em' => 'datetime',
            'cancelado_em' => 'datetime',
        ];
    }
}
