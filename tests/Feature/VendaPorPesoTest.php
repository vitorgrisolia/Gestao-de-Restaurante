<?php

namespace Tests\Feature;

use App\Actions\CalcularContaComanda;
use App\Actions\EnviarPedido;
use App\Models\CategoriaCardapio;
use App\Models\Comanda;
use App\Models\Ingrediente;
use App\Models\ItemCardapio;
use App\Models\PedidoItem;
use App\Models\SetorProducao;
use App\Models\User;
use App\PapelUsuario;
use App\TipoVenda;
use Database\Seeders\ModalidadesRestauranteSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VendaPorPesoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_peso_e_adicional_sao_calculados_no_servidor_e_preservados_no_historico(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create(['tipo_venda' => TipoVenda::Peso, 'preco_centavos' => 6000, 'permite_excesso_carne' => true]);

        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [[
            'item_cardapio_id' => $produto->id, 'quantidade' => 2, 'peso_gramas' => 450,
            'cobrar_excesso_carne' => true, 'adicional_carne' => '5.00', 'preco_unitario_centavos' => 1,
        ]]])->assertSessionHasNoErrors();
        $produto->update(['preco_centavos' => 9000, 'tipo_venda' => TipoVenda::Unidade, 'permite_excesso_carne' => false]);

        $item = PedidoItem::query()->sole();
        $this->assertSame(3200, $item->preco_unitario_centavos);
        $this->assertSame(6000, $item->preco_referencia_centavos);
        $this->assertSame(450, $item->peso_gramas);
        $this->assertSame(500, $item->adicional_carne_centavos);
        $this->assertSame(TipoVenda::Peso, $item->tipo_venda);
        $this->assertSame(6400, app(CalcularContaComanda::class)->handle($comanda)['total']);
    }

    /** @return array<string, array{mixed}> */
    public static function pesosInvalidos(): array
    {
        return ['ausente' => [null], 'zero' => [0], 'negativo' => [-1], 'fracionado' => [450.5], 'acima_do_limite' => [10001]];
    }

    #[DataProvider('pesosInvalidos')]
    public function test_peso_invalido_nao_cria_pedido(mixed $peso): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create(['tipo_venda' => TipoVenda::Peso]);

        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [['item_cardapio_id' => $produto->id, 'quantidade' => 1, 'peso_gramas' => $peso]]])->assertSessionHasErrors('itens.0.peso_gramas');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_itens', 0);
    }

    public function test_peso_de_450_gramas_sem_cobranca_extra_custa_27_reais(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create(['tipo_venda' => TipoVenda::Peso, 'preco_centavos' => 6000, 'permite_excesso_carne' => true]);

        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [['item_cardapio_id' => $produto->id, 'quantidade' => 1, 'peso_gramas' => 450, 'cobrar_excesso_carne' => false]]])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pedido_itens', ['preco_unitario_centavos' => 2700, 'adicional_carne_centavos' => 0, 'cobrar_excesso_carne' => false]);
    }

    public function test_escolha_de_cobranca_e_obrigatoria_e_valor_positivo_e_exigido(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create(['permite_excesso_carne' => true]);
        $dados = ['item_cardapio_id' => $produto->id, 'quantidade' => 1];

        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [$dados]])->assertSessionHasErrors('itens.0.cobrar_excesso_carne');
        $this->post(route('comandas.pedidos.store', $comanda), ['itens' => [[...$dados, 'cobrar_excesso_carne' => true, 'adicional_carne' => 0]]])->assertSessionHasErrors('itens.0.adicional_carne');

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_nao_cobrar_nao_aceita_valor_extra_escondido(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create(['permite_excesso_carne' => true]);

        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [['item_cardapio_id' => $produto->id, 'quantidade' => 1, 'cobrar_excesso_carne' => false, 'adicional_carne' => 5]]])->assertSessionHasErrors('itens.0.adicional_carne');

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_produto_sem_opcao_nao_aceita_cobranca_de_carne(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create();

        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [['item_cardapio_id' => $produto->id, 'quantidade' => 1, 'cobrar_excesso_carne' => true, 'adicional_carne' => 5]]])->assertSessionHasErrors('itens.0.cobrar_excesso_carne');

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_edicao_do_rascunho_recalcula_peso_com_preco_historico(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create(['tipo_venda' => TipoVenda::Peso, 'preco_centavos' => 6000, 'permite_excesso_carne' => true]);
        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [['item_cardapio_id' => $produto->id, 'quantidade' => 1, 'peso_gramas' => 450, 'cobrar_excesso_carne' => false]]])->assertSessionHasNoErrors();
        $item = PedidoItem::query()->sole();
        $produto->update(['preco_centavos' => 9000]);

        $this->patch(route('pedido-itens.update', $item), ['quantidade' => 1, 'peso_gramas' => 500, 'cobrar_excesso_carne' => true, 'adicional_carne' => 5])->assertSessionHasNoErrors();

        $this->assertSame(3500, $item->fresh()->preco_unitario_centavos);
        $this->assertSame(6000, $item->fresh()->preco_referencia_centavos);
    }

    public function test_baixa_por_peso_considera_ficha_tecnica_por_quilo(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $produto = ItemCardapio::factory()->create(['tipo_venda' => TipoVenda::Peso, 'preco_centavos' => 6000]);
        $produto->fichaTecnica()->create(['ingrediente_id' => $ingrediente->id, 'quantidade' => 1]);
        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [['item_cardapio_id' => $produto->id, 'quantidade' => 2, 'peso_gramas' => 450]]])->assertSessionHasNoErrors();
        $pedido = $comanda->pedidos()->sole();

        app(EnviarPedido::class)->handle($pedido);

        $this->assertSame('9.100', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseHas('movimentacoes_estoque', ['tipo' => 'baixa', 'quantidade' => -0.9]);
    }

    public function test_modalidades_da_placa_sao_cadastradas_sem_duplicar_nem_sobrescrever_precos(): void
    {
        $this->seed(ModalidadesRestauranteSeeder::class);
        $moda = ItemCardapio::query()->where('nome', 'Moda da casa')->sole();
        $this->assertSame(2800, $moda->preco_centavos);
        $this->assertDatabaseHas('itens_cardapio', ['nome' => 'Self-service à vontade', 'tipo_venda' => 'pessoa', 'preco_centavos' => 3000]);
        $this->assertDatabaseHas('itens_cardapio', ['nome' => 'Monte sua marmita', 'tipo_venda' => 'peso', 'preco_centavos' => 6000]);
        $this->assertDatabaseHas('itens_cardapio', ['nome' => 'Self-service por peso', 'tipo_venda' => 'peso', 'preco_centavos' => 6000]);
        $moda->update(['preco_centavos' => 2900]);

        $this->seed(ModalidadesRestauranteSeeder::class);

        $this->assertDatabaseCount('itens_cardapio', 4);
        $this->assertSame(2900, $moda->fresh()->preco_centavos);
    }

    public function test_somente_proprietario_configura_modalidades_e_excesso(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $categoria = CategoriaCardapio::factory()->create();
        $setor = SetorProducao::factory()->create();
        $dados = ['categoria_cardapio_id' => $categoria->id, 'setor_producao_id' => $setor->id, 'nome' => 'Refeição por peso', 'preco' => 60, 'tipo_venda' => 'peso', 'permite_excesso_carne' => true, 'disponivel' => true, 'ordem' => 1];

        $this->actingAs($gerente)->post(route('itens-cardapio.store'), $dados)->assertForbidden();
        $this->actingAs($proprietario)->post(route('itens-cardapio.store'), $dados)->assertSessionHasNoErrors();
        $produto = ItemCardapio::query()->sole();
        $this->put(route('itens-cardapio.update', $produto), [...$dados, 'preco' => 65, 'permite_excesso_carne' => false])->assertSessionHasNoErrors();

        $this->assertSame(TipoVenda::Peso, $produto->fresh()->tipo_venda);
        $this->assertSame(6500, $produto->fresh()->preco_centavos);
        $this->assertFalse($produto->fresh()->permite_excesso_carne);
    }

    public function test_a_vontade_multiplica_valor_por_pessoa(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $comanda = Comanda::factory()->create();
        $produto = ItemCardapio::factory()->create(['tipo_venda' => TipoVenda::Pessoa, 'preco_centavos' => 3000]);

        $this->actingAs($usuario)->post(route('comandas.pedidos.store', $comanda), ['itens' => [['item_cardapio_id' => $produto->id, 'quantidade' => 2]]])->assertSessionHasNoErrors();

        $this->assertSame(6000, app(CalcularContaComanda::class)->handle($comanda)['total']);
    }

    public function test_edicao_sem_excesso_aceita_campos_vazios_da_interface(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $item = PedidoItem::factory()->create(['quantidade' => 1, 'preco_unitario_centavos' => 2800]);

        $this->actingAs($usuario)->patch(route('pedido-itens.update', $item), ['quantidade' => 2, 'peso_gramas' => '', 'cobrar_excesso_carne' => '', 'adicional_carne' => ''])->assertSessionHasNoErrors();

        $this->assertSame(5600, $item->fresh()->subtotalCentavos());
        $this->assertFalse($item->fresh()->cobrar_excesso_carne);
        $this->assertSame(0, $item->fresh()->adicional_carne_centavos);
    }
}
