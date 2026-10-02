<?php

namespace App\Models;

use App\EstadoMesa;
use Database\Factories\MesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $setor_salao_id
 * @property string $numero
 * @property int $capacidade
 * @property EstadoMesa $estado
 * @property bool $ativa
 */
#[Fillable(['setor_salao_id', 'numero', 'capacidade', 'estado', 'ativa'])]
class Mesa extends Model
{
    /** @use HasFactory<MesaFactory> */
    use HasFactory;

    /** @return BelongsTo<SetorSalao, $this> */
    public function setorSalao(): BelongsTo
    {
        return $this->belongsTo(SetorSalao::class);
    }

    /** @return HasMany<Comanda, $this> */
    public function comandas(): HasMany
    {
        return $this->hasMany(Comanda::class);
    }

    /** @return HasOne<Comanda, $this> */
    public function comandaAtiva(): HasOne
    {
        return $this->hasOne(Comanda::class)->where('ativa', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'estado' => EstadoMesa::class,
            'capacidade' => 'integer',
            'ativa' => 'boolean',
        ];
    }
}
