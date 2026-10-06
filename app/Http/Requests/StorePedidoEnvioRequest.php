<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoEnvioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeRegistrarPedido() ?? false;
    }

    /** @return array<string, never> */
    public function rules(): array
    {
        return [];
    }
}
