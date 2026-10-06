<?php

namespace Database\Seeders;

use App\Models\CategoriaCardapio;
use App\Models\SetorProducao;
use Illuminate\Database\Seeder;

class ItemCardapioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itensPorCategoria = [
            'Entradas' => [
                ['nome' => 'Batata frita', 'descricao' => 'Porção de batatas fritas crocantes', 'preco_centavos' => 2_500, 'ordem' => 1],
                ['nome' => 'Mandioca frita', 'descricao' => 'Porção de mandioca frita', 'preco_centavos' => 2_200, 'ordem' => 2],
            ],
            'Pratos' => [
                ['nome' => 'Prato executivo', 'descricao' => 'Arroz, feijão, salada e acompanhamento', 'preco_centavos' => 3_290, 'ordem' => 1],
                ['nome' => 'Filé à parmegiana', 'descricao' => 'Filé empanado, molho de tomate e queijo', 'preco_centavos' => 4_590, 'ordem' => 2],
            ],
            'Bebidas' => [
                ['nome' => 'Refrigerante lata', 'descricao' => 'Lata de 350 ml', 'preco_centavos' => 700, 'ordem' => 1],
                ['nome' => 'Suco natural', 'descricao' => 'Copo de 400 ml', 'preco_centavos' => 1_000, 'ordem' => 2],
            ],
            'Sobremesas' => [
                ['nome' => 'Pudim', 'descricao' => 'Fatia de pudim de leite', 'preco_centavos' => 1_200, 'ordem' => 1],
                ['nome' => 'Petit gâteau', 'descricao' => 'Bolo quente de chocolate com sorvete', 'preco_centavos' => 2_200, 'ordem' => 2],
            ],
        ];

        foreach ($itensPorCategoria as $nomeCategoria => $itens) {
            $categoria = CategoriaCardapio::query()
                ->where('nome', $nomeCategoria)
                ->firstOrFail();
            $setorProducao = SetorProducao::query()
                ->where('nome', $nomeCategoria === 'Bebidas' ? 'Bar' : 'Cozinha')
                ->firstOrFail();

            foreach ($itens as $item) {
                $categoria->itens()->updateOrCreate(
                    ['nome' => $item['nome']],
                    [...$item, 'setor_producao_id' => $setorProducao->id, 'imagem' => null, 'disponivel' => true],
                );
            }
        }
    }
}
