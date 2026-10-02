<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AbrirComandaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAbrirComanda() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mesa_id' => ['required', 'integer', 'exists:mesas,id'],
            'quantidade_pessoas' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'mesa_id.required' => 'Selecione uma mesa.',
            'quantidade_pessoas.required' => 'Informe a quantidade de pessoas.',
            'quantidade_pessoas.min' => 'A comanda deve ter pelo menos uma pessoa.',
        ];
    }
}
