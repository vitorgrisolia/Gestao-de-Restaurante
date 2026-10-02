<?php

namespace App\Http\Controllers;

use App\Models\SetorSalao;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MapaSalaoController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $setores = SetorSalao::query()
            ->where('ativo', true)
            ->with(['mesas' => fn ($query) => $query
                ->where('ativa', true)
                ->with('comandaAtiva:id,mesa_id,quantidade_pessoas,aberta_em')
                ->orderBy('numero')])
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return Inertia::render('salao/index', [
            'setores' => $setores,
            'permissoes' => [
                'gerenciarSalao' => $request->user()?->papel->podeGerenciarSalao() ?? false,
                'abrirComanda' => $request->user()?->papel->podeAbrirComanda() ?? false,
            ],
        ]);
    }
}
