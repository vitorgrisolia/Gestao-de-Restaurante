<?php

namespace Tests\Feature\Models;

use App\Models\CategoriaCardapio;
use App\Models\ItemCardapio;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoriaCardapioTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_categoria_retorna_somente_seus_itens(): void
    {
        $categoria = CategoriaCardapio::factory()->create();
        $outraCategoria = CategoriaCardapio::factory()->create();
        $primeiroItem = ItemCardapio::factory()->for($categoria, 'categoria')->create();
        $segundoItem = ItemCardapio::factory()->for($categoria, 'categoria')->create();
        ItemCardapio::factory()->for($outraCategoria, 'categoria')->create();

        $categoria->load('itens');

        $this->assertSame(
            [$primeiroItem->id, $segundoItem->id],
            $categoria->itens->modelKeys(),
        );
    }
}
