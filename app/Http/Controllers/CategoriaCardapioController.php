<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaCardapioRequest;
use App\Http\Requests\UpdateCategoriaCardapioRequest;
use App\Models\CategoriaCardapio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoriaCardapioController extends Controller
{
    public function store(StoreCategoriaCardapioRequest $request): RedirectResponse
    {
        CategoriaCardapio::create($request->safe()->only([
            'nome', 'descricao', 'ativa', 'ordem',
        ]));

        return back()->with('success', 'Categoria cadastrada com sucesso.');
    }

    public function update(UpdateCategoriaCardapioRequest $request, CategoriaCardapio $categoriaCardapio): RedirectResponse
    {
        $categoriaCardapio->update($request->safe()->only([
            'nome', 'descricao', 'ativa', 'ordem',
        ]));

        return back()->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Request $request, CategoriaCardapio $categoriaCardapio): RedirectResponse
    {
        abort_unless($request->user()?->papel->podeAdministrarCadastros() ?? false, 403);

        if ($categoriaCardapio->itens()->exists()) {
            throw ValidationException::withMessages([
                'categoria' => 'Remova os itens da categoria antes de excluí-la.',
            ]);
        }

        $categoriaCardapio->delete();

        return back()->with('success', 'Categoria excluída com sucesso.');
    }
}
