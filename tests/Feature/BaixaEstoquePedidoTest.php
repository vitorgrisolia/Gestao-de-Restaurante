<?php

namespace Tests\Feature;

use App\Models\FichaTecnicaItem;
use App\Models\Ingrediente;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BaixaEstoquePedidoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_envio_do_pedido_baixa_ficha_tecnica_uma_unica_vez(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $pedido = Pedido::factory()->create(['criado_por_id' => $usuario->id]);
        $item = PedidoItem::factory()->for($pedido)->create(['quantidade' => 2]);
        FichaTecnicaItem::create(['item_cardapio_id' => $item->item_cardapio_id, 'ingrediente_id' => $ingrediente->id, 'quantidade' => 1.5]);
        $this->actingAs($usuario)->post(route('pedidos.envios.store', $pedido))->assertRedirect();
        $this->assertSame('7.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 1);
        $this->actingAs($usuario)->post(route('pedidos.envios.store', $pedido));
        $this->assertSame('7.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 1);
    }

    public function test_pedido_nao_e_enviado_quando_estoque_e_insuficiente(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 1]);
        $pedido = Pedido::factory()->create(['criado_por_id' => $usuario->id]);
        $item = PedidoItem::factory()->for($pedido)->create(['quantidade' => 2]);
        FichaTecnicaItem::create(['item_cardapio_id' => $item->item_cardapio_id, 'ingrediente_id' => $ingrediente->id, 'quantidade' => 1]);
        $this->actingAs($usuario)->post(route('pedidos.envios.store', $pedido))->assertSessionHasErrors('estoque');
        $this->assertDatabaseCount('movimentacoes_estoque', 0);
    }
}
