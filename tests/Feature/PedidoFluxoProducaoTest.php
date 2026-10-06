<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\PapelUsuario;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoFluxoProducaoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_atendente_envia_pedido_e_itens_para_producao(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $pedido = Pedido::factory()->create();
        $itens = PedidoItem::factory()->count(2)->for($pedido)->create();

        $this->actingAs($atendente)
            ->post(route('pedidos.envios.store', $pedido))
            ->assertRedirect()
            ->assertSessionHas('success', 'Pedido enviado à produção.');

        $pedido->refresh();
        $this->assertSame(StatusPedido::Enviado, $pedido->status);
        $this->assertNotNull($pedido->enviado_em);

        foreach ($itens as $item) {
            $item->refresh();
            $this->assertSame(StatusItemPedido::Enviado, $item->status);
            $this->assertNotNull($item->enviado_em);
        }
    }

    public function test_nao_envia_o_mesmo_pedido_duas_vezes(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $pedido = Pedido::factory()->enviado()->create();

        $this->actingAs($atendente)
            ->post(route('pedidos.envios.store', $pedido))
            ->assertSessionHasErrors([
                'pedido' => 'Somente pedidos em rascunho podem ser enviados.',
            ]);
    }

    public function test_atendente_cancela_pedido_enviado_com_auditoria(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $pedido = Pedido::factory()->enviado()->create();
        $itens = PedidoItem::factory()->count(2)->for($pedido)->enviado()->create();

        $this->actingAs($atendente)
            ->post(route('pedidos.cancelamentos.store', $pedido), [
                'motivo' => 'Cliente desistiu do pedido',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Pedido cancelado com auditoria registrada.');

        $pedido->refresh();
        $this->assertSame(StatusPedido::Cancelado, $pedido->status);
        $this->assertNotNull($pedido->cancelado_em);

        foreach ($itens as $item) {
            $this->assertDatabaseHas('pedido_itens', [
                'id' => $item->id,
                'status' => StatusItemPedido::Cancelado->value,
                'cancelado_por_id' => $atendente->id,
                'motivo_cancelamento' => 'Cliente desistiu do pedido',
            ]);
            $this->assertNotNull($item->fresh()?->cancelado_em);
        }
    }

    public function test_cancelamento_exige_motivo(): void
    {
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $pedido = Pedido::factory()->enviado()->create();

        $this->actingAs($gerente)
            ->post(route('pedidos.cancelamentos.store', $pedido), ['motivo' => ''])
            ->assertSessionHasErrors([
                'motivo' => 'Informe o motivo do cancelamento.',
            ]);

        $this->assertSame(StatusPedido::Enviado, $pedido->fresh()?->status);
    }

    public function test_nao_cancela_pedido_em_rascunho_ou_entregue(): void
    {
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);

        foreach ([StatusPedido::Rascunho, StatusPedido::Entregue] as $status) {
            $pedido = Pedido::factory()->create(['status' => $status]);

            $this->actingAs($gerente)
                ->post(route('pedidos.cancelamentos.store', $pedido), [
                    'motivo' => 'Solicitação do cliente',
                ])
                ->assertSessionHasErrors([
                    'pedido' => 'Este pedido não pode mais ser cancelado.',
                ]);
        }
    }

    public function test_cozinha_nao_envia_nem_cancela_pedidos(): void
    {
        $cozinha = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $rascunho = Pedido::factory()->create();
        $enviado = Pedido::factory()->enviado()->create();

        $this->actingAs($cozinha)
            ->post(route('pedidos.envios.store', $rascunho))
            ->assertForbidden();
        $this->actingAs($cozinha)
            ->post(route('pedidos.cancelamentos.store', $enviado), [
                'motivo' => 'Tentativa sem autorização',
            ])
            ->assertForbidden();
    }
}
