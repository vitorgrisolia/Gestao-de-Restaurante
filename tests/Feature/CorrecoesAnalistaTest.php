<?php

namespace Tests\Feature;

use App\Actions\CancelarPedido;
use App\Actions\ConsultarBackupVerificado;
use App\Actions\EnviarPedido;
use App\Actions\MovimentarEstoque;
use App\Console\Commands\CriarBackup;
use App\Models\Comanda;
use App\Models\ImpressaoProducao;
use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\ItemCardapio;
use App\Models\Pagamento;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\PapelUsuario;
use App\StatusItemPedido;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class CorrecoesAnalistaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_venda_durante_inventario_preserva_baixa_e_aplica_diferenca_inicial(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $produto = ItemCardapio::factory()->create();
        $produto->fichaTecnica()->create(['ingrediente_id' => $ingrediente->id, 'quantidade' => 2]);
        $inventario = Inventario::factory()->create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $item = $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 10]);
        $pedido = Pedido::factory()->create(['criado_por_id' => $usuario->id]);
        PedidoItem::factory()->for($pedido)->create(['item_cardapio_id' => $produto->id, 'quantidade' => 1]);

        app(EnviarPedido::class)->handle($pedido);
        $this->actingAs($usuario)->patch(route('inventarios.update', $inventario), ['itens' => [['id' => $item->id, 'quantidade_contada' => 9]]])->assertSessionHasNoErrors();

        $this->assertSame('7.000', $ingrediente->fresh()->estoque_atual);
        $this->assertSame('concluido', $inventario->fresh()->status);
        $this->assertDatabaseHas('movimentacoes_estoque', ['tipo' => 'baixa', 'quantidade' => -2]);
        $this->assertDatabaseHas('movimentacoes_estoque', ['tipo' => 'inventario', 'quantidade' => -1]);
    }

    public function test_ajuste_de_inventario_negativo_rejeita_toda_conclusao(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 1]);
        $inventario = Inventario::factory()->create(['status' => 'aberto', 'iniciado_por_id' => $usuario->id, 'iniciado_em' => now()]);
        $item = $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => 10]);

        $this->actingAs($usuario)->patch(route('inventarios.update', $inventario), ['itens' => [['id' => $item->id, 'quantidade_contada' => 8]]])->assertSessionHasErrors('estoque');

        $this->assertSame('1.000', $ingrediente->fresh()->estoque_atual);
        $this->assertSame('aberto', $inventario->fresh()->status);
        $this->assertDatabaseCount('movimentacoes_estoque', 0);
    }

    public function test_recontagem_preserva_ingredientes_nao_alterados(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $inventario = Inventario::factory()->create(['status' => 'aberto', 'iniciado_por_id' => $usuario->id, 'iniciado_em' => now()]);
        $alterado = $inventario->itens()->create(['ingrediente_id' => Ingrediente::factory()->create(['estoque_atual' => 8])->id, 'quantidade_sistema' => 10, 'quantidade_contada' => 9]);
        $preservado = $inventario->itens()->create(['ingrediente_id' => Ingrediente::factory()->create(['estoque_atual' => 10])->id, 'quantidade_sistema' => 10, 'quantidade_contada' => 9]);

        $this->actingAs($usuario)->post(route('inventarios.recontagem', $inventario))->assertSessionHasNoErrors();

        $this->assertNull($alterado->fresh()->quantidade_contada);
        $this->assertSame('8.000', $alterado->fresh()->quantidade_sistema);
        $this->assertSame('9.000', $preservado->fresh()->quantidade_contada);
        $this->assertDatabaseCount('movimentacoes_estoque', 0);
    }

    public function test_dois_backups_no_mesmo_segundo_nao_colidem(): void
    {
        $anterior = app()->storagePath();
        app()->useStoragePath(sys_get_temp_dir().'/backups-unicos-'.bin2hex(random_bytes(8)));
        try {
            File::ensureDirectoryExists(storage_path());
            $origem = storage_path('origem.sqlite');
            (new \PDO('sqlite:'.$origem))->exec('CREATE TABLE teste (id INTEGER)');
            config()->set('database.connections.sqlite.database', $origem);
            $this->freezeTime();

            $this->artisan('backup:criar')->assertSuccessful();
            $this->artisan('backup:criar')->assertSuccessful();

            $this->assertCount(2, File::glob(storage_path('app/private/backups/*.sqlite')));
            $this->assertNotNull(app(ConsultarBackupVerificado::class)->handle());
        } finally {
            app()->useStoragePath($anterior);
        }
    }

    public function test_falha_de_integridade_nao_publica_backup_nem_deixa_arquivo_parcial(): void
    {
        $anterior = app()->storagePath();
        app()->useStoragePath(sys_get_temp_dir().'/backup-rejeitado-'.bin2hex(random_bytes(8)));
        try {
            File::ensureDirectoryExists(storage_path());
            $origem = storage_path('origem.sqlite');
            (new \PDO('sqlite:'.$origem))->exec('CREATE TABLE teste (id INTEGER)');
            config()->set('database.connections.sqlite.database', $origem);
            $comando = new class extends CriarBackup
            {
                protected function verificarIntegridade(string $arquivo): void
                {
                    throw new RuntimeException('O backup falhou na verificação de integridade.');
                }
            };
            $comando->setLaravel(app());

            try {
                $comando->run(new ArrayInput([]), new BufferedOutput);
                $this->fail('O backup inválido deveria ser rejeitado.');
            } catch (RuntimeException $exception) {
                $this->assertSame('O backup falhou na verificação de integridade.', $exception->getMessage());
            }

            $this->assertSame([], File::glob(storage_path('app/private/backups/*')));
            $this->assertNull(app(ConsultarBackupVerificado::class)->handle());
        } finally {
            app()->useStoragePath($anterior);
        }
    }

    public function test_monitoramento_ignora_arquivo_sem_checksum_e_checksum_invalido(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $anterior = app()->storagePath();
        app()->useStoragePath(sys_get_temp_dir().'/monitor-backup-'.bin2hex(random_bytes(8)));
        try {
            File::ensureDirectoryExists(storage_path('app/private/backups'));
            File::put(storage_path('app/private/backups/incompleto.sqlite'), 'incompleto');
            File::put(storage_path('app/private/backups/invalido.sqlite'), 'invalido');
            File::put(storage_path('app/private/backups/invalido.sqlite.json'), json_encode(['sha256' => hash_file('sha256', storage_path('app/private/backups/invalido.sqlite'))]));
            File::put(storage_path('app/private/backups/corrompido.sqlite'), 'alterado');
            File::put(storage_path('app/private/backups/corrompido.sqlite.json'), json_encode(['sha256' => str_repeat('0', 64)]));

            $this->actingAs($usuario)->get(route('monitoramento.show'))->assertStatus(503)->assertJsonPath('componentes.backup_recente', false);
            $this->artisan('sistema:verificar')->assertFailed();

            $this->assertNull(app(ConsultarBackupVerificado::class)->handle());
        } finally {
            app()->useStoragePath($anterior);
        }
    }

    public function test_dashboard_conta_apenas_comandas_com_saldo_pendente(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        Comanda::factory()->create();
        $pendente = Comanda::factory()->create();
        PedidoItem::factory()->for(Pedido::factory()->create(['comanda_id' => $pendente->id]))->create(['quantidade' => 1, 'preco_unitario_centavos' => 1000]);
        $quitada = Comanda::factory()->create();
        PedidoItem::factory()->for(Pedido::factory()->create(['comanda_id' => $quitada->id]))->create(['quantidade' => 1, 'preco_unitario_centavos' => 1000]);
        Pagamento::factory()->create(['comanda_id' => $quitada->id, 'valor_centavos' => 1000]);

        $this->actingAs($usuario)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('secoes.0.indicadores.Comandas abertas', 3)
            ->where('secoes.2.indicadores.Comandas a receber', 1));
    }

    public function test_item_cancelado_nao_aparece_no_cupom(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $impressao = ImpressaoProducao::factory()->create();
        PedidoItem::factory()->create(['pedido_id' => $impressao->pedido_id, 'setor_producao_id' => $impressao->setor_producao_id, 'status' => StatusItemPedido::Cancelado, 'nome_item' => 'Produto cancelado exclusivo']);
        PedidoItem::factory()->enviado()->create(['pedido_id' => $impressao->pedido_id, 'setor_producao_id' => $impressao->setor_producao_id, 'nome_item' => 'Produto ativo exclusivo']);

        $this->actingAs($usuario)->get(route('impressoes-termicas.show', $impressao))->assertSee('Produto ativo exclusivo')->assertDontSee('Produto cancelado exclusivo');
    }

    public function test_csv_neutraliza_formulas_sem_alterar_auditoria(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $ingrediente = Ingrediente::factory()->create();
        app(MovimentarEstoque::class)->handle($ingrediente, $usuario, 'entrada', 1, '=1+1');

        $csv = $this->actingAs($usuario)->get(route('estoque.relatorio'))->streamedContent();

        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertDatabaseHas('movimentacoes_estoque', ['motivo' => '=1+1']);
    }

    public function test_cancelamento_em_preparo_registra_perda_sem_nova_baixa(): void
    {
        $usuario = User::factory()->create();
        $ingrediente = Ingrediente::factory()->create(['estoque_atual' => 10]);
        $pedido = Pedido::factory()->enviado()->create();
        $item = PedidoItem::factory()->for($pedido)->create(['status' => StatusItemPedido::EmPreparo, 'iniciado_em' => now()]);
        app(MovimentarEstoque::class)->handle($ingrediente, $usuario, 'baixa', -3, pedidoItemId: $item->id);

        app(CancelarPedido::class)->handle($pedido, $usuario, 'Cliente desistiu');

        $this->assertSame('7.000', $ingrediente->fresh()->estoque_atual);
        $this->assertDatabaseCount('movimentacoes_estoque', 1);
        $this->assertDatabaseHas('movimentacoes_estoque', ['tipo' => 'perda', 'quantidade' => -3, 'pedido_item_id' => $item->id]);
    }

    public function test_backup_externo_nao_verificado_nao_e_reportado_como_saudavel(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $conexao = DB::connection();
        DB::extend('externo-teste', fn () => $conexao);
        config()->set('database.connections.externo-teste', ['driver' => 'externo-teste']);
        config()->set('database.default', 'externo-teste');

        $this->actingAs($usuario)->get(route('monitoramento.show'))->assertStatus(503)->assertJsonPath('componentes.backup', 'externo_nao_verificado');
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('secoes.7.indicadores.Último backup local', 'Não se aplica — backup externo não verificado'));
        $this->artisan('sistema:verificar')->assertFailed()->expectsOutput('Backup externo não verificado. Configure a verificação do banco utilizado.');
    }

    public function test_recontagem_preserva_valores_digitados_ainda_nao_salvos(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $inventario = Inventario::factory()->create(['status' => 'aberto', 'iniciado_por_id' => $usuario->id, 'iniciado_em' => now()]);
        $item = $inventario->itens()->create(['ingrediente_id' => Ingrediente::factory()->create(['estoque_atual' => 10])->id, 'quantidade_sistema' => 10]);

        $this->actingAs($usuario)->post(route('inventarios.recontagem', $inventario), ['itens' => [['id' => $item->id, 'quantidade_contada' => 9]]])->assertSessionHasNoErrors();

        $this->assertSame('9.000', $item->fresh()->quantidade_contada);
    }

    public function test_alerta_de_minimo_ignora_ingrediente_inativo_em_ambas_as_telas(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        Ingrediente::factory()->create(['ativo' => false, 'estoque_atual' => 0, 'estoque_minimo' => 2]);
        Ingrediente::factory()->create(['ativo' => true, 'estoque_atual' => 1, 'estoque_minimo' => 2]);

        $this->actingAs($usuario)->get(route('estoque.index'))->assertInertia(fn (Assert $page) => $page->where('resumo.abaixo_minimo', 1));
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('secoes.3.indicadores.No mínimo ou abaixo', 1));
    }

    public function test_abertura_simultanea_de_inventario_e_rejeitada_sem_criar_registros(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Estoque]);
        $bloqueio = Cache::store('database')->lock('inventarios:abertura', 60);
        $this->assertTrue($bloqueio->get());

        try {
            $this->actingAs($usuario)->post(route('inventarios.store'), [])->assertSessionHasErrors('inventario');

            $this->assertDatabaseCount('inventarios', 0);
        } finally {
            $bloqueio->release();
        }
    }
}
