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
        ];
    }

    /**
     * @return list<array{item_cardapio_id: int, quantidade: int, observacao: string|null}>
     */
    public function itens(): array
    {
        /** @var list<array{item_cardapio_id: int|string, quantidade: int|string, observacao?: string|null}> $itens */
        $itens = $this->validated('itens');

        return array_map(static fn (array $item): array => [
            'item_cardapio_id' => (int) $item['item_cardapio_id'],
            'quantidade' => (int) $item['quantidade'],
            'observacao' => filled($item['observacao'] ?? null) ? trim((string) $item['observacao']) : null,
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
