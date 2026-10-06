<?php

namespace App\Http\Controllers;

use App\Actions\EstornarPagamento;
use App\Http\Requests\StoreEstornoPagamentoRequest;
use App\Models\Pagamento;
use Illuminate\Http\RedirectResponse;

class EstornoPagamentoController extends Controller
{
    public function store(StoreEstornoPagamentoRequest $request, Pagamento $pagamento, EstornarPagamento $estornar): RedirectResponse
    {
        $estornar->handle($pagamento, $request->user(), trim((string) $request->validated('motivo')));

        return back()->with('success', 'Pagamento estornado e auditado.');
    }
}
