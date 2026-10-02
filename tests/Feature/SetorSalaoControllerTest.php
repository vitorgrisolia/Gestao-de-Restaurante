<?php

namespace Tests\Feature;

use App\Models\SetorSalao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SetorSalaoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_proprietario_cadastra_setor_valido(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);

        $this->actingAs($proprietario)
            ->from(route('salao.index'))
            ->post(route('setores-salao.store'), [
                'nome' => 'Varanda',
                'descricao' => 'Área externa coberta',
            ])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHas('success', 'Setor cadastrado com sucesso.');

        $this->assertDatabaseHas('setores_salao', [
            'nome' => 'Varanda',
            'descricao' => 'Área externa coberta',
            'ativo' => true,
        ]);
    }

    public function test_atendente_nao_pode_cadastrar_setor(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);

        $this->actingAs($atendente)
            ->post(route('setores-salao.store'), ['nome' => 'Varanda'])
            ->assertForbidden();

        $this->assertDatabaseMissing('setores_salao', ['nome' => 'Varanda']);
    }

    public function test_nome_do_setor_e_obrigatorio_e_unico(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        SetorSalao::factory()->create(['nome' => 'Varanda']);

        $this->actingAs($proprietario)
            ->from(route('salao.index'))
            ->post(route('setores-salao.store'), ['nome' => 'Varanda'])
            ->assertRedirect(route('salao.index'))
            ->assertSessionHasErrors([
                'nome' => 'Já existe um setor com este nome.',
            ]);
    }
}
