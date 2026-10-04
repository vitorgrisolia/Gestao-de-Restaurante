<?php

namespace App\Http\Controllers;

use App\Actions\FinalizarComanda;
use App\FormaPagamento;
use App\Http\Requests\StorePagamentoRequest;
use App\Models\Comanda;
use Illuminate\Http\RedirectResponse;

class PagamentoController extends Controller
{
    public function store(
        StorePagamentoRequest $request,
        Comanda $comanda,
        FinalizarComanda $finalizarComanda,
    ): RedirectResponse {
        $finalizarComanda->handle(
            $comanda,
            $request->user(),
            FormaPagamento::from($request->string('forma_pagamento')->toString()),
        );

        return redirect()
            ->route('salao.index')
            ->with('success', 'Pagamento registrado, comanda fechada e mesa liberada.');
    }
}
