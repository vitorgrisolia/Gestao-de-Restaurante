<?php

namespace App\Models;

use Database\Factories\CaixaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['aberto_por_id', 'fechado_por_id', 'aberto', 'valor_abertura_centavos', 'valor_esperado_centavos', 'valor_informado_centavos', 'diferenca_centavos', 'aberto_em', 'fechado_em'])]
class Caixa extends Model
{
    /** @use HasFactory<CaixaFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function abertoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aberto_por_id');
    }

    /** @return HasMany<MovimentoCaixa, $this> */
    public function movimentos(): HasMany
    {
        return $this->hasMany(MovimentoCaixa::class);
    }

    /** @return HasMany<Pagamento, $this> */
    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    protected function casts(): array
    {
        return ['aberto' => 'boolean', 'aberto_em' => 'datetime', 'fechado_em' => 'datetime'];
    }
}
