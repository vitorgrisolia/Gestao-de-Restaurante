<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSetorSalaoRequest;
use App\Http\Requests\UpdateSetorSalaoRequest;
use App\Models\SetorSalao;
use Illuminate\Http\RedirectResponse;

class SetorSalaoController extends Controller
{
    public function store(StoreSetorSalaoRequest $request): RedirectResponse
    {
        SetorSalao::create($request->validated());

        return back()->with('success', 'Setor cadastrado com sucesso.');
    }

    public function update(UpdateSetorSalaoRequest $request, SetorSalao $setorSalao): RedirectResponse
    {
        $setorSalao->update($request->validated());

        return back()->with('success', 'Setor atualizado com sucesso.');
    }
}
