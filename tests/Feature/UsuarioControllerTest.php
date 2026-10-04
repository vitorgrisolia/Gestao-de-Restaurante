<?php

namespace Tests\Feature;

use App\Models\User;
use App\PapelUsuario;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_proprietario_visualiza_cadastro_de_usuarios(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);

        $this->actingAs($proprietario)
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $pagina) => $pagina
                ->component('usuarios/index')
                ->has('usuarios', 1)
                ->has('papeis', count(PapelUsuario::cases())));
    }

    public function test_atendente_nao_visualiza_cadastro_de_usuarios(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);

        $this->actingAs($atendente)
            ->get(route('usuarios.index'))
            ->assertForbidden();
    }

    public function test_proprietario_cadastra_usuario_verificado(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);

        $this->actingAs($proprietario)
            ->from(route('usuarios.index'))
            ->post(route('usuarios.store'), [
                'name' => 'Maria Caixa',
                'email' => 'maria@restaurante.test',
                'papel' => PapelUsuario::Caixa->value,
                'password' => 'senha-segura',
                'password_confirmation' => 'senha-segura',
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success', 'Usuário cadastrado com sucesso.');

        $usuario = User::query()->where('email', 'maria@restaurante.test')->firstOrFail();

        $this->assertSame('Maria Caixa', $usuario->name);
        $this->assertSame(PapelUsuario::Caixa, $usuario->papel);
        $this->assertNotNull($usuario->email_verified_at);
        $this->assertTrue(Hash::check('senha-segura', $usuario->password));
    }

    public function test_rejeita_email_repetido_e_papel_invalido(): void
    {
        $proprietario = User::factory()->create(['papel' => PapelUsuario::Proprietario]);
        User::factory()->create(['email' => 'existente@restaurante.test']);

        $this->actingAs($proprietario)
            ->post(route('usuarios.store'), [
                'name' => 'Usuário repetido',
                'email' => 'existente@restaurante.test',
                'papel' => 'administrador_total',
                'password' => 'senha-segura',
                'password_confirmation' => 'senha-segura',
            ])
            ->assertSessionHasErrors([
                'email' => 'Este e-mail já está cadastrado.',
                'papel' => 'Selecione um papel válido.',
            ]);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_atendente_nao_cadastra_usuario(): void
    {
        $atendente = User::factory()->create(['papel' => PapelUsuario::Atendente]);

        $this->actingAs($atendente)
            ->post(route('usuarios.store'), [
                'name' => 'Usuário indevido',
                'email' => 'indevido@restaurante.test',
                'papel' => PapelUsuario::Gerente->value,
                'password' => 'senha-segura',
                'password_confirmation' => 'senha-segura',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'indevido@restaurante.test']);
    }
}
