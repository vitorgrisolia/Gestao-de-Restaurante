<?php

namespace App\Actions;

use App\EstadoMesa;
use App\FormaPagamento;
use App\Models\Caixa;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Pagamento;
use App\Models\PedidoItem;
use App\Models\User;
use App\StatusComanda;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarPagamento
{
    public function __construct(private CalcularContaComanda $calcularConta) {}

    /** @param list<int> $itens */
    public function handle(Comanda $comanda, User $usuario, FormaPagamento $forma, string $tipoDivisao, int $valorInformado, array $itens = []): Pagamento
    {
        return DB::transaction(function () use ($comanda, $usuario, $forma, $tipoDivisao, $valorInformado, $itens): Pagamento {
            $conta = Comanda::query()->lockForUpdate()->findOrFail($comanda->id);

            if (! $conta->ativa || $conta->status !== StatusComanda::Aberta) {
                throw ValidationException::withMessages(['comanda' => 'Esta comanda já foi fechada ou cancelada.']);
            }

            $totais = $this->calcularConta->handle($conta);

            if ($totais['total'] <= 0) {
                throw ValidationException::withMessages(['comanda' => 'Adicione pelo menos um item antes de fechar a comanda.']);
            }

            $caixa = Caixa::query()->where('aberto', true)->lockForUpdate()->latest('id')->first();

            if (! $caixa instanceof Caixa) {
                throw ValidationException::withMessages(['caixa' => 'Abra o caixa antes de receber pagamentos.']);
            }

            $valorSolicitado = $this->valorSolicitado($conta, $tipoDivisao, $valorInformado, $itens, $totais['saldo']);
            $valorAplicado = min($valorSolicitado, $totais['saldo']);
            $valorRecebido = $forma === FormaPagamento::Dinheiro ? max($valorInformado, $valorAplicado) : $valorAplicado;

            $pagamento = $conta->pagamentos()->create([
                'caixa_id' => $caixa->id,
                'recebido_por_id' => $usuario->id,
                'forma' => $forma,
                'valor_centavos' => $valorAplicado,
                'valor_recebido_centavos' => $valorRecebido,
                'troco_centavos' => max(0, $valorRecebido - $valorAplicado),
                'tipo_divisao' => $tipoDivisao,
                'referencia_divisao' => $tipoDivisao === 'itens' ? $itens : null,
                'pago_em' => now(),
            ]);

            $caixa->movimentos()->create(['usuario_id' => $usuario->id, 'pagamento_id' => $pagamento->id, 'tipo' => 'entrada', 'valor_centavos' => $valorAplicado, 'descricao' => 'Pagamento da comanda #'.$conta->id, 'registrado_em' => now()]);

            if ($valorAplicado === $totais['saldo']) {
                $conta->update(['status' => StatusComanda::Fechada, 'ativa' => null, 'fechada_em' => now()]);
                Mesa::query()->whereKey($conta->mesa_id)->update(['estado' => EstadoMesa::Livre]);
            }

            return $pagamento;
        });
    }

    /** @param list<int> $itens */
    private function valorSolicitado(Comanda $comanda, string $tipo, int $valor, array $itens, int $saldo): int
    {
        $solicitado = match ($tipo) {
            'integral' => $saldo,
            'itens' => (int) PedidoItem::query()->whereIn('id', $itens)->whereHas('pedido', fn ($query) => $query->where('comanda_id', $comanda->id))->get()->sum(fn (PedidoItem $item): int => $item->subtotalCentavos()),
            default => $valor,
        };

        if ($solicitado <= 0) {
            throw ValidationException::withMessages(['valor' => 'Informe um valor maior que zero.']);
        }

        return $solicitado;
    }
}
