<?php

namespace App\Models;

use App\StatusComanda;
use Database\Factories\ComandaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $mesa_id
 * @property int $aberta_por_id
 * @property int $quantidade_pessoas
 * @property StatusComanda $status
 * @property bool|null $ativa
 * @property Carbon $aberta_em
 * @property Carbon|null $fechada_em
 */
#[Fillable(['mesa_id', 'aberta_por_id', 'quantidade_pessoas', 'status', 'ativa', 'aberta_em', 'fechada_em'])]
class Comanda extends Model
{
    /** @use HasFactory<ComandaFactory> */
    use HasFactory;

    /** @return BelongsTo<Mesa, $this> */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    /** @return BelongsTo<User, $this> */
    public function abertaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aberta_por_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusComanda::class,
            'ativa' => 'boolean',
            'aberta_em' => 'datetime',
            'fechada_em' => 'datetime',
        ];
    }
}
