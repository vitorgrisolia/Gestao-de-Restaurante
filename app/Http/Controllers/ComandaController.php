<?php

namespace App\Http\Controllers;

use App\Actions\AbrirComanda;
use App\Http\Requests\AbrirComandaRequest;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;

class ComandaController extends Controller
{
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
