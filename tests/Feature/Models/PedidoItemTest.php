<?php

namespace Tests\Feature\Models;

use App\Models\ItemCardapio;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\StatusItemPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoItemTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_item_retorna_pedido_e_produto_do_cardapio(): void
    {
        $pedido = Pedido::factory()->create();
        $produto = ItemCardapio::factory()->create();
        $item = PedidoItem::factory()
            ->for($pedido)
            ->for($produto, 'itemCardapio')
            ->create();

        $item->load(['pedido', 'itemCardapio']);
        $produto->load('pedidoItens');

        $this->assertSame($pedido->id, $item->pedido->id);
        $this->assertSame($produto->id, $item->itemCardapio->id);
        $this->assertSame($item->id, $produto->pedidoItens->sole()->id);
    }

    public function test_subtotal_multiplica_quantidade_pelo_preco_unitario(): void
    {
        $item = PedidoItem::factory()->create([
            'quantidade' => 3,
            'preco_unitario_centavos' => 1_250,
        ]);

        $this->assertSame(3_750, $item->subtotalCentavos());
    }

    public function test_estado_enviado_registra_status_e_horario(): void
    {
        $this->freezeTime();

        $item = PedidoItem::factory()->enviado()->create();

        $this->assertSame(StatusItemPedido::Enviado, $item->status);
        $this->assertSame(now()->toDateTimeString(), $item->enviado_em?->toDateTimeString());
    }
}
