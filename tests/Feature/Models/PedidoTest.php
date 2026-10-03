<?php

namespace Tests\Feature\Models;

use App\Models\Comanda;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\StatusPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_pedido_retorna_comanda_criador_e_itens(): void
    {
        $usuario = User::factory()->create();
        $comanda = Comanda::factory()->for($usuario, 'abertaPor')->create();
        $pedido = Pedido::factory()
            ->for($comanda)
            ->for($usuario, 'criadoPor')
            ->create();
        $item = PedidoItem::factory()->for($pedido)->create();

        $pedido->load(['comanda', 'criadoPor', 'itens']);
        $comanda->load('pedidos');

        $this->assertSame($comanda->id, $pedido->comanda->id);
        $this->assertSame($usuario->id, $pedido->criadoPor->id);
        $this->assertSame($item->id, $pedido->itens->sole()->id);
        $this->assertSame($pedido->id, $comanda->pedidos->sole()->id);
    }

    public function test_estado_enviado_registra_status_e_horario(): void
    {
        $this->freezeTime();

        $pedido = Pedido::factory()->enviado()->create();

        $this->assertSame(StatusPedido::Enviado, $pedido->status);
        $this->assertSame(now()->toDateTimeString(), $pedido->enviado_em?->toDateTimeString());
    }
}
