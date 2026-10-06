<?php

namespace App\Http\Requests;

use App\Models\CategoriaCardapio;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaCardapioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        /** @var CategoriaCardapio $categoria */
        $categoria = $this->route('categoria_cardapio');

        return [
            'nome' => ['required', 'string', 'max:100', Rule::unique('categorias_cardapio', 'nome')->ignore($categoria)],
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
