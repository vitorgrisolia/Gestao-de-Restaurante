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
            'peso_gramas' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'cobrar_excesso_carne' => ['nullable', 'boolean'],
            'adicional_carne' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
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

    /** @return array{peso_gramas?: int|null, cobrar_excesso_carne?: bool|null, adicional_carne_centavos?: int} */
    public function dadosVenda(): array
    {
        $dados = $this->validated();
        $venda = [];
        if (array_key_exists('peso_gramas', $dados)) {
            $venda['peso_gramas'] = $dados['peso_gramas'] !== null ? (int) $dados['peso_gramas'] : null;
        }
        if (array_key_exists('cobrar_excesso_carne', $dados)) {
            $venda['cobrar_excesso_carne'] = $dados['cobrar_excesso_carne'] !== null ? (bool) $dados['cobrar_excesso_carne'] : null;
        }
        if (array_key_exists('adicional_carne', $dados)) {
            $venda['adicional_carne_centavos'] = (int) round((float) ($dados['adicional_carne'] ?? 0) * 100);
        }

        return $venda;
    }
}
