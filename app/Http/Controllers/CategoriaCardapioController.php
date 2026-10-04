<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaCardapioRequest;
use App\Models\CategoriaCardapio;
use Illuminate\Http\RedirectResponse;

class CategoriaCardapioController extends Controller
{
    public function store(StoreCategoriaCardapioRequest $request): RedirectResponse
    {
        CategoriaCardapio::create($request->safe()->only([
            'nome', 'descricao', 'ativa', 'ordem',
        ]));

        return back()->with('success', 'Categoria cadastrada com sucesso.');
    }
}
