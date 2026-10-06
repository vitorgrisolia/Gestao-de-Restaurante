<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSetorProducaoRequest;
use App\Http\Requests\UpdateSetorProducaoRequest;
use App\Models\SetorProducao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SetorProducaoController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->papel->podeAdministrarCadastros() ?? false, 403);

        return Inertia::render('producao/setores/index', [
            'setores' => SetorProducao::query()
                ->withCount('itensCardapio')
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function store(StoreSetorProducaoRequest $request): RedirectResponse
    {
        SetorProducao::create($request->safe()->only([
            'nome', 'descricao', 'ativo', 'ordem',
        ]));

        return back()->with('success', 'Setor de produção cadastrado com sucesso.');
    }

    public function update(UpdateSetorProducaoRequest $request, SetorProducao $setorProducao): RedirectResponse
    {
        $setorProducao->update($request->safe()->only([
            'nome', 'descricao', 'ativo', 'ordem',
        ]));

        return back()->with('success', 'Setor de produção atualizado com sucesso.');
    }

    public function destroy(Request $request, SetorProducao $setorProducao): RedirectResponse
    {
        abort_unless($request->user()?->papel->podeAdministrarCadastros() ?? false, 403);

        if ($setorProducao->itensCardapio()->exists() || $setorProducao->itensPedido()->exists() || $setorProducao->impressoes()->exists()) {
            throw ValidationException::withMessages([
                'setor' => 'Este setor possui itens ou histórico de produção e não pode ser excluído.',
            ]);
        }

        $setorProducao->delete();

        return back()->with('success', 'Setor de produção excluído com sucesso.');
    }
}
