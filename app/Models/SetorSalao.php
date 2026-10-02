<?php

namespace App\Models;

use Database\Factories\SetorSalaoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property bool $ativo
 * @property int $ordem
 */
#[Fillable(['nome', 'descricao', 'ativo', 'ordem'])]
class SetorSalao extends Model
{
    /** @use HasFactory<SetorSalaoFactory> */
    use HasFactory;

    protected $table = 'setores_salao';

    /** @return HasMany<Mesa, $this> */
    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'ordem' => 'integer',
        ];
    }
}
