<?php

namespace App\Actions;

use App\EstadoMesa;
use App\Models\Caixa;
use App\Models\Pagamento;
use App\Models\User;
use App\StatusComanda;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstornarPagamento
{
    public function handle(Pagamento $pagamento, User $usuario, string $motivo): Pagamento
    {
        return DB::transaction(function () use ($pagamento, $usuario, $motivo) {
            $p = Pagamento::query()->lockForUpdate()->findOrFail($pagamento->id);
            if ($p->estornado_em) {
                throw ValidationException::withMessages(['pagamento' => 'Este pagamento já foi estornado.']);
            }$p->update(['estornado_por_id' => $usuario->id, 'motivo_estorno' => $motivo, 'estornado_em' => now()]);
            $caixa = $p->caixa()->first();
            if ($caixa instanceof Caixa) {
                $caixa->movimentos()->create(['usuario_id' => $usuario->id, 'pagamento_id' => $p->id, 'tipo' => 'estorno', 'valor_centavos' => -$p->valor_centavos, 'descricao' => 'Estorno: '.$motivo, 'registrado_em' => now()]);
            }$comanda = $p->comanda;
            if ($comanda->status === StatusComanda::Fechada) {
                $comanda->update(['status' => StatusComanda::Aberta, 'ativa' => true, 'fechada_em' => null]);
                $comanda->mesa()->update(['estado' => EstadoMesa::Ocupada]);
            }

            return $p;
        });
    }
}
