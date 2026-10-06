<?php

namespace App\Actions;

use App\Models\Comanda;
use App\Models\Pedido;
use App\Models\SetorProducao;
use App\StatusComanda;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnviarPedido
{
    public function handle(Pedido $pedido): Pedido
    {
        return DB::transaction(function () use ($pedido): Pedido {
            $pedidoBloqueado = Pedido::query()->lockForUpdate()->findOrFail($pedido->id);
            $comanda = Comanda::query()->lockForUpdate()->findOrFail($pedidoBloqueado->comanda_id);

            if (! $comanda->ativa || $comanda->status !== StatusComanda::Aberta) {
                throw ValidationException::withMessages([
                    'pedido' => 'A comanda não está aberta para enviar pedidos.',
                ]);
            }

            if ($pedidoBloqueado->status !== StatusPedido::Rascunho) {
                throw ValidationException::withMessages([
                    'pedido' => 'Somente pedidos em rascunho podem ser enviados.',
                ]);
            }

            $enviadoEm = now();
            $pedidoBloqueado->update([
                'status' => StatusPedido::Enviado,
                'enviado_em' => $enviadoEm,
            ]);
            $pedidoBloqueado->itens()
                ->where('status', StatusItemPedido::Rascunho)
                ->update([
                    'status' => StatusItemPedido::Enviado,
                    'enviado_em' => $enviadoEm,
                ]);

            $setores = SetorProducao::query()
                ->whereIn('id', $pedidoBloqueado->itens()->whereNotNull('setor_producao_id')->pluck('setor_producao_id'))
                ->get();

            foreach ($setores as $setor) {
                $pedidoBloqueado->impressoesProducao()->firstOrCreate([
                    'setor_producao_id' => $setor->id,
                    'sequencia' => 1,
                ], [
                    'solicitada_por_id' => $pedidoBloqueado->criado_por_id,
                    'chave_idempotencia' => (string) Str::uuid(),
                    'tipo' => 'inicial',
                    'solicitada_em' => $enviadoEm,
                ]);
            }

            return $pedidoBloqueado->load('itens');
        });
    }
}
