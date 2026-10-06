<?php

namespace App\Http\Controllers;

use App\Actions\ConcluirInventario;
use App\Http\Requests\ConcluirInventarioRequest;
use App\Http\Requests\StoreInventarioRequest;
use App\Models\Ingrediente;
use App\Models\Inventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioController extends Controller
{
    public function store(StoreInventarioRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            if (Inventario::query()->where('status', 'aberto')->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['inventario' => 'Conclua o inventário aberto antes de iniciar outro.']);
            }$inventario = Inventario::create(['iniciado_por_id' => $request->user()->id, 'status' => 'aberto', 'observacao' => $request->validated('observacao'), 'iniciado_em' => now()]);
            $inventario->itens()->createMany(Ingrediente::query()->where('ativo', true)->get()->map(fn (Ingrediente $i) => ['ingrediente_id' => $i->id, 'quantidade_sistema' => $i->estoque_atual])->all());
        });

        return back()->with('success', 'Inventário iniciado. Informe as quantidades contadas.');
    }

    public function update(ConcluirInventarioRequest $request, Inventario $inventario, ConcluirInventario $concluir): RedirectResponse
    {
        /** @var array<int, array{id: int, quantidade_contada: numeric-string|int|float}> $itens */
        $itens = $request->validated('itens');
        $contagens = collect($itens)->mapWithKeys(fn (array $item): array => [(int) $item['id'] => (float) $item['quantidade_contada']])->all();
        $concluir->handle($inventario, $request->user(), $contagens);

        return back()->with('success', 'Inventário concluído e diferenças ajustadas.');
    }
}
