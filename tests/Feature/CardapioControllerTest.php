<?php

namespace Tests\Feature;

use App\Models\CategoriaCardapio;
use App\Models\ItemCardapio;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CardapioControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_proprietario_visualiza_cadastro_do_cardapio(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $categoria = CategoriaCardapio::factory()
            ->has(ItemCardapio::factory()->count(2), 'itens')
            ->create();

        $this->actingAs($proprietario)
            ->get(route('cardapio.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $pagina) => $pagina
                ->component('cardapio/index')
                ->where('resumo.categorias', 1)
                ->where('resumo.itens', 2)
                ->has('categorias', 1)
                ->where('categorias.0.id', $categoria->id));
    }

    public function test_atendente_nao_visualiza_cadastro_do_cardapio(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);

        $this->actingAs($atendente)
            ->get(route('cardapio.index'))
            ->assertForbidden();
    }

    public function test_proprietario_cadastra_categoria(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);

        $this->actingAs($proprietario)
            ->from(route('cardapio.index'))
            ->post(route('categorias-cardapio.store'), [
                'nome' => 'Massas',
                'descricao' => 'Pratos com massas artesanais',
                'ativa' => true,
                'ordem' => 5,
            ])
            ->assertRedirect(route('cardapio.index'))
            ->assertSessionHas('success', 'Categoria cadastrada com sucesso.');

        $this->assertDatabaseHas('categorias_cardapio', [
            'nome' => 'Massas',
            'descricao' => 'Pratos com massas artesanais',
            'ativa' => true,
            'ordem' => 5,
        ]);
    }

    public function test_proprietario_cadastra_item_com_preco_em_centavos(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $categoria = CategoriaCardapio::factory()->create();

        $this->actingAs($proprietario)
            ->from(route('cardapio.index'))
            ->post(route('itens-cardapio.store'), [
                'categoria_cardapio_id' => $categoria->id,
                'nome' => 'Lasanha bolonhesa',
                'descricao' => 'Massa, molho e queijo',
                'preco' => '42.90',
                'imagem' => 'https://example.com/lasanha.jpg',
                'disponivel' => true,
                'ordem' => 3,
            ])
            ->assertRedirect(route('cardapio.index'))
            ->assertSessionHas('success', 'Item cadastrado com sucesso.');

        $this->assertDatabaseHas('itens_cardapio', [
            'categoria_cardapio_id' => $categoria->id,
            'nome' => 'Lasanha bolonhesa',
            'preco_centavos' => 4_290,
            'disponivel' => true,
            'ordem' => 3,
        ]);
    }

    public function test_rejeita_item_com_nome_repetido_na_mesma_categoria(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $categoria = CategoriaCardapio::factory()->create();
        ItemCardapio::factory()->for($categoria, 'categoria')->create(['nome' => 'Lasanha']);

        $this->actingAs($proprietario)
            ->post(route('itens-cardapio.store'), [
                'categoria_cardapio_id' => $categoria->id,
                'nome' => 'Lasanha',
                'preco' => '30.00',
                'disponivel' => true,
                'ordem' => 0,
            ])
            ->assertSessionHasErrors([
                'nome' => 'Já existe um item com este nome na categoria.',
            ]);

        $this->assertDatabaseCount('itens_cardapio', 1);
    }

    public function test_gerente_nao_cadastra_categoria_ou_item(): void
    {
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $categoria = CategoriaCardapio::factory()->create();

        $this->actingAs($gerente)
            ->post(route('categorias-cardapio.store'), [
                'nome' => 'Massas',
                'ativa' => true,
                'ordem' => 0,
            ])
            ->assertForbidden();

        $this->actingAs($gerente)
            ->post(route('itens-cardapio.store'), [
                'categoria_cardapio_id' => $categoria->id,
                'nome' => 'Lasanha',
                'preco' => '30.00',
                'disponivel' => true,
                'ordem' => 0,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('categorias_cardapio', ['nome' => 'Massas']);
        $this->assertDatabaseMissing('itens_cardapio', ['nome' => 'Lasanha']);
    }
}
