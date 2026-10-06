<?php

namespace App\Http\Controllers;

use App\Models\PedidoItem;
use App\Models\SetorProducao;
use App\Models\User;
use App\StatusItemPedido;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PainelProducaoController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()?->papel->podeOperarProducao() ?? false, 403);

        $setorSelecionado = $request->integer('setor');
        $setores = SetorProducao::query()->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']);

        if ($setorSelecionado === 0) {
            $primeiroSetor = $setores->first();
            $setorSelecionado = $primeiroSetor instanceof SetorProducao ? $primeiroSetor->id : 0;
        }

        $itens = PedidoItem::query()
            ->where('setor_producao_id', $setorSelecionado)
            ->whereIn('status', [StatusItemPedido::Enviado, StatusItemPedido::EmPreparo, StatusItemPedido::Pronto, StatusItemPedido::Entregue])
            ->where(function ($query): void {
                $query->where('status', '!=', StatusItemPedido::Entregue)
                    ->orWhere('entregue_em', '>=', now()->subHours(2));
            })
            ->with([
                'pedido:id,comanda_id,observacao',
                'pedido.comanda:id,mesa_id',
                'pedido.comanda.mesa:id,numero',
                'iniciadoPor:id,name',
                'prontoPor:id,name',
                'entreguePor:id,name',
            ])
            ->orderByRaw("case status when 'enviado' then 1 when 'em_preparo' then 2 when 'pronto' then 3 else 4 end")
            ->orderBy('enviado_em')
            ->get()
            ->map(fn (PedidoItem $item): array => [
                'id' => $item->id,
                'pedido_id' => $item->pedido_id,
                'mesa' => $item->pedido->comanda->mesa->numero,
                'nome_item' => $item->nome_item,
                'quantidade' => $item->quantidade,
                'observacao' => $item->observacao,
                'observacao_pedido' => $item->pedido->observacao,
                'status' => $item->status->value,
                'enviado_em' => $item->enviado_em?->toIso8601String(),
                'iniciado_em' => $item->iniciado_em?->toIso8601String(),
                'pronto_em' => $item->pronto_em?->toIso8601String(),
                'entregue_em' => $item->entregue_em?->toIso8601String(),
                'responsavel' => $this->responsavelAtual($item),
                'atrasado' => $item->status === StatusItemPedido::Enviado && $item->enviado_em?->lt(now()->subMinutes(15)),
            ]);

        return Inertia::render('producao/painel', [
            'setores' => $setores,
            'setorSelecionado' => $setorSelecionado,
            'itens' => $itens,
        ]);
    }

    private function responsavelAtual(PedidoItem $item): ?string
    {
        foreach (['entreguePor', 'prontoPor', 'iniciadoPor'] as $relacao) {
            $usuario = $item->getRelation($relacao);

            if ($usuario instanceof User) {
                return $usuario->name;
            }
        }

        return null;
    }
}
