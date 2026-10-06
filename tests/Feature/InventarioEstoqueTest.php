<?php

namespace Tests\Feature;

use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InventarioEstoqueTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_inventario_cria_fotografia_e_ajusta_diferenca_contada(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 8]);
        $this->actingAs($usuario)->post(route('inventarios.store'), ['observacao' => 'Fechamento mensal'])->assertRedirect();
        $inventario = Inventario::query()->firstOrFail();
        $item = $inventario->itens()->firstOrFail();
        $this->actingAs($usuario)->patch(route('inventarios.update', $inventario), ['itens' => [['id' => $item->id, 'quantidade_contada' => 6.5]]])->assertRedirect();
        $this->assertSame('6.500', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseHas('movimentacoes_estoque', ['ingrediente_id' => $ingrediente->id, 'tipo' => 'inventario']);
        $this->assertSame('concluido', $inventario->fresh()->status);
    }

    public function test_relatorio_csv_exige_permissao_e_contem_cabecalho(): void
    {
        $estoquista = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $resposta = $this->actingAs($estoquista)->get(route('estoque.relatorio'));
        $resposta->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Ingrediente', $resposta->streamedContent());
    }
}
