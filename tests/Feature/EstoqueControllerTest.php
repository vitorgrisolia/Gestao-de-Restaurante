<?php

namespace Tests\Feature;

use App\Models\Ingrediente;
use App\Models\UnidadeMedida;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EstoqueControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_equipe_de_estoque_acessa_painel_e_atendente_nao(): void
    {
        $estoquista = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $this->actingAs($estoquista)->get(route('estoque.index'))->assertOk();
        $this->actingAs($atendente)->get(route('estoque.index'))->assertForbidden();
    }

    public function test_somente_proprietario_administra_ingredientes(): void
    {
        $unidade = UnidadeMedida::factory()->create();
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $dados = ['nome' => 'Farinha especial', 'unidade_medida_id' => $unidade->id, 'estoque_minimo' => 2, 'ativo' => true];
        $this->actingAs($gerente)->post(route('ingredientes.store'), $dados)->assertForbidden();
        $this->actingAs($proprietario)->post(route('ingredientes.store'), $dados)->assertRedirect();
        $this->assertDatabaseHas('ingredientes', ['nome' => 'Farinha especial']);
    }

    public function test_entrada_atualiza_saldo_custo_medio_e_auditoria(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10, 'custo_medio_centavos' => 100]);
        $this->actingAs($usuario)->post(route('movimentacoes-estoque.store', $ingrediente), ['tipo' => 'entrada', 'quantidade' => 10, 'custo_unitario' => 3, 'motivo' => 'Nota fiscal 123'])->assertRedirect();
        $ingrediente->refresh();
        $this->assertSame('20.000', $ingrediente->estoque_atual);
        $this->assertSame(200, $ingrediente->custo_medio_centavos);
        $this->assertDatabaseHas('movimentacoes_estoque', ['usuario_id' => $usuario->id, 'tipo' => 'entrada', 'motivo' => 'Nota fiscal 123']);
    }
}
