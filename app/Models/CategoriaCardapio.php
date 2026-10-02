<?php

namespace App\Models;

use Database\Factories\CategoriaCardapioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $descricao
 * @property bool $ativa
 * @property int $ordem
 */
#[Fillable(['nome', 'descricao', 'ativa', 'ordem'])]
class CategoriaCardapio extends Model
{
    /** @use HasFactory<CategoriaCardapioFactory> */
    use HasFactory;

    protected $table = 'categorias_cardapio';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ativa' => 'boolean',
            'ordem' => 'integer',
        ];
    }
}
