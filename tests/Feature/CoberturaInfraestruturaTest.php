<?php

namespace Tests\Feature;

use App\Console\Commands\RestaurarBackup;
use App\Models\ImpressaoProducao;
use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\ItemCardapio;
use App\Models\UnidadeMedida;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class CoberturaInfraestruturaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_atendente_nao_acessa_nem_confirma_cupom(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Atendente]);
        $impressao = ImpressaoProducao::factory()->create();

        $this->actingAs($usuario)->get(route('impressoes-termicas.show', $impressao))->assertForbidden();
        $this->patch(route('impressoes-termicas.update', $impressao))->assertForbidden();

        $this->assertNull($impressao->fresh()->impressa_em);
    }

    public function test_monitoramento_e_comando_detectam_backup_ausente(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        File::shouldReceive('glob')->twice()->andReturn([]);

        $this->actingAs($usuario)->get(route('monitoramento.show'))->assertStatus(503)->assertJsonPath('componentes.backup_recente', false);
        $this->artisan('sistema:verificar')->expectsOutput('Nenhum backup criado nas últimas 24 horas')->assertFailed();
    }

    public function test_monitoramento_e_comando_detectam_jobs_falhos(): void
    {
        $usuario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        DB::table('failed_jobs')->insert(['uuid' => 'teste-falha', 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Falha de teste', 'failed_at' => now()]);
        File::shouldReceive('glob')->twice()->andReturn([]);

        $this->actingAs($usuario)->get(route('monitoramento.show'))->assertStatus(503)->assertJsonPath('componentes.fila_falhas', 1);
        $this->artisan('sistema:verificar')->expectsOutput('1 trabalho(s) com falha na fila')->assertFailed();
    }

    public function test_checksum_invalido_nao_altera_banco_nem_estado_de_manutencao(): void
    {
        $storageAnterior = app()->storagePath();
        app()->useStoragePath(sys_get_temp_dir().'/checksum-test-'.bin2hex(random_bytes(8)));
        try {
            $diretorio = storage_path('app/private/backups');
            File::ensureDirectoryExists($diretorio);
            File::put($diretorio.'/teste.sqlite', 'conteudo alterado');
            File::put($diretorio.'/teste.sqlite.json', json_encode(['sha256' => str_repeat('0', 64)], JSON_THROW_ON_ERROR));
            $destino = storage_path('original.sqlite');
            File::put($destino, 'original preservado');
            config()->set('database.connections.sqlite.database', $destino);
            Artisan::shouldReceive('call')->never();
            $comando = app(RestaurarBackup::class);
            $comando->setLaravel(app());
            try {
                $comando->run(new ArrayInput(['arquivo' => 'teste.sqlite', '--force' => true]), new BufferedOutput);
                $this->fail('Checksum inválido deveria rejeitar a restauração.');
            } catch (RuntimeException $exception) {
                $this->assertSame('Checksum inválido. O backup pode estar corrompido.', $exception->getMessage());
            }
            $this->assertSame('original preservado', File::get($destino));
        } finally {
            app()->useStoragePath($storageAnterior);
        }
    }

    /** @return array<string, array{PapelUsuario, bool}> */
    public static function papeis(): array
    {
        return ['gerente' => [PapelUsuario::Gerente, true], 'estoque' => [PapelUsuario::Estoque, true], 'cozinha' => [PapelUsuario::Cozinha, false], 'atendente' => [PapelUsuario::Atendente, false]];
    }

    #[DataProvider('papeis')]
    public function test_permissoes_operacionais_e_cadastros(PapelUsuario $papel, bool $operaEstoque): void
    {
        $usuario = User::factory()->create(['papel' => $papel]);
        $ingrediente = Ingrediente::factory()->create();
        $unidade = UnidadeMedida::factory()->create();
        $produto = ItemCardapio::factory()->create();
        $this->actingAs($usuario);

        $painel = $this->get(route('estoque.index'));
        $relatorio = $this->get(route('estoque.relatorio'));
        if ($operaEstoque) {
            $painel->assertOk();
            $relatorio->assertOk();
            $this->post(route('movimentacoes-estoque.store', $ingrediente), ['tipo' => 'entrada', 'quantidade' => 1, 'motivo' => 'Entrada autorizada'])->assertSessionHasNoErrors();
        } else {
            $painel->assertForbidden();
            $relatorio->assertForbidden();
            $this->post(route('movimentacoes-estoque.store', $ingrediente), ['tipo' => 'entrada', 'quantidade' => 1, 'motivo' => 'Entrada proibida'])->assertForbidden();
        }

        $this->post(route('ingredientes.store'), [])->assertForbidden();
        $this->put(route('ingredientes.update', $ingrediente), [])->assertForbidden();
        $this->delete(route('ingredientes.destroy', $ingrediente))->assertForbidden();
        $this->post(route('unidades-medida.store'), [])->assertForbidden();
        $this->put(route('unidades-medida.update', $unidade), [])->assertForbidden();
        $this->delete(route('unidades-medida.destroy', $unidade))->assertForbidden();
        $this->put(route('fichas-tecnicas.update', $produto), ['ingredientes' => []])->assertForbidden();
        $this->get(route('monitoramento.show'))->assertForbidden();
        $inventario = Inventario::create(['iniciado_por_id' => $usuario->id, 'status' => 'aberto', 'iniciado_em' => now()]);
        $item = $inventario->itens()->create(['ingrediente_id' => $ingrediente->id, 'quantidade_sistema' => $ingrediente->fresh()->estoque_atual]);
        if ($operaEstoque) {
            $this->post(route('inventarios.recontagem', $inventario))->assertSessionHasNoErrors();
            $this->patch(route('inventarios.update', $inventario), ['itens' => [['id' => $item->id, 'quantidade_contada' => $ingrediente->fresh()->estoque_atual]]])->assertSessionHasNoErrors();
            $this->post(route('inventarios.store'), [])->assertSessionHasNoErrors();
        } else {
            $this->post(route('inventarios.store'), [])->assertForbidden();
            $this->post(route('inventarios.recontagem', $inventario))->assertForbidden();
            $this->patch(route('inventarios.update', $inventario), [])->assertForbidden();
        }

        $impressao = ImpressaoProducao::factory()->create();
        $cupom = $this->get(route('impressoes-termicas.show', $impressao));
        $confirmacao = $this->patch(route('impressoes-termicas.update', $impressao));
        if ($papel->podeOperarProducao()) {
            $cupom->assertOk();
            $confirmacao->assertRedirect();
            $this->assertNotNull($impressao->fresh()->impressa_em);
        } else {
            $cupom->assertForbidden();
            $confirmacao->assertForbidden();
            $this->assertNull($impressao->fresh()->impressa_em);
        }
    }
}
