<?php

namespace Tests\Feature;

use App\Actions\CancelarPedido;
use App\Actions\ConcluirInventario;
use App\Actions\MovimentarEstoque;
use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\UnidadeMedida;
use App\Models\User;
use App\PapelUsuario;
use App\StatusItemPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CorrecoesEstoqueTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_ingrediente_em_inventario_nao_pode_ser_excluido(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $ingrediente = Ingrediente::factory()->create();
        $inventario = Inventario::create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 1]);

        $this->actingAs($usuario)->delete(route('ingredientes.destroy', $ingrediente))
            ->assertSessionHasErrors('ingrediente');
        $this->assertModelExists($ingrediente);
    }

    public function test_unidade_aceita_nome_de_80_caracteres_no_cadastro_e_edicao(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $dados = ['nome' => str_repeat('A', 80), 'sigla' => 'kg', 'casas_decimais' => 3, 'ativa' => true];

        $this->actingAs($usuario)->post(route('unidades-medida.store'), $dados)->assertSessionHasNoErrors();
        $unidade = UnidadeMedida::query()->firstOrFail();
        $dados['nome'] = str_repeat('B', 80);
        $this->put(route('unidades-medida.update', $unidade), $dados)->assertSessionHasNoErrors();
        $this->assertSame($dados['nome'], $unidade->fresh()->nome);
    }

    public function test_mesma_chave_de_movimento_nao_desconta_duas_vezes(): void
    {
        $usuario = User::factory()->create();
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $acao = app(MovimentarEstoque::class);

        $primeira = $acao->handle($ingrediente, $usuario, 'baixa', -2, chaveIdempotencia: 'pedido-item:12:ingrediente:3');
        $segunda = $acao->handle($ingrediente, $usuario, 'baixa', -2, chaveIdempotencia: 'pedido-item:12:ingrediente:3');

        $this->assertSame($primeira->id, $segunda->id);
        $this->assertSame('8.000', $ingrediente->fresh()->estoque_atual);
    }

    public function test_cancelamento_devolve_apenas_itens_nao_preparados(): void
    {
        $usuario = User::factory()->create();
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $pedido = Pedido::factory()->enviado()->create();
        $naoPreparado = PedidoItem::factory()->for($pedido)->enviado()->create();
        $emPreparo = PedidoItem::factory()->for($pedido)->create(['status' => StatusItemPedido::EmPreparo, 'iniciado_em' => now()]);
        $movimentar = app(MovimentarEstoque::class);
        $movimentar->handle($ingrediente, $usuario, 'baixa', -2, pedidoItemId: $naoPreparado->id);
        $movimentar->handle($ingrediente, $usuario, 'baixa', -3, pedidoItemId: $emPreparo->id);

        app(CancelarPedido::class)->handle($pedido, $usuario, 'Cliente desistiu');

        $this->assertSame('7.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 3);
        $this->assertDatabaseHas('movimentacoes_estoque', ['tipo' => 'devolucao', 'pedido_item_id' => $naoPreparado->id]);
    }

    public function test_inventario_nao_sobrescreve_vendas_ocorridas_durante_a_contagem(): void
    {
        $usuario = User::factory()->create();
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 8]);
        $inventario = Inventario::create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $item = $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 10]);

        try {
            app(ConcluirInventario::class)->handle($inventario, $usuario, [$item->id => 9]);
            $this->fail('O inventário deveria recusar uma contagem desatualizada.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('inventario', $exception->errors());
        }

        $this->assertSame('8.000', $ingrediente->fresh()->estoque_atual);
        $this->assertSame('aberto', $inventario->fresh()->status);
    }

    public function test_recontagem_atualiza_referencia_sem_alterar_estoque(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 8]);
        $inventario = Inventario::create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $item = $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 10]);

        $this->actingAs($usuario)->post(route('inventarios.recontagem', $inventario))->assertSessionHasNoErrors();

        $this->assertSame('8.000', $item->fresh()->quantidade_sistema);
        $this->assertSame('8.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 0);
    }
}
