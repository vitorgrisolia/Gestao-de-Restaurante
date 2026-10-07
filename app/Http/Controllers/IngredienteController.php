<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIngredienteRequest;
use App\Http\Requests\UpdateIngredienteRequest;
use App\Models\Ingrediente;
use App\Models\InventarioItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IngredienteController extends Controller
{
    public function store(StoreIngredienteRequest $request): RedirectResponse
    {
        Ingrediente::create($request->validated());

        return back()->with('success', 'Ingrediente cadastrado. Registre uma entrada para informar o saldo.');
    }

    public function update(UpdateIngredienteRequest $request, Ingrediente $ingrediente): RedirectResponse
    {
        $ingrediente->update($request->validated());

        return back()->with('success', 'Ingrediente atualizado.');
    }

    public function destroy(Request $request, Ingrediente $ingrediente): RedirectResponse
    {
        abort_unless($request->user()?->papel->podeAdministrarCadastros() ?? false, 403);
        if ($ingrediente->fichasTecnicas()->exists() || $ingrediente->movimentacoes()->exists() || InventarioItem::query()->where('ingrediente_id', $ingrediente->id)->exists()) {
            throw ValidationException::withMessages(['ingrediente' => 'Há histórico ou ficha técnica vinculada. Desative o ingrediente.']);
        } $ingrediente->delete();

        return back()->with('success', 'Ingrediente excluído.');
    }
}
