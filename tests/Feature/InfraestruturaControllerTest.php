<?php

namespace Tests\Feature;

use App\Models\ImpressaoProducao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InfraestruturaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_monitoramento_e_restrito_ao_proprietario(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        $gerente = User::factory()->create(['papel' => PapelUsuario::Gerente]);
        $this->actingAs($gerente)->get(route('monitoramento.show'))->assertForbidden();
        File::shouldReceive('glob')->once()->andReturn([]);
        $this->actingAs($proprietario)->get(route('monitoramento.show'))->assertStatus(503)->assertJsonStructure(['status', 'componentes' => ['banco', 'armazenamento', 'fila_falhas', 'ultimo_backup', 'ambiente']]);
    }

    public function test_cozinha_visualiza_cupom_e_confirma_impressao_sem_duplicar_data(): void
    {
        $cozinha = User::factory()->create(['papel' => PapelUsuario::Cozinha]);
        $impressao = ImpressaoProducao::factory()->create();
        $this->actingAs($cozinha)->get(route('impressoes-termicas.show', $impressao))->assertOk()->assertSee('PEDIDO #'.$impressao->pedido_id);
        $this->actingAs($cozinha)->patch(route('impressoes-termicas.update', $impressao))->assertRedirect();
        $primeira = $impressao->fresh()->impressa_em;
        $this->travel(1)->minutes();
        $this->actingAs($cozinha)->patch(route('impressoes-termicas.update', $impressao));
        $this->assertTrue($primeira->equalTo($impressao->fresh()->impressa_em));
    }
}
