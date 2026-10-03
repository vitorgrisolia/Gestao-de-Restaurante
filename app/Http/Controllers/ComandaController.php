<?php

namespace App\Http\Controllers;

use App\Actions\AbrirComanda;
use App\Http\Requests\AbrirComandaRequest;
use App\Models\CategoriaCardapio;
use App\Models\Comanda;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComandaController extends Controller
{
    public function show(Request $request, Comanda $comanda): Response
    {
        abort_unless($request->user()?->papel->podeRegistrarPedido() ?? false, 403);

        $comanda->load('mesa:id,numero');

        $pedidos = $comanda->pedidos()
            ->with(['criadoPor:id,name', 'itens'])
            ->orderByDesc('id')
            ->get();

        $categorias = CategoriaCardapio::query()
            ->where('ativa', true)
            ->with(['itens' => fn ($query) => $query
                ->where('disponivel', true)
                ->orderBy('ordem')
                ->orderBy('nome')])
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return Inertia::render('comandas/show', [
            'comanda' => $comanda,
            'pedidos' => $pedidos,
            'categorias' => $categorias,
        ]);
    }

    public function store(AbrirComandaRequest $request, AbrirComanda $abrirComanda): RedirectResponse
    {
        $abrirComanda->handle(
            Mesa::findOrFail($request->integer('mesa_id')),
            $request->user(),
            $request->integer('quantidade_pessoas'),
        );

        return back()->with('success', 'Mesa aberta com sucesso.');
    }
}
