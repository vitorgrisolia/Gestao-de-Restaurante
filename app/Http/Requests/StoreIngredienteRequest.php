<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngredienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['unidade_medida_id' => ['required', 'exists:unidades_medida,id'], 'nome' => ['required', 'string', 'max:120', 'unique:ingredientes,nome'], 'estoque_minimo' => ['required', 'numeric', 'min:0'], 'ativo' => ['required', 'boolean']];
    }
}
