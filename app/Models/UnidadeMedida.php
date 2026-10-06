<?php

namespace App\Models;

use Database\Factories\UnidadeMedidaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'sigla', 'casas_decimais', 'ativa'])]
class UnidadeMedida extends Model
{
    /** @use HasFactory<UnidadeMedidaFactory> */
    use HasFactory;

    protected $table = 'unidades_medida';

    /** @return HasMany<Ingrediente, $this> */
    public function ingredientes(): HasMany
    {
        return $this->hasMany(Ingrediente::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['casas_decimais' => 'integer', 'ativa' => 'boolean'];
    }
}
