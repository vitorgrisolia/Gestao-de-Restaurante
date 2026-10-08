<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeRegistrarPedido() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'observacao' => ['nullable', 'string', 'max:1000'],
            'itens' => ['required', 'array', 'min:1', 'max:50'],
            'itens.*.item_cardapio_id' => ['required', 'integer', 'distinct', 'exists:itens_cardapio,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1', 'max:99'],
            'itens.*.observacao' => ['nullable', 'string', 'max:500'],
            'itens.*.peso_gramas' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'itens.*.cobrar_excesso_carne' => ['nullable', 'boolean'],
            'itens.*.adicional_carne' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
        ];
    }

    /**
     * @return list<array{item_cardapio_id: int, quantidade: int, observacao: string|null, peso_gramas: int|null, cobrar_excesso_carne: bool|null, adicional_carne_centavos: int}>
     */
    public function itens(): array
    {
        /** @var list<array{item_cardapio_id: int|string, quantidade: int|string, observacao?: string|null, peso_gramas?: int|string|null, cobrar_excesso_carne?: bool|int|string|null, adicional_carne?: numeric-string|int|float|null}> $itens */
        $itens = $this->validated('itens');

        return array_map(static fn (array $item): array => [
            'item_cardapio_id' => (int) $item['item_cardapio_id'],
            'quantidade' => (int) $item['quantidade'],
            'observacao' => filled($item['observacao'] ?? null) ? trim((string) $item['observacao']) : null,
            'peso_gramas' => isset($item['peso_gramas']) ? (int) $item['peso_gramas'] : null,
            'cobrar_excesso_carne' => isset($item['cobrar_excesso_carne']) ? (bool) $item['cobrar_excesso_carne'] : null,
            'adicional_carne_centavos' => (int) round((float) ($item['adicional_carne'] ?? 0) * 100),
        ], $itens);
    }

    public function observacaoPedido(): ?string
    {
        $observacao = $this->validated('observacao');

        return filled($observacao) ? trim((string) $observacao) : null;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'itens.required' => 'Adicione pelo menos um item ao pedido.',
            'itens.min' => 'Adicione pelo menos um item ao pedido.',
            'itens.*.item_cardapio_id.distinct' => 'Cada produto deve aparecer apenas uma vez no pedido.',
            'itens.*.item_cardapio_id.exists' => 'Um dos itens selecionados não existe no cardápio.',
            'itens.*.quantidade.min' => 'A quantidade de cada item deve ser pelo menos 1.',
        ];
    }
}
