<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnidadeMedidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['nome' => ['required', 'string', 'max:80', 'unique:unidades_medida,nome'], 'sigla' => ['required', 'string', 'max:10', 'unique:unidades_medida,sigla'], 'casas_decimais' => ['required', 'integer', 'between:0,3'], 'ativa' => ['required', 'boolean']];
    }
}
