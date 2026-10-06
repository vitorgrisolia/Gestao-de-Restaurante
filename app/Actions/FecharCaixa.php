<?php

namespace App\Actions;

use App\Models\Caixa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FecharCaixa
{
    public function handle(Caixa $caixa, User $usuario, int $informado): Caixa
    {
        return DB::transaction(function () use ($caixa, $usuario, $informado) {
            $bloqueado = Caixa::query()->lockForUpdate()->findOrFail($caixa->id);
            if (! $bloqueado->aberto) {
                throw ValidationException::withMessages(['caixa' => 'Este caixa já foi fechado.']);
            }$esperado = $bloqueado->valor_abertura_centavos + (int) $bloqueado->movimentos()->where('tipo', 'entrada')->sum('valor_centavos') + (int) $bloqueado->movimentos()->where('tipo', 'estorno')->sum('valor_centavos');
            $bloqueado->update(['fechado_por_id' => $usuario->id, 'aberto' => false, 'valor_esperado_centavos' => $esperado, 'valor_informado_centavos' => $informado, 'diferenca_centavos' => $informado - $esperado, 'fechado_em' => now()]);

            return $bloqueado;
        });
    }
}
