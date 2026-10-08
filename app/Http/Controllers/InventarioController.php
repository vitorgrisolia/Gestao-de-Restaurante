<?php

namespace App\Http\Controllers;

use App\Actions\ConcluirInventario;
use App\Http\Requests\ConcluirInventarioRequest;
use App\Http\Requests\ReiniciarInventarioRequest;
use App\Http\Requests\StoreInventarioRequest;
use App\Models\Ingrediente;
use App\Models\Inventario;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\Repository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioController extends Controller
{
    public function reiniciar(ReiniciarInventarioRequest $request, Inventario $inventario): RedirectResponse
    {
        /** @var array<int, array{id: int, quantidade_contada: numeric-string|int|float|null}> $itens */
        $itens = $request->validated('itens', []);
        $contagens = collect($itens)->keyBy('id');
        DB::transaction(function () use ($inventario, $contagens): void {
            $contagem = Inventario::query()->lockForUpdate()->findOrFail($inventario->id);
            if ($contagem->status !== 'aberto') {
                throw ValidationException::withMessages(['inventario' => 'Somente inventários abertos podem ser recontados.']);
            }
            foreach ($contagem->itens()->orderBy('ingrediente_id')->get() as $item) {
                $ingrediente = Ingrediente::query()->lockForUpdate()->findOrFail($item->ingrediente_id);
                if (round((float) $ingrediente->estoque_atual, 3) === round((float) $item->quantidade_sistema, 3)) {
                    if ($contagens->has($item->id)) {
                        $item->update(['quantidade_contada' => $contagens->get($item->id)['quantidade_contada']]);
                    }

                    continue;
                }
                $item->update(['quantidade_sistema' => $ingrediente->estoque_atual, 'quantidade_contada' => null, 'diferenca' => null]);
            }
        });

        return back()->with('success', 'Referências atualizadas somente dos ingredientes com saldo alterado. Reconte esses ingredientes; os demais foram preservados.');
    }

    public function store(StoreInventarioRequest $request): RedirectResponse
    {
        /** @var Repository $cache */
        $cache = Cache::store('database');
        /** @var DatabaseStore $armazenamento */
        $armazenamento = $cache->getStore();
        $resultado = $armazenamento->lock('inventarios:abertura', 60)->get(function () use ($request): void {
            DB::transaction(function () use ($request): void {
                if (Inventario::query()->where('status', 'aberto')->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['inventario' => 'Conclua o inventário aberto antes de iniciar outro.']);
                }

                $inventario = Inventario::create(['iniciado_por_id' => $request->user()->id, 'status' => 'aberto', 'observacao' => $request->validated('observacao'), 'iniciado_em' => now()]);
                $inventario->itens()->createMany(Ingrediente::query()->where('ativo', true)->orderBy('id')->lockForUpdate()->get()->map(fn (Ingrediente $i) => ['ingrediente_id' => $i->id, 'quantidade_sistema' => $i->estoque_atual])->all());
            });
        });

        if ($resultado === false) {
            throw ValidationException::withMessages(['inventario' => 'Outro inventário está sendo iniciado. Aguarde e tente novamente.']);
        }

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
