<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovimentacaoEstoqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeOperarEstoque() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['tipo' => ['required', Rule::in(['entrada', 'perda', 'ajuste'])], 'quantidade' => ['required', 'numeric', 'not_in:0', 'between:-999999,999999'], 'custo_unitario' => ['nullable', 'numeric', 'min:0', 'max:999999.99'], 'motivo' => ['required', 'string', 'max:500']];
    }
}
