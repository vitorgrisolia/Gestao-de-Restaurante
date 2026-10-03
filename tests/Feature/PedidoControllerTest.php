<?php

namespace Tests\Feature;

use App\Models\Comanda;
use App\Models\ItemCardapio;
use App\Models\User;
use App\PapelUsuario;
use App\StatusComanda;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_atendente_registra_pedido_com_preco_do_cardapio(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->for($atendente, 'abertaPor')->create();
        $produto = ItemCardapio::factory()->create([
            'nome' => 'Prato executivo',
            'preco_centavos' => 3_290,
        ]);

        $this->actingAs($atendente)
            ->from(route('salao.index'))
            ->post(route('comandas.pedidos.store', $comanda), [
                'observacao' => 'Sem pressa',
                'itens' => [[
                    'item_cardapio_id' => $produto->id,
                    'quantidade' => 2,
                    'observacao' => 'Sem cebola',
                    'preco_unitario_centavos' => 1,
                ]],
            ])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHas('success', 'Pedido registrado com sucesso.');

        $this->assertDatabaseHas('pedidos', [
            'comanda_id' => $comanda->id,
            'criado_por_id' => $atendente->id,
            'status' => 'rascunho',
            'observacao' => 'Sem pressa',
        ]);
        $this->assertDatabaseHas('pedido_itens', [
            'item_cardapio_id' => $produto->id,
            'nome_item' => 'Prato executivo',
            'quantidade' => 2,
            'preco_unitario_centavos' => 3_290,
            'observacao' => 'Sem cebola',
            'status' => 'rascunho',
        ]);
    }

    public function test_rejeita_pedido_sem_itens(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->for($atendente, 'abertaPor')->create();

        $this->actingAs($atendente)
            ->post(route('comandas.pedidos.store', $comanda), ['itens' => []])
            ->assertSessionHasErrors([
                'itens' => 'Adicione pelo menos um item ao pedido.',
            ]);

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_rejeita_produto_indisponivel_sem_criar_pedido(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->for($atendente, 'abertaPor')->create();
        $produto = ItemCardapio::factory()->indisponivel()->create();

        $this->actingAs($atendente)
            ->post(route('comandas.pedidos.store', $comanda), [
                'itens' => [[
                    'item_cardapio_id' => $produto->id,
                    'quantidade' => 1,
                ]],
            ])
            ->assertSessionHasErrors([
                'itens' => 'Um ou mais itens do cardápio não estão disponíveis.',
            ]);

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_itens', 0);
    }

    public function test_rejeita_pedido_em_comanda_fechada(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->for($atendente, 'abertaPor')->create([
            'status' => StatusComanda::Fechada,
            'ativa' => false,
        ]);
        $produto = ItemCardapio::factory()->create();

        $this->actingAs($atendente)
            ->post(route('comandas.pedidos.store', $comanda), [
                'itens' => [[
                    'item_cardapio_id' => $produto->id,
                    'quantidade' => 1,
                ]],
            ])
            ->assertSessionHasErrors([
                'comanda' => 'Esta comanda não está aberta para receber pedidos.',
            ]);

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_cozinha_nao_pode_registrar_pedido(): void
    {
        $cozinha = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create();

        $this->actingAs($cozinha)
            ->post(route('comandas.pedidos.store', $comanda), [
                'itens' => [[
                    'item_cardapio_id' => $produto->id,
                    'quantidade' => 1,
                ]],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('pedidos', 0);
    }
}
