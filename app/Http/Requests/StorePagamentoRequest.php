<?php

namespace App\Http\Requests;

use App\FormaPagamento;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagamentoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['tipo_divisao' => $this->input('tipo_divisao', 'integral')]);
    }

    public function authorize(): bool
    {
        return $this->user()?->papel->podeReceberPagamento() ?? false;
    }

    /** @return array<string,ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return ['forma_pagamento' => ['required', Rule::enum(FormaPagamento::class)], 'tipo_divisao' => ['required', Rule::in(['integral', 'pessoa', 'itens', 'valor'])], 'valor' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'], 'itens' => ['nullable', 'array'], 'itens.*' => ['integer', 'distinct', 'exists:pedido_itens,id']];
    }

    public function valorCentavos(): int
    {
        return (int) round($this->float('valor') * 100);
    }

    /** @return list<int> */
    public function itens(): array
    {
        return array_values(array_map('intval', $this->validated('itens', [])));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['forma_pagamento.enum' => 'Selecione uma forma de pagamento válida.'];
    }
}
