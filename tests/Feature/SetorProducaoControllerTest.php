<?php

namespace Tests\Feature;

use App\Models\ItemCardapio;
use App\Models\SetorProducao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SetorProducaoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_proprietario_visualiza_setores_de_producao(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        SetorProducao::factory()->create(['nome' => 'Cozinha']);

        $this->actingAs($proprietario)
            ->get(route('setores-producao.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('producao/setores/index')
                ->has('setores', 1)
                ->where('setores.0.nome', 'Cozinha'));
    }

    public function test_proprietario_realiza_crud_completo_de_setor(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);

        $this->actingAs($proprietario)
            ->post(route('setores-producao.store'), [
                'nome' => 'Bar',
                'descricao' => 'Bebidas e coquetéis',
                'ativo' => true,
                'ordem' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Setor de produção cadastrado com sucesso.');

        $setor = SetorProducao::query()->where('nome', 'Bar')->firstOrFail();

        $this->actingAs($proprietario)
            ->patch(route('setores-producao.update', $setor), [
                'nome' => 'Bar principal',
                'descricao' => null,
                'ativo' => false,
                'ordem' => 3,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Setor de produção atualizado com sucesso.');

        $this->assertDatabaseHas('setores_producao', [
            'id' => $setor->id,
            'nome' => 'Bar principal',
            'ativo' => false,
            'ordem' => 3,
        ]);

        $this->actingAs($proprietario)
            ->delete(route('setores-producao.destroy', $setor))
            ->assertRedirect()
            ->assertSessionHas('success', 'Setor de produção excluído com sucesso.');

        $this->assertModelMissing($setor);
    }

    public function test_setor_com_item_vinculado_nao_pode_ser_excluido(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $setor = SetorProducao::factory()->create();
        ItemCardapio::factory()->create(['setor_producao_id' => $setor->id]);

        $this->actingAs($proprietario)
            ->delete(route('setores-producao.destroy', $setor))
            ->assertSessionHasErrors([
                'setor' => 'Este setor possui itens ou histórico de produção e não pode ser excluído.',
            ]);

        $this->assertModelExists($setor);
    }

    public function test_gerente_nao_acessa_nem_altera_setores_de_producao(): void
    {
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $setor = SetorProducao::factory()->create();

        $this->actingAs($gerente)->get(route('setores-producao.index'))->assertForbidden();
        $this->actingAs($gerente)->post(route('setores-producao.store'), [
            'nome' => 'Confeitaria', 'ativo' => true, 'ordem' => 1,
        ])->assertForbidden();
        $this->actingAs($gerente)->patch(route('setores-producao.update', $setor), [
            'nome' => 'Alterado', 'ativo' => true, 'ordem' => 1,
        ])->assertForbidden();
        $this->actingAs($gerente)->delete(route('setores-producao.destroy', $setor))->assertForbidden();

        $this->assertDatabaseMissing('setores_producao', ['nome' => 'Confeitaria']);
        $this->assertDatabaseMissing('setores_producao', ['nome' => 'Alterado']);
        $this->assertModelExists($setor);
    }
}
