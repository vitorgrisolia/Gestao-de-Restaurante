<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContaPagamentoCaixaTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_inicio_redireciona_para_visao_geral(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
