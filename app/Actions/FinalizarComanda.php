<?php

namespace App\Actions;

use App\EstadoMesa;
use App\FormaPagamento;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Pagamento;
use App\Models\PedidoItem;
use App\Models\User;
use App\StatusComanda;
use App\StatusItemPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinalizarComanda
{
    public function handle(Comanda $comanda, User $usuario, FormaPagamento $formaPagamento): Pagamento
    {
        return DB::transaction(function () use ($comanda, $usuario, $formaPagamento): Pagamento {
            $comandaBloqueada = Comanda::query()
                ->lockForUpdate()
                ->findOrFail($comanda->id);

            if (! $comandaBloqueada->ativa || $comandaBloqueada->status !== StatusComanda::Aberta) {
                throw ValidationException::withMessages([
                    'comanda' => 'Esta comanda já foi fechada ou cancelada.',
                ]);
            }

            $mesa = Mesa::query()->lockForUpdate()->findOrFail($comandaBloqueada->mesa_id);

            $itens = PedidoItem::query()
                ->whereHas('pedido', fn ($query) => $query->where('comanda_id', $comandaBloqueada->id))
                ->where('status', '!=', StatusItemPedido::Cancelado)
                ->get();

            $totalCentavos = $itens->sum(
                fn (PedidoItem $item): int => $item->subtotalCentavos(),
            );

            if ($totalCentavos <= 0) {
                throw ValidationException::withMessages([
                    'comanda' => 'Adicione pelo menos um item antes de fechar a comanda.',
                ]);
            }

            $pagamento = $comandaBloqueada->pagamentos()->create([
                'recebido_por_id' => $usuario->id,
                'forma' => $formaPagamento,
                'valor_centavos' => $totalCentavos,
                'pago_em' => now(),
            ]);

            $comandaBloqueada->update([
                'status' => StatusComanda::Fechada,
                'ativa' => false,
                'fechada_em' => now(),
            ]);

            $mesa->update(['estado' => EstadoMesa::Livre]);

            return $pagamento;
        });
    }
}
