<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaCardapioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100', 'unique:categorias_cardapio,nome'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'ativa' => ['required', 'boolean'],
            'ordem' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome da categoria.',
            'nome.unique' => 'Já existe uma categoria com este nome.',
            'ativa.required' => 'Informe se a categoria está ativa.',
            'ordem.required' => 'Informe a ordem da categoria.',
        ];
    }
}
