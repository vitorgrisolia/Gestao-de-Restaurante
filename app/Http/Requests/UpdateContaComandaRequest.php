<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContaComandaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeReceberPagamento() ?? false;
    }

    /** @return array<string,ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return ['servico_percentual' => ['required', 'integer', 'min:0', 'max:30'], 'couvert' => ['required', 'numeric', 'min:0', 'max:99999.99'], 'desconto' => ['required', 'numeric', 'min:0', 'max:999999.99'], 'acrescimo' => ['required', 'numeric', 'min:0', 'max:999999.99']];
    }

    public function dinheiro(string $campo): int
    {
        return (int) round($this->float($campo) * 100);
    }
}
