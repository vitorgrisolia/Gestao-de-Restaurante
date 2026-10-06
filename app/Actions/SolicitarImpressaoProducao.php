<?php

namespace App\Actions;

use App\Models\ImpressaoProducao;
use App\Models\Pedido;
use App\Models\SetorProducao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SolicitarImpressaoProducao
{
    public function handle(Pedido $pedido, SetorProducao $setor, User $usuario, string $chaveIdempotencia): ImpressaoProducao
    {
        return DB::transaction(function () use ($pedido, $setor, $usuario, $chaveIdempotencia): ImpressaoProducao {
            $existente = ImpressaoProducao::query()->where('chave_idempotencia', $chaveIdempotencia)->first();

            if ($existente instanceof ImpressaoProducao) {
                return $existente;
            }

            $possuiItens = $pedido->itens()->where('setor_producao_id', $setor->id)->exists();

            if (! $possuiItens) {
                throw ValidationException::withMessages([
                    'impressao' => 'O pedido não possui itens neste setor.',
                ]);
            }

            Pedido::query()->lockForUpdate()->findOrFail($pedido->id);
            $sequencia = ((int) ImpressaoProducao::query()
                ->whereBelongsTo($pedido)
                ->whereBelongsTo($setor, 'setorProducao')
                ->max('sequencia')) + 1;

            return ImpressaoProducao::create([
                'pedido_id' => $pedido->id,
                'setor_producao_id' => $setor->id,
                'solicitada_por_id' => $usuario->id,
                'chave_idempotencia' => $chaveIdempotencia,
                'sequencia' => $sequencia,
                'tipo' => $sequencia === 1 ? 'inicial' : 'reimpressao',
                'solicitada_em' => now(),
            ]);
        });
    }
}
