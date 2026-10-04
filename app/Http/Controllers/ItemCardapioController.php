<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemCardapioRequest;
use App\Models\ItemCardapio;
use Illuminate\Http\RedirectResponse;

class ItemCardapioController extends Controller
{
    public function store(StoreItemCardapioRequest $request): RedirectResponse
    {
        ItemCardapio::create([
            ...$request->safe()->only([
                'categoria_cardapio_id', 'nome', 'descricao', 'imagem', 'disponivel', 'ordem',
            ]),
            'preco_centavos' => (int) round($request->float('preco') * 100),
        ]);

        return back()->with('success', 'Item cadastrado com sucesso.');
    }
}
