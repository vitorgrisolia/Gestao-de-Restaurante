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
}
