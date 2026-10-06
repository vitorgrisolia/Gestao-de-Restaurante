<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReimpressaoProducaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeOperarProducao() ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'chave_idempotencia' => ['required', 'uuid'],
        ];
    }

    public function chaveIdempotencia(): string
    {
        return (string) $this->validated('chave_idempotencia');
    }
}
