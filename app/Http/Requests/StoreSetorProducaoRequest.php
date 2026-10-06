<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSetorProducaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100', 'unique:setores_producao,nome'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'ativo' => ['required', 'boolean'],
            'ordem' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome do setor.',
            'nome.unique' => 'Já existe um setor de produção com este nome.',
            'ativo.required' => 'Informe se o setor está ativo.',
            'ordem.required' => 'Informe a ordem do setor.',
        ];
    }
}
