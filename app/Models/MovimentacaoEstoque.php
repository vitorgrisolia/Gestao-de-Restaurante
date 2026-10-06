<?php

namespace App\Models;

use Database\Factories\MovimentacaoEstoqueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ingrediente_id', 'usuario_id', 'pedido_item_id', 'tipo', 'quantidade', 'saldo_anterior', 'saldo_posterior', 'custo_unitario_centavos', 'motivo', 'chave_idempotencia', 'registrada_em'])]
class MovimentacaoEstoque extends Model
{
    /** @use HasFactory<MovimentacaoEstoqueFactory> */
    use HasFactory;

    protected $table = 'movimentacoes_estoque';

    /** @return BelongsTo<Ingrediente, $this> */
    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['quantidade' => 'decimal:3', 'saldo_anterior' => 'decimal:3', 'saldo_posterior' => 'decimal:3', 'custo_unitario_centavos' => 'integer', 'registrada_em' => 'datetime'];
    }
}
