<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateContaComandaRequest;
use App\Models\Comanda;
use Illuminate\Http\RedirectResponse;

class ContaComandaController extends Controller
{
    public function update(UpdateContaComandaRequest $request, Comanda $comanda): RedirectResponse
    {
        $comanda->update(['servico_percentual' => $request->integer('servico_percentual'), 'couvert_por_pessoa_centavos' => $request->dinheiro('couvert'), 'desconto_centavos' => $request->dinheiro('desconto'), 'acrescimo_centavos' => $request->dinheiro('acrescimo')]);

        return back()->with('success', 'Valores da conta atualizados.');
    }
}
