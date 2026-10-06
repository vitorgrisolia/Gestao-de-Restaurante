<?php

namespace Tests\Feature;

use App\Models\CategoriaCardapio;
use App\Models\ItemCardapio;
use App\Models\PedidoItem;
use App\Models\SetorProducao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CardapioCrudTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_proprietario_atualiza_categoria(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $categoria = CategoriaCardapio::factory()->create(['nome' => 'Bebidas']);

        $this->actingAs($proprietario)
            ->patch(route('categorias-cardapio.update', $categoria), [
                'nome' => 'Bebidas geladas',
                'descricao' => 'Sucos e refrigerantes',
                'ativa' => false,
                'ordem' => 8,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Categoria atualizada com sucesso.');

        $this->assertDatabaseHas('categorias_cardapio', [
            'id' => $categoria->id,
            'nome' => 'Bebidas geladas',
            'ativa' => false,
            'ordem' => 8,
        ]);
    }

    public function test_proprietario_atualiza_item_e_preco(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $categoria = CategoriaCardapio::factory()->create();
        $setor = SetorProducao::factory()->create();
        $item = ItemCardapio::factory()->for($categoria, 'categoria')->create();

        $this->actingAs($proprietario)
            ->patch(route('itens-cardapio.update', $item), [
                'categoria_cardapio_id' => $categoria->id,
                'setor_producao_id' => $setor->id,
                'nome' => 'Suco de laranja',
                'descricao' => 'Copo de 500 ml',
                'preco' => '12.50',
                'imagem' => null,
                'disponivel' => true,
                'ordem' => 4,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Item atualizado com sucesso.');

        $this->assertDatabaseHas('itens_cardapio', [
            'id' => $item->id,
            'nome' => 'Suco de laranja',
            'preco_centavos' => 1_250,
            'ordem' => 4,
        ]);
    }

    public function test_proprietario_exclui_item_sem_historico_e_categoria_vazia(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $categoria = CategoriaCardapio::factory()->create();
        $item = ItemCardapio::factory()->for($categoria, 'categoria')->create();

        $this->actingAs($proprietario)
            ->delete(route('itens-cardapio.destroy', $item))
            ->assertRedirect();
        $this->actingAs($proprietario)
            ->delete(route('categorias-cardapio.destroy', $categoria))
            ->assertRedirect();

        $this->assertModelMissing($item);
        $this->assertModelMissing($categoria);
    }

    public function test_nao_exclui_item_com_historico_de_pedido(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $item = ItemCardapio::factory()->create();
        PedidoItem::factory()->for($item, 'itemCardapio')->create();

        $this->actingAs($proprietario)
            ->delete(route('itens-cardapio.destroy', $item))
            ->assertSessionHasErrors([
                'item' => 'Este item possui pedidos registrados e não pode ser excluído. Marque-o como indisponível.',
            ]);

        $this->assertModelExists($item);
    }

    public function test_gerente_nao_atualiza_nem_exclui_cardapio(): void
    {
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $categoria = CategoriaCardapio::factory()->create();
        $item = ItemCardapio::factory()->for($categoria, 'categoria')->create();

        $this->actingAs($gerente)
            ->patch(route('categorias-cardapio.update', $categoria), [
                'nome' => 'Alterada', 'ativa' => true, 'ordem' => 0,
            ])
            ->assertForbidden();
        $this->actingAs($gerente)
            ->delete(route('itens-cardapio.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseMissing('categorias_cardapio', ['nome' => 'Alterada']);
        $this->assertModelExists($item);
    }
}
