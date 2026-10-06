<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnidadeMedidaRequest;
use App\Http\Requests\UpdateUnidadeMedidaRequest;
use App\Models\UnidadeMedida;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UnidadeMedidaController extends Controller
{
    public function store(StoreUnidadeMedidaRequest $request): RedirectResponse
    {
        UnidadeMedida::create($request->validated());

        return back()->with('success', 'Unidade cadastrada.');
    }

    public function update(UpdateUnidadeMedidaRequest $request, UnidadeMedida $unidadeMedida): RedirectResponse
    {
        $unidadeMedida->update($request->validated());

        return back()->with('success', 'Unidade atualizada.');
    }

    public function destroy(Request $request, UnidadeMedida $unidadeMedida): RedirectResponse
    {
        abort_unless($request->user()?->papel->podeAdministrarCadastros() ?? false, 403);
        if ($unidadeMedida->ingredientes()->exists()) {
            throw ValidationException::withMessages(['unidade' => 'A unidade está vinculada a ingredientes. Desative-a em vez de excluir.']);
        } $unidadeMedida->delete();

        return back()->with('success', 'Unidade excluída.');
    }
}
