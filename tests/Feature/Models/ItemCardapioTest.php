<?php

namespace Tests\Feature\Models;

use App\Models\CategoriaCardapio;
use App\Models\ItemCardapio;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ItemCardapioTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_item_retorna_sua_categoria(): void
    {
        $categoria = CategoriaCardapio::factory()->create();
        $item = ItemCardapio::factory()->for($categoria, 'categoria')->create();

        $item->load('categoria');

        $this->assertSame($categoria->id, $item->categoria->id);
    }

    public function test_preco_permanece_inteiro_em_centavos(): void
    {
        $item = ItemCardapio::factory()->create([
            'preco_centavos' => 3_290,
        ]);

        $item->refresh();

        $this->assertSame(3_290, $item->preco_centavos);
    }

    public function test_estado_indisponivel_cria_item_fora_de_venda(): void
    {
        $item = ItemCardapio::factory()->indisponivel()->create();

        $item->refresh();

        $this->assertFalse($item->disponivel);
    }
}
