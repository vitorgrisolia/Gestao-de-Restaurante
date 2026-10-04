<?php

namespace Tests\Feature;

use App\EstadoMesa;
use App\FormaPagamento;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\PapelUsuario;
use App\StatusComanda;
use App\StatusItemPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PagamentoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_caixa_recebe_pagamento_fecha_comanda_e_libera_mesa(): void
    {
        $caixa = User::factory()->create(['papel' => PapelUsuario::Caixa]);
        $mesa = Mesa::factory()->create(['estado' => EstadoMesa::Ocupada]);
        $comanda = Comanda::factory()
            ->for($mesa)
            ->for($caixa, 'abertaPor')
            ->create();
        $pedido = Pedido::factory()->for($comanda)->for($caixa, 'criadoPor')->create();

        PedidoItem::factory()->for($pedido)->create([
            'quantidade' => 2,
            'preco_unitario_centavos' => 3_290,
        ]);
        PedidoItem::factory()->for($pedido)->create([
            'quantidade' => 1,
            'preco_unitario_centavos' => 1_200,
            'status' => StatusItemPedido::Cancelado,
        ]);

        $this->actingAs($caixa)
            ->post(route('comandas.pagamentos.store', $comanda), [
                'forma_pagamento' => FormaPagamento::Pix->value,
            ])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHas(
                'success',
                'Pagamento registrado, comanda fechada e mesa liberada.',
            );

        $this->assertDatabaseHas('pagamentos', [
            'comanda_id' => $comanda->id,
            'recebido_por_id' => $caixa->id,
            'forma' => FormaPagamento::Pix->value,
            'valor_centavos' => 6_580,
        ]);
        $this->assertDatabaseHas('comandas', [
            'id' => $comanda->id,
            'status' => StatusComanda::Fechada->value,
            'ativa' => false,
        ]);
        $this->assertDatabaseHas('mesas', [
            'id' => $mesa->id,
            'estado' => EstadoMesa::Livre->value,
        ]);
        $this->assertNotNull($comanda->fresh()?->fechada_em);
    }

    public function test_nao_fecha_comanda_sem_itens_cobraveis(): void
    {
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $mesa = Mesa::factory()->create(['estado' => EstadoMesa::Ocupada]);
        $comanda = Comanda::factory()->for($mesa)->create();

        $this->actingAs($gerente)
            ->post(route('comandas.pagamentos.store', $comanda), [
                'forma_pagamento' => FormaPagamento::Dinheiro->value,
            ])
            ->assertSessionHasErrors([
                'comanda' => 'Adicione pelo menos um item antes de fechar a comanda.',
            ]);

        $this->assertDatabaseCount('pagamentos', 0);
        $this->assertDatabaseHas('comandas', [
            'id' => $comanda->id,
            'status' => StatusComanda::Aberta->value,
            'ativa' => true,
        ]);
        $this->assertDatabaseHas('mesas', [
            'id' => $mesa->id,
            'estado' => EstadoMesa::Ocupada->value,
        ]);
    }

    public function test_nao_registra_segundo_pagamento_em_comanda_fechada(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $comanda = Comanda::factory()->create([
            'status' => StatusComanda::Fechada,
            'ativa' => false,
            'fechada_em' => now(),
        ]);

        $this->actingAs($proprietario)
            ->post(route('comandas.pagamentos.store', $comanda), [
                'forma_pagamento' => FormaPagamento::CartaoCredito->value,
            ])
            ->assertSessionHasErrors([
                'comanda' => 'Esta comanda já foi fechada ou cancelada.',
            ]);

        $this->assertDatabaseCount('pagamentos', 0);
    }

    public function test_atendente_nao_pode_receber_pagamento(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();

        $this->actingAs($atendente)
            ->post(route('comandas.pagamentos.store', $comanda), [
                'forma_pagamento' => FormaPagamento::Pix->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('pagamentos', 0);
    }

    public function test_exige_forma_de_pagamento_valida(): void
    {
        $caixa = User::factory()->create(['papel' => PapelUsuario::Caixa]);
        $comanda = Comanda::factory()->create();

        $this->actingAs($caixa)
            ->post(route('comandas.pagamentos.store', $comanda), [
                'forma_pagamento' => 'cheque',
            ])
            ->assertSessionHasErrors([
                'forma_pagamento' => 'Selecione uma forma de pagamento válida.',
            ]);

        $this->assertDatabaseCount('pagamentos', 0);
    }
}
