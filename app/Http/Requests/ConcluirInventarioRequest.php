<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConcluirInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeOperarEstoque() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['itens' => ['required', 'array', 'min:1'], 'itens.*.id' => ['required', 'distinct', 'exists:inventario_itens,id'], 'itens.*.quantidade_contada' => ['required', 'numeric', 'min:0', 'max:999999']];
    }
}
