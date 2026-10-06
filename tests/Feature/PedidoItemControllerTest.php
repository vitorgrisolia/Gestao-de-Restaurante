<?php

namespace Tests\Feature;

use App\Models\Comanda;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\PapelUsuario;
use App\StatusComanda;
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoItemControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_atendente_altera_quantidade_e_observacao_de_item_em_rascunho(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->for($atendente, 'abertaPor')->create();
        $pedido = Pedido::factory()->for($comanda)->for($atendente, 'criadoPor')->create();
        $item = PedidoItem::factory()->for($pedido)->create([
            'nome_item' => 'Prato executivo',
            'quantidade' => 1,
            'preco_unitario_centavos' => 3_290,
            'observacao' => null,
        ]);

        $this->actingAs($atendente)
            ->from(route('comandas.show', $comanda))
            ->patch(route('pedido-itens.update', $item), [
                'quantidade' => 3,
                'observacao' => 'Sem cebola',
                'preco_unitario_centavos' => 1,
                'nome_item' => 'Preço adulterado',
            ])
            ->assertRedirect(route('comandas.show', $comanda))
            ->assertSessionHas('success', 'Item atualizado com sucesso.');

        $this->assertDatabaseHas('pedido_itens', [
            'id' => $item->id,
            'nome_item' => 'Prato executivo',
            'quantidade' => 3,
            'preco_unitario_centavos' => 3_290,
            'observacao' => 'Sem cebola',
        ]);
    }

    public function test_rejeita_quantidade_invalida_sem_alterar_item(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $item = PedidoItem::factory()->create(['quantidade' => 2]);

        $this->actingAs($atendente)
            ->patch(route('pedido-itens.update', $item), [
                'quantidade' => 0,
                'observacao' => null,
            ])
            ->assertSessionHasErrors([
                'quantidade' => 'A quantidade deve ser pelo menos 1.',
            ]);

        $this->assertDatabaseHas('pedido_itens', [
            'id' => $item->id,
            'quantidade' => 2,
        ]);
    }

    public function test_rejeita_alteracao_de_item_ja_enviado(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $pedido = Pedido::factory()->enviado()->create();
        $item = PedidoItem::factory()->for($pedido)->enviado()->create(['quantidade' => 1]);

        $this->actingAs($atendente)
            ->patch(route('pedido-itens.update', $item), [
                'quantidade' => 2,
                'observacao' => null,
            ])
            ->assertSessionHasErrors([
                'item' => 'Somente itens de pedidos em rascunho podem ser alterados.',
            ]);

        $this->assertDatabaseHas('pedido_itens', [
            'id' => $item->id,
            'quantidade' => 1,
            'status' => StatusItemPedido::Enviado->value,
        ]);
    }

    public function test_rejeita_alteracao_quando_comanda_esta_fechada(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create([
            'status' => StatusComanda::Fechada,
            'ativa' => null,
        ]);
        $pedido = Pedido::factory()->for($comanda)->create();
        $item = PedidoItem::factory()->for($pedido)->create(['quantidade' => 1]);

        $this->actingAs($atendente)
            ->patch(route('pedido-itens.update', $item), [
                'quantidade' => 2,
                'observacao' => null,
            ])
            ->assertSessionHasErrors(['item']);

        $this->assertSame(1, $item->fresh()?->quantidade);
    }

    public function test_atendente_remove_item_e_mantem_pedido_com_outros_itens(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $pedido = Pedido::factory()->for($atendente, 'criadoPor')->create();
        $itemRemovido = PedidoItem::factory()->for($pedido)->create();
        $itemMantido = PedidoItem::factory()->for($pedido)->create();

        $this->actingAs($atendente)
            ->delete(route('pedido-itens.destroy', $itemRemovido))
            ->assertRedirect()
            ->assertSessionHas('success', 'Item removido com sucesso.');

        $this->assertModelMissing($itemRemovido);
        $this->assertModelExists($itemMantido);
        $this->assertModelExists($pedido);
    }

    public function test_remove_pedido_quando_ultimo_item_do_rascunho_e_removido(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $pedido = Pedido::factory()->for($atendente, 'criadoPor')->create();
        $item = PedidoItem::factory()->for($pedido)->create();

        $this->actingAs($atendente)
            ->delete(route('pedido-itens.destroy', $item))
            ->assertRedirect();

        $this->assertModelMissing($item);
        $this->assertModelMissing($pedido);
    }

    public function test_rejeita_remocao_de_item_ja_enviado(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $pedido = Pedido::factory()->create(['status' => StatusPedido::Enviado]);
        $item = PedidoItem::factory()->for($pedido)->create([
            'status' => StatusItemPedido::Enviado,
        ]);

        $this->actingAs($atendente)
            ->delete(route('pedido-itens.destroy', $item))
            ->assertSessionHasErrors([
                'item' => 'Somente itens de pedidos em rascunho podem ser removidos.',
            ]);

        $this->assertModelExists($item);
    }

    public function test_cozinha_nao_altera_nem_remove_item(): void
    {
        $cozinha = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $item = PedidoItem::factory()->create(['quantidade' => 1]);

        $this->actingAs($cozinha)
            ->patch(route('pedido-itens.update', $item), [
                'quantidade' => 2,
                'observacao' => null,
            ])
            ->assertForbidden();

        $this->actingAs($cozinha)
            ->delete(route('pedido-itens.destroy', $item))
            ->assertForbidden();

        $this->assertModelExists($item);
        $this->assertSame(1, $item->fresh()?->quantidade);
    }
}
