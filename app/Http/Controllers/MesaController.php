<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMesaRequest;
use App\Http\Requests\UpdateMesaRequest;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;

class MesaController extends Controller
{
    public function store(StoreMesaRequest $request): RedirectResponse
    {
        Mesa::create($request->validated());

        return back()->with('success', 'Mesa cadastrada com sucesso.');
    }

    public function update(UpdateMesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $mesa->update($request->validated());

        return back()->with('success', 'Mesa atualizada com sucesso.');
    }
}
