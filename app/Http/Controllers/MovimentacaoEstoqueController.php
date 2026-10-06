<?php

namespace App\Http\Controllers;

use App\Actions\MovimentarEstoque;
use App\Http\Requests\StoreMovimentacaoEstoqueRequest;
use App\Models\Ingrediente;
use Illuminate\Http\RedirectResponse;

class MovimentacaoEstoqueController extends Controller
{
    public function store(StoreMovimentacaoEstoqueRequest $request, Ingrediente $ingrediente, MovimentarEstoque $movimentar): RedirectResponse
    {
        $dados = $request->validated();
        $quantidade = (float) $dados['quantidade'];
        if ($dados['tipo'] === 'perda') {
            $quantidade = -abs($quantidade);
        }if ($dados['tipo'] === 'entrada') {
            $quantidade = abs($quantidade);
        } $custo = isset($dados['custo_unitario']) ? (int) round(((float) $dados['custo_unitario']) * 100) : null;
        $movimentar->handle($ingrediente, $request->user(), $dados['tipo'], $quantidade, $dados['motivo'], $custo);

        return back()->with('success', 'Movimentação registrada.');
    }
}
