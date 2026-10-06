<?php

namespace App\Models;

use Database\Factories\MovimentoCaixaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['caixa_id', 'usuario_id', 'pagamento_id', 'tipo', 'valor_centavos', 'descricao', 'registrado_em'])]
class MovimentoCaixa extends Model
{
    /** @use HasFactory<MovimentoCaixaFactory> */
    use HasFactory;

    protected $table = 'movimentos_caixa';

    /** @return BelongsTo<Caixa, $this> */
    public function caixa(): BelongsTo
    {
        return $this->belongsTo(Caixa::class);
    }

    protected function casts(): array
    {
        return ['valor_centavos' => 'integer', 'registrado_em' => 'datetime'];
    }
}
