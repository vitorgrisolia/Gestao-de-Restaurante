<?php

namespace App\Actions;

use App\Models\Comanda;
use App\Models\ItemCardapio;
use App\Models\Pedido;
use App\Models\User;
use App\StatusComanda;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CriarPedido
{
    public function __construct(private readonly CalcularPrecoItemPedido $calcularPreco) {}

    /**
     * @param  list<array{item_cardapio_id: int, quantidade: int, observacao: string|null, peso_gramas?: int|null, cobrar_excesso_carne?: bool|null, adicional_carne_centavos?: int}>  $itens
     */
    public function handle(Comanda $comanda, User $usuario, array $itens, ?string $observacao): Pedido
    {
        return DB::transaction(function () use ($comanda, $usuario, $itens, $observacao): Pedido {
            $comandaBloqueada = Comanda::query()->lockForUpdate()->findOrFail($comanda->id);

            if (! $comandaBloqueada->ativa || $comandaBloqueada->status !== StatusComanda::Aberta) {
                throw ValidationException::withMessages([
                    'comanda' => 'Esta comanda não está aberta para receber pedidos.',
                ]);
            }

            $idsProdutos = collect($itens)->pluck('item_cardapio_id');
            $produtos = ItemCardapio::query()
                ->whereKey($idsProdutos)
                ->where('disponivel', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($produtos->count() !== $idsProdutos->count()) {
                throw ValidationException::withMessages([
                    'itens' => 'Um ou mais itens do cardápio não estão disponíveis.',
                ]);
            }

            $pedido = $comandaBloqueada->pedidos()->create([
                'criado_por_id' => $usuario->id,
                'status' => StatusPedido::Rascunho,
                'observacao' => $observacao,
            ]);

            foreach ($itens as $indice => $dadosItem) {
                $produto = $produtos->get($dadosItem['item_cardapio_id']);

                if (! $produto instanceof ItemCardapio) {
                    throw ValidationException::withMessages([
                        'itens' => 'Um ou mais itens do cardápio não estão disponíveis.',
                    ]);
                }

                $peso = $dadosItem['peso_gramas'] ?? null;
                $cobrarExcesso = $dadosItem['cobrar_excesso_carne'] ?? null;
                $adicional = $dadosItem['adicional_carne_centavos'] ?? 0;
                $preco = $this->calcularPreco->handle($produto->tipo_venda, $produto->preco_centavos, $peso, $produto->permite_excesso_carne, $cobrarExcesso, $adicional, "itens.{$indice}.");

                $pedido->itens()->create([
                    'item_cardapio_id' => $produto->id,
                    'setor_producao_id' => $produto->setor_producao_id,
                    'nome_item' => $produto->nome,
                    'quantidade' => $dadosItem['quantidade'],
                    'preco_unitario_centavos' => $preco,
                    'preco_referencia_centavos' => $produto->preco_centavos,
                    'tipo_venda' => $produto->tipo_venda,
                    'peso_gramas' => $peso,
                    'permite_excesso_carne' => $produto->permite_excesso_carne,
                    'cobrar_excesso_carne' => $cobrarExcesso ?? false,
                    'adicional_carne_centavos' => $adicional,
                    'observacao' => $dadosItem['observacao'],
                    'status' => StatusItemPedido::Rascunho,
                ]);
            }

            return $pedido->load('itens');
        });
    }
}
