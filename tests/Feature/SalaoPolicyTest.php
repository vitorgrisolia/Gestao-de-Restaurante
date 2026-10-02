<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\SetorSalao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SalaoPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('papeisComPermissaoEsperada')]
    public function test_apenas_proprietario_e_gerente_gerenciam_o_salao(
        PapelUsuario $papel,
        bool $permitido,
    ): void {
        $usuario = User::factory()->create(['papel' => $papel]);

        $this->assertSame($permitido, $usuario->can('create', SetorSalao::class));
        $this->assertSame($permitido, $usuario->can('create', Mesa::class));
    }

    /** @return array<string, array{PapelUsuario, bool}> */
    public static function papeisComPermissaoEsperada(): array
    {
        return [
            'proprietário' => [PapelUsuario::Proprietario, true],
            'gerente' => [PapelUsuario::Gerente, true],
            'caixa' => [PapelUsuario::Caixa, false],
            'atendente' => [PapelUsuario::Atendente, false],
            'cozinha' => [PapelUsuario::Cozinha, false],
            'estoque' => [PapelUsuario::Estoque, false],
        ];
    }
}
