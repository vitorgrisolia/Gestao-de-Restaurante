<?php

namespace App\Actions;

use App\Models\Caixa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbrirCaixa
{
    public function handle(User $usuario, int $valor): Caixa
    {
        return DB::transaction(function () use ($usuario, $valor) {
            if (Caixa::query()->where('aberto', true)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['caixa' => 'Já existe um caixa aberto.']);
            }$caixa = Caixa::create(['aberto_por_id' => $usuario->id, 'aberto' => true, 'valor_abertura_centavos' => $valor, 'aberto_em' => now()]);
            $caixa->movimentos()->create(['usuario_id' => $usuario->id, 'tipo' => 'abertura', 'valor_centavos' => $valor, 'descricao' => 'Abertura do caixa', 'registrado_em' => now()]);

            return $caixa;
        });
    }
}
