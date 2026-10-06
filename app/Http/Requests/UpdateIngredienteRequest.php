<?php

namespace App\Http\Requests;

use App\Models\Ingrediente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIngredienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $ingrediente = $this->route('ingrediente');
        $id = $ingrediente instanceof Ingrediente ? $ingrediente->id : null;

        return ['unidade_medida_id' => ['required', 'exists:unidades_medida,id'], 'nome' => ['required', 'string', 'max:120', Rule::unique('ingredientes', 'nome')->ignore($id)], 'estoque_minimo' => ['required', 'numeric', 'min:0'], 'ativo' => ['required', 'boolean']];
    }
}
