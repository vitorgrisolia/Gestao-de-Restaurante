<?php

namespace App\Http\Controllers;

use App\Actions\RegistrarPagamento;
use App\FormaPagamento;
use App\Http\Requests\StorePagamentoRequest;
use App\Models\Comanda;
use Illuminate\Http\RedirectResponse;

class PagamentoController extends Controller
{
    public function store(StorePagamentoRequest $request, Comanda $comanda, RegistrarPagamento $registrar): RedirectResponse
    {
        $pagamento = $registrar->handle($comanda, $request->user(), FormaPagamento::from((string) $request->validated('forma_pagamento')), (string) $request->validated('tipo_divisao'), $request->valorCentavos(), $request->itens());

        return $pagamento->comanda->refresh()->ativa ? back()->with('success', 'Pagamento parcial registrado.') : redirect()->route('salao.index')->with('success', 'Pagamento registrado, comanda fechada e mesa liberada.');
    }
}
