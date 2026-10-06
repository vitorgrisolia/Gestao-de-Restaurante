<?php

namespace App\Models;

use Database\Factories\ImpressaoProducaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pedido_id
 * @property int $setor_producao_id
 * @property int $solicitada_por_id
 * @property string $chave_idempotencia
 * @property int $sequencia
 * @property string $tipo
 * @property Carbon $solicitada_em
 * @property Carbon|null $impressa_em
 */
#[Fillable(['pedido_id', 'setor_producao_id', 'solicitada_por_id', 'chave_idempotencia', 'sequencia', 'tipo', 'solicitada_em', 'impressa_em'])]
class ImpressaoProducao extends Model
{
    /** @use HasFactory<ImpressaoProducaoFactory> */
    use HasFactory;

    protected $table = 'impressoes_producao';

    /** @return BelongsTo<Pedido, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    /** @return BelongsTo<SetorProducao, $this> */
    public function setorProducao(): BelongsTo
    {
        return $this->belongsTo(SetorProducao::class);
    }

    /** @return BelongsTo<User, $this> */
    public function solicitadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitada_por_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sequencia' => 'integer',
            'solicitada_em' => 'datetime',
            'impressa_em' => 'datetime',
        ];
    }
}
