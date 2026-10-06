<?php

namespace App\Actions;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AtualizarStatusItemProducao
{
    public function handle(PedidoItem $item, StatusItemPedido $novoStatus, User $usuario): PedidoItem
    {
        return DB::transaction(function () use ($item, $novoStatus, $usuario): PedidoItem {
            $itemBloqueado = PedidoItem::query()->lockForUpdate()->findOrFail($item->id);
            $transicoes = [
                StatusItemPedido::Enviado->value => StatusItemPedido::EmPreparo,
                StatusItemPedido::EmPreparo->value => StatusItemPedido::Pronto,
                StatusItemPedido::Pronto->value => StatusItemPedido::Entregue,
            ];

            if (($transicoes[$itemBloqueado->status->value] ?? null) !== $novoStatus) {
                throw ValidationException::withMessages([
                    'status' => 'O item não pode avançar para esse status.',
                ]);
            }

            $agora = now();
            $atributos = match ($novoStatus) {
                StatusItemPedido::EmPreparo => ['iniciado_em' => $agora, 'iniciado_por_id' => $usuario->id],
                StatusItemPedido::Pronto => ['pronto_em' => $agora, 'pronto_por_id' => $usuario->id],
                StatusItemPedido::Entregue => ['entregue_em' => $agora, 'entregue_por_id' => $usuario->id],
            };

            $itemBloqueado->update(['status' => $novoStatus, ...$atributos]);
            $this->sincronizarPedido($itemBloqueado->pedido_id);

            return $itemBloqueado->refresh();
        });
    }

    private function sincronizarPedido(int $pedidoId): void
    {
        $pedido = Pedido::query()->lockForUpdate()->findOrFail($pedidoId);
        $itens = $pedido->itens()->where('status', '!=', StatusItemPedido::Cancelado)->get();

        if ($itens->isEmpty()) {
            return;
        }

        $status = match (true) {
            $itens->every(fn (PedidoItem $item): bool => $item->status === StatusItemPedido::Entregue) => StatusPedido::Entregue,
            $itens->every(fn (PedidoItem $item): bool => in_array($item->status, [StatusItemPedido::Pronto, StatusItemPedido::Entregue], true)) => StatusPedido::Pronto,
            $itens->contains(fn (PedidoItem $item): bool => in_array($item->status, [StatusItemPedido::EmPreparo, StatusItemPedido::Pronto, StatusItemPedido::Entregue], true)) => StatusPedido::EmPreparo,
            default => StatusPedido::Enviado,
        };

        $pedido->update([
            'status' => $status,
            'iniciado_em' => $status !== StatusPedido::Enviado ? ($pedido->iniciado_em ?? now()) : null,
            'pronto_em' => in_array($status, [StatusPedido::Pronto, StatusPedido::Entregue], true) ? ($pedido->pronto_em ?? now()) : null,
            'entregue_em' => $status === StatusPedido::Entregue ? ($pedido->entregue_em ?? now()) : null,
        ]);
    }
}
