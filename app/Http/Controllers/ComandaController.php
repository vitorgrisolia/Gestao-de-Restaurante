<?php

namespace App\Http\Controllers;

use App\Actions\AbrirComanda;
use App\Actions\CalcularContaComanda;
use App\FormaPagamento;
use App\Http\Requests\AbrirComandaRequest;
use App\Models\CategoriaCardapio;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\PedidoItem;
use App\StatusItemPedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComandaController extends Controller
{
    public function show(Request $request, Comanda $comanda, CalcularContaComanda $calcularConta): Response
    {
        abort_unless($request->user()?->papel->podeRegistrarPedido() ?? false, 403);

        $comanda->load('mesa:id,numero');

        $pedidos = $comanda->pedidos()
            ->with(['criadoPor:id,name', 'itens.canceladoPor:id,name'])
            ->orderByDesc('id')
            ->get();

        $totalComandaCentavos = $pedidos->flatMap->itens
            ->reject(fn (PedidoItem $item): bool => $item->status === StatusItemPedido::Cancelado)
            ->sum(fn (PedidoItem $item): int => $item->subtotalCentavos());

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
            'totalComandaCentavos' => $totalComandaCentavos,
            'conta' => $calcularConta->handle($comanda),
            'pagamentos' => $comanda->pagamentos()->with('recebidoPor:id,name')->latest('id')->get(),
            'podeFecharComanda' => $request->user()->papel->podeReceberPagamento(),
            'formasPagamento' => array_map(
                fn (FormaPagamento $forma): array => ['valor' => $forma->value, 'nome' => $forma->nome()],
                FormaPagamento::cases(),
            ),
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
