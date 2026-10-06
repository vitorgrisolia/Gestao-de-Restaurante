<?php

namespace App\Actions;

use App\Models\Comanda;
use App\Models\Pedido;
use App\Models\User;
use App\StatusComanda;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelarPedido
{
    public function handle(Pedido $pedido, User $usuario, string $motivo): Pedido
    {
        return DB::transaction(function () use ($pedido, $usuario, $motivo): Pedido {
            $pedidoBloqueado = Pedido::query()->lockForUpdate()->findOrFail($pedido->id);
            $comanda = Comanda::query()->lockForUpdate()->findOrFail($pedidoBloqueado->comanda_id);

            if (! $comanda->ativa || $comanda->status !== StatusComanda::Aberta) {
                throw ValidationException::withMessages([
                    'pedido' => 'A comanda não está aberta para cancelar pedidos.',
                ]);
            }

            if (! in_array($pedidoBloqueado->status, [StatusPedido::Enviado, StatusPedido::EmPreparo, StatusPedido::Pronto], true)) {
                throw ValidationException::withMessages([
                    'pedido' => 'Este pedido não pode mais ser cancelado.',
                ]);
            }

            $canceladoEm = now();
            $pedidoBloqueado->update([
                'status' => StatusPedido::Cancelado,
                'cancelado_em' => $canceladoEm,
            ]);
            $pedidoBloqueado->itens()
                ->where('status', '!=', StatusItemPedido::Cancelado)
                ->update([
                    'status' => StatusItemPedido::Cancelado,
                    'cancelado_por_id' => $usuario->id,
                    'motivo_cancelamento' => $motivo,
                    'cancelado_em' => $canceladoEm,
                ]);

            return $pedidoBloqueado->load(['itens.canceladoPor']);
        });
    }
}
