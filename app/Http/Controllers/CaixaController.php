<?php

namespace App\Http\Controllers;

use App\Actions\AbrirCaixa;
use App\Actions\FecharCaixa;
use App\Http\Requests\StoreCaixaRequest;
use App\Http\Requests\UpdateCaixaRequest;
use App\Models\Caixa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaixaController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->papel->podeReceberPagamento() ?? false, 403);

        return Inertia::render('caixa/index', ['caixa' => Caixa::query()->with(['abertoPor:id,name', 'movimentos' => fn ($q) => $q->latest('id')])->where('aberto', true)->latest('id')->first()]);
    }

    public function store(StoreCaixaRequest $request, AbrirCaixa $abrir): RedirectResponse
    {
        $abrir->handle($request->user(), (int) round($request->float('valor_abertura') * 100));

        return back()->with('success', 'Caixa aberto.');
    }

    public function update(UpdateCaixaRequest $request, Caixa $caixa, FecharCaixa $fechar): RedirectResponse
    {
        $fechar->handle($caixa, $request->user(), (int) round($request->float('valor_informado') * 100));

        return back()->with('success','Caixa fechado e conferido.');
    }
}
