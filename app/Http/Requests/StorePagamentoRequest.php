<?php

namespace App\Http\Requests;

use App\FormaPagamento;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagamentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->papel->podeReceberPagamento() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'forma_pagamento' => ['required', Rule::enum(FormaPagamento::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'forma_pagamento.required' => 'Selecione a forma de pagamento.',
            'forma_pagamento.enum' => 'Selecione uma forma de pagamento válida.',
        ];
    }
}
