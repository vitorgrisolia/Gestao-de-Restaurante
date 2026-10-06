<?php

namespace App\Http\Requests;

use App\Models\UnidadeMedida;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnidadeMedidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $unidade = $this->route('unidade_medida');
        $id = $unidade instanceof UnidadeMedida ? $unidade->id : null;

        return ['nome' => ['required', 'string', 'max:80', Rule::unique('unidades_medida', 'nome')->ignore($id)], 'sigla' => ['required', 'string', 'max:10', Rule::unique('unidades_medida', 'sigla')->ignore($id)], 'casas_decimais' => ['required', 'integer', 'between:0,3'], 'ativa' => ['required', 'boolean']];
    }
}
