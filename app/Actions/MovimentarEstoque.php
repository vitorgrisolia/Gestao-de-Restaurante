<?php

namespace App\Actions;

use App\Models\Ingrediente;
use App\Models\MovimentacaoEstoque;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovimentarEstoque
{
    public function handle(Ingrediente $ingrediente, User $usuario, string $tipo, float $quantidade, ?string $motivo = null, ?int $custoUnitarioCentavos = null, ?int $pedidoItemId = null, ?string $chaveIdempotencia = null): MovimentacaoEstoque
    {
        return DB::transaction(function () use ($ingrediente, $usuario, $tipo, $quantidade, $motivo, $custoUnitarioCentavos, $pedidoItemId, $chaveIdempotencia): MovimentacaoEstoque {
            if ($chaveIdempotencia !== null) {
                $existente = MovimentacaoEstoque::query()->where('chave_idempotencia', $chaveIdempotencia)->first();

                if ($existente instanceof MovimentacaoEstoque) {
                    return $existente;
                }
            }

            $ingredienteBloqueado = Ingrediente::query()->lockForUpdate()->findOrFail($ingrediente->id);
            $saldoAnterior = (float) $ingredienteBloqueado->estoque_atual;
            $saldoPosterior = round($saldoAnterior + $quantidade, 3);

            if ($saldoPosterior < 0) {
                throw ValidationException::withMessages([
                    'estoque' => "Estoque insuficiente de {$ingredienteBloqueado->nome}.",
                ]);
            }

            $custo = $custoUnitarioCentavos ?? $ingredienteBloqueado->custo_medio_centavos;

            if ($tipo === 'entrada' && $quantidade > 0 && $custoUnitarioCentavos !== null) {
                $valorAnterior = $saldoAnterior * $ingredienteBloqueado->custo_medio_centavos;
                $ingredienteBloqueado->custo_medio_centavos = max(0, (int) round(($valorAnterior + ($quantidade * $custoUnitarioCentavos)) / max($saldoPosterior, 0.001)));
            }

            $ingredienteBloqueado->estoque_atual = $saldoPosterior;
            $ingredienteBloqueado->save();

            return MovimentacaoEstoque::create([
                'ingrediente_id' => $ingredienteBloqueado->id,
                'usuario_id' => $usuario->id,
                'pedido_item_id' => $pedidoItemId,
                'tipo' => $tipo,
                'quantidade' => number_format($quantidade, 3, '.', ''),
                'saldo_anterior' => number_format($saldoAnterior, 3, '.', ''),
                'saldo_posterior' => number_format($saldoPosterior, 3, '.', ''),
                'custo_unitario_centavos' => $custo,
                'motivo' => $motivo,
                'chave_idempotencia' => $chaveIdempotencia,
                'registrada_em' => now(),
            ]);
        });
    }
}
