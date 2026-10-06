<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFichaTecnicaRequest;
use App\Models\ItemCardapio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class FichaTecnicaController extends Controller
{
    public function update(StoreFichaTecnicaRequest $request, ItemCardapio $itemCardapio): RedirectResponse
    {
        DB::transaction(function () use ($request, $itemCardapio): void {
            $itemCardapio->fichaTecnica()->delete();
            $itemCardapio->fichaTecnica()->createMany($request->validated('ingredientes'));
        });

        return back()->with('success', 'Ficha técnica atualizada.');
    }
}
