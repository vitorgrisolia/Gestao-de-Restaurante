<?php

namespace App\Http\Controllers;

use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\ItemCardapio;
use App\Models\MovimentacaoEstoque;
use App\Models\UnidadeMedida;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EstoqueController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()?->papel->podeOperarEstoque() ?? false, 403);
        $ingredientes = Ingrediente::query()->with('unidadeMedida')->orderBy('nome')->get();
        $inventario = Inventario::query()->where('status', 'aberto')->with('itens.ingrediente.unidadeMedida')->latest('iniciado_em')->first();

        return Inertia::render('estoque/index', [
            'podeAdministrar' => $request->user()->papel->podeAdministrarCadastros(),
            'unidades' => UnidadeMedida::query()->orderBy('nome')->get(),
            'ingredientes' => $ingredientes,
            'itensCardapio' => ItemCardapio::query()->with('fichaTecnica.ingrediente.unidadeMedida')->orderBy('nome')->get(),
            'inventarioAberto' => $inventario,
            'movimentacoes' => MovimentacaoEstoque::query()->with(['ingrediente.unidadeMedida', 'usuario'])->latest('registrada_em')->limit(30)->get(),
            'resumo' => ['itens_ativos' => $ingredientes->where('ativo', true)->count(), 'abaixo_minimo' => $ingredientes->where('ativo', true)->filter(fn (Ingrediente $i) => $i->estoque_atual <= $i->estoque_minimo)->count(), 'valor_estoque_centavos' => $ingredientes->sum(fn (Ingrediente $i) => (int) round($i->estoque_atual * $i->custo_medio_centavos))],
        ]);
    }
}
