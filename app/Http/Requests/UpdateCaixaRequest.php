<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCaixaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeReceberPagamento() ?? false;
    }

    /** @return array<string,ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return ['valor_informado' => ['required', 'numeric', 'min:0', 'max:999999.99']];
    }
}
