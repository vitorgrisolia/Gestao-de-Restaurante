<?php

namespace App\Actions;

use App\Models\Comanda;
use App\Models\MovimentacaoEstoque;
use App\Models\Pedido;
use App\Models\User;
use App\StatusComanda;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelarPedido
{
    public function __construct(private MovimentarEstoque $movimentarEstoque) {}

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
            $itensNaoPreparados = $pedidoBloqueado->itens()
                ->where('status', StatusItemPedido::Enviado)
                ->whereNull('iniciado_em')
                ->lockForUpdate()
                ->pluck('id');
            $baixas = MovimentacaoEstoque::query()
                ->whereIn('pedido_item_id', $itensNaoPreparados)
                ->where('tipo', 'baixa')
                ->with('ingrediente')
                ->orderBy('ingrediente_id')
                ->get();

            foreach ($baixas as $baixa) {
                $this->movimentarEstoque->handle(
                    $baixa->ingrediente,
                    $usuario,
                    'devolucao',
                    abs((float) $baixa->quantidade),
                    "Cancelamento do pedido #{$pedidoBloqueado->id}: {$motivo}",
                    $baixa->custo_unitario_centavos,
                    $baixa->pedido_item_id,
                    "cancelamento:baixa:{$baixa->id}",
                );
            }

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
