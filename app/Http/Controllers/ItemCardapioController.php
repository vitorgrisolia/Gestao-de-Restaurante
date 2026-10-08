<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemCardapioRequest;
use App\Http\Requests\UpdateItemCardapioRequest;
use App\Models\ItemCardapio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemCardapioController extends Controller
{
    public function store(StoreItemCardapioRequest $request): RedirectResponse
    {
        ItemCardapio::create($this->attributes($request));

        return back()->with('success', 'Item cadastrado com sucesso.');
    }

    public function update(UpdateItemCardapioRequest $request, ItemCardapio $itemCardapio): RedirectResponse
    {
        $itemCardapio->update($this->attributes($request));

        return back()->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(Request $request, ItemCardapio $itemCardapio): RedirectResponse
    {
        abort_unless($request->user()?->papel->podeAdministrarCadastros() ?? false, 403);

        if ($itemCardapio->pedidoItens()->exists()) {
            throw ValidationException::withMessages([
                'item' => 'Este item possui pedidos registrados e não pode ser excluído. Marque-o como indisponível.',
            ]);
        }

        $itemCardapio->delete();

        return back()->with('success', 'Item excluído com sucesso.');
    }

    /** @return array<string, mixed> */
    private function attributes(StoreItemCardapioRequest|UpdateItemCardapioRequest $request): array
    {
        return [
            ...$request->safe()->only([
                'categoria_cardapio_id', 'setor_producao_id', 'nome', 'descricao', 'imagem', 'disponivel', 'ordem', 'tipo_venda', 'permite_excesso_carne',
            ]),
            'preco_centavos' => (int) round($request->float('preco') * 100),
        ];
    }
}
