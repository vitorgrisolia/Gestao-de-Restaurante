<?php

namespace App\Http\Requests;

use App\Models\SetorSalao;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSetorSalaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $setorSalao = $this->route('setor_salao');

        return $setorSalao instanceof SetorSalao
            && ($this->user()?->can('update', $setorSalao) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => [
                'required',
                'string',
                'max:80',
                Rule::unique('setores_salao', 'nome')->ignore($this->route('setor_salao')),
            ],
            'descricao' => ['nullable', 'string', 'max:160'],
            'ativo' => ['required', 'boolean'],
            'ordem' => ['required', 'integer', 'min:0', 'max:999'],
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
