<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePedidoItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->papel->podeRegistrarPedido() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantidade' => ['required', 'integer', 'min:1', 'max:99'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'quantidade.required' => 'Informe a quantidade.',
            'quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'quantidade.max' => 'A quantidade não pode ser maior que 99.',
        ];
    }

    public function observacaoItem(): ?string
    {
        $observacao = $this->validated('observacao');

        return filled($observacao) ? trim((string) $observacao) : null;
    }
}
