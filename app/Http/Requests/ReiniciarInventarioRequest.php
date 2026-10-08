<?php

namespace App\Http\Requests;

use App\Models\Inventario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReiniciarInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeOperarEstoque() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $inventario = $this->route('inventario');

        return [
            'itens' => ['sometimes', 'array'],
            'itens.*.id' => ['required', 'distinct', Rule::exists('inventario_itens', 'id')->where('inventario_id', $inventario instanceof Inventario ? $inventario->id : null)],
            'itens.*.quantidade_contada' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ];
    }
}
