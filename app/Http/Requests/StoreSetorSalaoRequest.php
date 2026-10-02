<?php

namespace App\Http\Requests;

use App\Models\SetorSalao;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSetorSalaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', SetorSalao::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:80', 'unique:setores_salao,nome'],
            'descricao' => ['nullable', 'string', 'max:160'],
            'ativo' => ['sometimes', 'boolean'],
            'ordem' => ['sometimes', 'integer', 'min:0', 'max:999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome do setor.',
            'nome.unique' => 'Já existe um setor com este nome.',
        ];
    }
}
