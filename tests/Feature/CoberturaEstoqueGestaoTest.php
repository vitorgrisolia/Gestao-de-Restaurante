<?php

namespace Tests\Feature;

use App\Actions\MovimentarEstoque;
use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\ItemCardapio;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CoberturaEstoqueGestaoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_inventario_incompleto_e_item_de_outra_contagem_nao_alteram_saldo(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $inventario = Inventario::create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $primeiro = $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 10]);
        $segundo = $inventario->itens()->create(['ingrediente_id' => Ingrediente::factory()->create(['estoque_atual' => 10])->id, 'quantidade_sistema' => 10]);
        $outro = Inventario::create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $estrangeiro = $outro->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 10]);

        $this->actingAs($usuario)->patch(route('inventarios.update', $inventario), ['itens' => [['id' => $primeiro->id, 'quantidade_contada' => 8]]])->assertSessionHasErrors('contagens');
        $this->patch(route('inventarios.update', $inventario), ['itens' => [
            ['id' => $primeiro->id, 'quantidade_contada' => 8],
            ['id' => $segundo->id, 'quantidade_contada' => 9],
            ['id' => $estrangeiro->id, 'quantidade_contada' => 7],
        ]])->assertSessionHasErrors('itens.2.id');

        $this->assertSame('10.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 0);
        $this->assertNull($primeiro->fresh()->quantidade_contada);
    }

    public function test_segunda_conclusao_nao_ajusta_inventario_duas_vezes(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $inventario = Inventario::create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $item = $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 10]);
        $dados = ['itens' => [['id' => $item->id, 'quantidade_contada' => 8]]];

        $this->actingAs($usuario)->patch(route('inventarios.update', $inventario), $dados)->assertSessionHasNoErrors();
        $this->patch(route('inventarios.update', $inventario), $dados)->assertSessionHasErrors('inventario');

        $this->assertSame('8.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 1);
    }

    public function test_perda_positiva_subtrai_e_ajuste_nao_pode_deixar_saldo_negativo(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);

        $this->actingAs($usuario)->post(route('movimentacoes-estoque.store', $ingrediente), ['tipo' => 'perda', 'quantidade' => 3, 'motivo' => 'Quebra'])->assertSessionHasNoErrors();
        $this->post(route('movimentacoes-estoque.store', $ingrediente), ['tipo' => 'ajuste', 'quantidade' => -8, 'motivo' => 'Ajuste'])->assertSessionHasErrors(['estoque' => "Estoque insuficiente de {$ingrediente->nome}."]);

        $this->assertSame('7.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 1);
        $this->assertDatabaseHas('movimentacoes_estoque', ['tipo' => 'perda', 'quantidade' => -3]);
    }

    public function test_entrada_com_saldo_zero_define_custo_e_sem_custo_preserva_media(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 0, 'custo_medio_centavos' => 0]);

        $this->actingAs($usuario)->post(route('movimentacoes-estoque.store', $ingrediente), ['tipo' => 'entrada', 'quantidade' => 5, 'custo_unitario' => 12.50, 'motivo' => 'Compra'])->assertSessionHasNoErrors();
        $this->assertSame(1250, $ingrediente->fresh()->custo_medio_centavos);
        $this->post(route('movimentacoes-estoque.store', $ingrediente), ['tipo' => 'entrada', 'quantidade' => 2, 'motivo' => 'Reposição'])->assertSessionHasNoErrors();
        $this->assertSame(1250, $ingrediente->fresh()->custo_medio_centavos);
        $this->assertSame('7.000', $ingrediente->fresh()->estoque_atual);
    }

    public function test_ficha_repetida_e_rejeitada_e_vazia_remove_composicao(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $produto = ItemCardapio::factory()->create();
        $ingrediente = Ingrediente::factory()->create();
        $produto->fichaTecnica()->create(['ingrediente_id' => $ingrediente->id, 'quantidade' => 1]);

        $linha = ['ingrediente_id' => $ingrediente->id, 'quantidade' => 2];
        $this->actingAs($usuario)->put(route('fichas-tecnicas.update', $produto), ['ingredientes' => [$linha, $linha]])->assertSessionHasErrors('ingredientes.0.ingrediente_id');
        $this->assertSame(1, $produto->fichaTecnica()->count());
        $this->put(route('fichas-tecnicas.update', $produto), ['ingredientes' => []])->assertSessionHasNoErrors();
        $this->assertSame(0, $produto->fichaTecnica()->count());
    }

    public function test_nome_maior_que_80_e_rejeitado_sem_escrever_no_banco(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);

        $this->actingAs($usuario)->post(route('unidades-medida.store'), ['nome' => str_repeat('A', 81), 'sigla' => 'kg', 'casas_decimais' => 3, 'ativa' => true])->assertSessionHasErrors('nome');

        $this->assertDatabaseCount('unidades_medida', 0);
    }

    public function test_csv_filtra_periodo_incluindo_as_duas_datas_limite(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create();
        foreach (['2026-01-01 23:59:59', '2026-01-02 00:00:00', '2026-01-03 23:59:59', '2026-01-04 00:00:00'] as $indice => $data) {
            $movimento = app(MovimentarEstoque::class)->handle($ingrediente, $usuario, 'entrada', 1, 'Registro-'.$indice);
            $movimento->update(['registrada_em' => $data]);
        }

        $csv = $this->actingAs($usuario)->get(route('estoque.relatorio', ['inicio' => '2026-01-02', 'fim' => '2026-01-03']))->streamedContent();

        $this->assertStringContainsString('Registro-1', $csv);
        $this->assertStringContainsString('Registro-2', $csv);
        $this->assertStringNotContainsString('Registro-0', $csv);
        $this->assertStringNotContainsString('Registro-3', $csv);
    }
}
