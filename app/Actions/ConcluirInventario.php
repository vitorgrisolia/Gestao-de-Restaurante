<?php

namespace App\Actions;

use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConcluirInventario
{
    public function __construct(private MovimentarEstoque $movimentarEstoque) {}

    /** @param array<int, float> $contagens */
    public function handle(Inventario $inventario, User $usuario, array $contagens): Inventario
    {
        return DB::transaction(function () use ($inventario, $usuario, $contagens): Inventario {
            $inventarioBloqueado = Inventario::query()->lockForUpdate()->findOrFail($inventario->id);

            if ($inventarioBloqueado->status !== 'aberto') {
                throw ValidationException::withMessages(['inventario' => 'Este inventário já foi concluído.']);
            }

            foreach ($inventarioBloqueado->itens()->orderBy('ingrediente_id')->get() as $item) {
                $contada = $contagens[$item->id] ?? null;

                if ($contada === null || $contada < 0) {
                    throw ValidationException::withMessages(['contagens' => 'Informe a contagem de todos os ingredientes.']);
                }

                $ingrediente = Ingrediente::query()->lockForUpdate()->findOrFail($item->ingrediente_id);
                $diferenca = round($contada - (float) $item->quantidade_sistema, 3);

                if ($diferenca !== 0.0) {
                    $this->movimentarEstoque->handle($ingrediente, $usuario, 'inventario', $diferenca, "Inventário #{$inventarioBloqueado->id}", chaveIdempotencia: "inventario:{$inventarioBloqueado->id}:ingrediente:{$ingrediente->id}");
                }

                $item->update(['quantidade_contada' => $contada, 'diferenca' => $diferenca]);
            }

            $inventarioBloqueado->update(['status' => 'concluido', 'concluido_por_id' => $usuario->id, 'concluido_em' => now()]);

            return $inventarioBloqueado->refresh();
        });
    }
}
