<?php

namespace App\Models;

use App\FormaPagamento;
use Database\Factories\PagamentoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $comanda_id
 * @property int|null $caixa_id
 * @property int $recebido_por_id
 * @property FormaPagamento $forma
 * @property int $valor_centavos
 * @property Carbon $pago_em
 */
#[Fillable(['comanda_id', 'caixa_id', 'recebido_por_id', 'forma', 'valor_centavos', 'valor_recebido_centavos', 'troco_centavos', 'tipo_divisao', 'referencia_divisao', 'pago_em', 'estornado_por_id', 'motivo_estorno', 'estornado_em'])]
class Pagamento extends Model
{
    /** @use HasFactory<PagamentoFactory> */
    use HasFactory;

    /** @return BelongsTo<Comanda, $this> */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class);
    }

    /** @return BelongsTo<User, $this> */
    public function recebidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recebido_por_id');
    }

    /** @return BelongsTo<Caixa, $this> */
    public function caixa(): BelongsTo
    {
        return $this->belongsTo(Caixa::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'forma' => FormaPagamento::class,
            'valor_centavos' => 'integer',
            'pago_em' => 'datetime',
            'referencia_divisao' => 'array',
            'estornado_em' => 'datetime',
        ];
    }
}
