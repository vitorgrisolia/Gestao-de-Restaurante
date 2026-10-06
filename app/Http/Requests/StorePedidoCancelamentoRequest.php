<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePedidoCancelamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeRegistrarPedido() ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'motivo.required' => 'Informe o motivo do cancelamento.',
            'motivo.min' => 'O motivo deve ter pelo menos 3 caracteres.',
        ];
    }

    public function motivo(): string
    {
        return $this->string('motivo')->trim()->toString();
    }
}
