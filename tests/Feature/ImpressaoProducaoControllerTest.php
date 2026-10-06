<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\SetorProducao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ImpressaoProducaoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_mesma_chave_nao_gera_impressao_duplicada(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $setor = SetorProducao::factory()->create();
        $pedido = Pedido::factory()->enviado()->create();
        PedidoItem::factory()->enviado()->for($pedido)->create(['setor_producao_id' => $setor->id]);
        $dados = ['chave_idempotencia' => '5a795734-94be-4e83-a667-89d7c491f286'];

        $this->actingAs($usuario)->post(route('producao.impressoes.store', [$pedido, $setor]), $dados)->assertRedirect();
        $this->actingAs($usuario)->post(route('producao.impressoes.store', [$pedido, $setor]), $dados)->assertRedirect();

        $this->assertDatabaseCount('impressoes_producao', 1);
        $this->assertDatabaseHas('impressoes_producao', [
            'pedido_id' => $pedido->id,
            'setor_producao_id' => $setor->id,
            'sequencia' => 1,
            'tipo' => 'inicial',
        ]);
    }

    public function test_nova_chave_registra_reimpressao_com_proxima_sequencia(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $setor = SetorProducao::factory()->create();
        $pedido = Pedido::factory()->enviado()->create();
        PedidoItem::factory()->enviado()->for($pedido)->create(['setor_producao_id' => $setor->id]);

        foreach (['5a795734-94be-4e83-a667-89d7c491f286', '7ccb5c1d-0fd3-4290-8b33-d47b988998a8'] as $chave) {
            $this->actingAs($usuario)->post(route('producao.impressoes.store', [$pedido, $setor]), ['chave_idempotencia' => $chave])->assertRedirect();
        }

        $this->assertDatabaseCount('impressoes_producao', 2);
        $this->assertDatabaseHas('impressoes_producao', ['pedido_id' => $pedido->id, 'sequencia' => 2, 'tipo' => 'reimpressao']);
    }

    public function test_nao_imprime_pedido_de_outro_setor(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $setorDoPedido = SetorProducao::factory()->create();
        $outroSetor = SetorProducao::factory()->create();
        $pedido = Pedido::factory()->enviado()->create();
        PedidoItem::factory()->enviado()->for($pedido)->create(['setor_producao_id' => $setorDoPedido->id]);

        $this->actingAs($usuario)->post(route('producao.impressoes.store', [$pedido, $outroSetor]), [
            'chave_idempotencia' => '5a795734-94be-4e83-a667-89d7c491f286',
        ])->assertSessionHasErrors(['impressao' => 'O pedido não possui itens neste setor.']);

        $this->assertDatabaseCount('impressoes_producao', 0);
    }

    public function test_atendente_nao_solicita_reimpressao(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $setor = SetorProducao::factory()->create();
        $pedido = Pedido::factory()->enviado()->create();

        $this->actingAs($atendente)->post(route('producao.impressoes.store', [$pedido, $setor]), [
            'chave_idempotencia' => '5a795734-94be-4e83-a667-89d7c491f286',
        ])->assertForbidden();

        $this->assertDatabaseCount('impressoes_producao', 0);
    }
}
