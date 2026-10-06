<?php

namespace Tests\Feature;

use App\EstadoMesa;
use App\FormaPagamento;
use App\Models\Caixa;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FechamentoComandaRecorrenteTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_permite_fechar_mais_de_uma_comanda_da_mesma_mesa(): void
    {
        $caixa = User::factory()->create(['papel' => PapelUsuario::Caixa]);
        Caixa::factory()->create(['aberto_por_id' => $caixa->id, 'aberto' => true]);
        $mesa = Mesa::factory()->create(['estado' => EstadoMesa::Ocupada]);

        foreach ([1_000, 2_000] as $valorCentavos) {
            $comanda = Comanda::factory()
                ->for($mesa)
                ->for($caixa, 'abertaPor')
                ->create();
            $pedido = Pedido::factory()
                ->for($comanda)
                ->for($caixa, 'criadoPor')
                ->create();

            PedidoItem::factory()->for($pedido)->create([
                'preco_unitario_centavos' => $valorCentavos,
            ]);

            $this->actingAs($caixa)
                ->post(route('comandas.pagamentos.store', $comanda), [
                    'forma_pagamento' => FormaPagamento::Pix->value,
                ])
                ->assertRedirect(route('salao.index'));

            $mesa->update(['estado' => EstadoMesa::Ocupada]);
        }

        $this->assertSame(
            2,
            Comanda::query()
                ->whereBelongsTo($mesa)
                ->whereNull('ativa')
                ->count(),
        );
        $this->assertDatabaseCount('pagamentos', 2);
    }
}
