<?php

namespace App;

enum PapelUsuario: string
{
    case Proprietario = 'proprietario';
    case Gerente = 'gerente';
    case Caixa = 'caixa';
    case Atendente = 'atendente';
    case Cozinha = 'cozinha';
    case Estoque = 'estoque';

    public function podeGerenciarSalao(): bool
    {
        return in_array($this, [self::Proprietario, self::Gerente], true);
    }

    public function podeAbrirComanda(): bool
    {
        return in_array($this, [self::Proprietario, self::Gerente, self::Caixa, self::Atendente], true);
    }

    public function podeRegistrarPedido(): bool
    {
        return in_array($this, [self::Proprietario, self::Gerente, self::Caixa, self::Atendente], true);
    }

    public function podeReceberPagamento(): bool
    {
        return in_array($this, [self::Proprietario, self::Gerente, self::Caixa], true);
    }

    public function podeAdministrarCadastros(): bool
    {
        return $this === self::Proprietario;
    }

    public function nome(): string
    {
        return match ($this) {
            self::Proprietario => 'Proprietário',
            self::Gerente => 'Gerente',
            self::Caixa => 'Caixa',
            self::Atendente => 'Atendente',
            self::Cozinha => 'Cozinha',
            self::Estoque => 'Estoque',
        };
    }
}
