<?php

namespace App\Http\Controllers;

use App\Models\CategoriaCardapio;
use App\Models\ItemCardapio;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CardapioController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->papel->podeAdministrarCadastros(), 403);

        $categorias = CategoriaCardapio::query()
            ->with(['itens' => fn ($query) => $query
                ->orderBy('ordem')
                ->orderBy('nome')])
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return Inertia::render('cardapio/index', [
            'categorias' => $categorias,
            'resumo' => [
                'categorias' => $categorias->count(),
                'itens' => ItemCardapio::query()->count(),
                'disponiveis' => ItemCardapio::query()->where('disponivel', true)->count(),
            ],
        ]);
    }
}
