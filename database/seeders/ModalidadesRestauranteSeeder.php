<?php

namespace Database\Seeders;

use App\Models\CategoriaCardapio;
use App\Models\SetorProducao;
use App\TipoVenda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModalidadesRestauranteSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $categoria = CategoriaCardapio::query()->firstOrCreate(['nome' => 'Bufê e marmitas'], ['descricao' => 'Modalidades da tabela de preços do restaurante', 'ativa' => true, 'ordem' => 0]);
            $cozinha = SetorProducao::query()->firstOrCreate(['nome' => 'Cozinha'], ['descricao' => 'Preparo de refeições', 'ativo' => true, 'ordem' => 1]);
            $modalidades = [
                ['Monte sua marmita', 'Peso líquido da marmita, sem embalagem.', 6000, TipoVenda::Peso],
                ['Moda da casa', 'Marmita de tamanho padrão.', 2800, TipoVenda::Unidade],
                ['Self-service à vontade', 'Valor por pessoa.', 3000, TipoVenda::Pessoa],
                ['Self-service por peso', 'Peso líquido do alimento, sem prato.', 6000, TipoVenda::Peso],
            ];
            foreach ($modalidades as $ordem => [$nome, $descricao, $preco, $tipo]) {
                $categoria->itens()->firstOrCreate(['nome' => $nome], [
                    'descricao' => $descricao, 'preco_centavos' => $preco, 'tipo_venda' => $tipo,
                    'permite_excesso_carne' => true, 'setor_producao_id' => $cozinha->id,
                    'disponivel' => true, 'ordem' => $ordem,
                ]);
            }
        });
    }
}
