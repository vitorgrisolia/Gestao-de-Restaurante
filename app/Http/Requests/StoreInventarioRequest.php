<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeOperarEstoque() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['observacao' => ['nullable', 'string', 'max:500']];
    }
}
