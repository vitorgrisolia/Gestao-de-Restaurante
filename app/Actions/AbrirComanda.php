<?php

namespace App\Actions;

use App\EstadoMesa;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\User;
use App\StatusComanda;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbrirComanda
{
    public function handle(Mesa $mesa, User $usuario, int $quantidadePessoas): Comanda
    {
        return DB::transaction(function () use ($mesa, $usuario, $quantidadePessoas): Comanda {
            $mesaBloqueada = Mesa::query()->lockForUpdate()->findOrFail($mesa->id);

            if (! $mesaBloqueada->ativa || $mesaBloqueada->estado !== EstadoMesa::Livre) {
                throw ValidationException::withMessages([
                    'mesa_id' => 'A mesa selecionada não está livre.',
                ]);
            }

            if ($mesaBloqueada->comandaAtiva()->exists()) {
                throw ValidationException::withMessages([
                    'mesa_id' => 'A mesa selecionada já possui uma comanda aberta.',
                ]);
            }

            $comanda = $mesaBloqueada->comandas()->create([
                'aberta_por_id' => $usuario->id,
                'quantidade_pessoas' => $quantidadePessoas,
                'status' => StatusComanda::Aberta,
                'ativa' => true,
                'aberta_em' => now(),
            ]);

            $mesaBloqueada->update(['estado' => EstadoMesa::Ocupada]);

            return $comanda;
        });
    }
}
