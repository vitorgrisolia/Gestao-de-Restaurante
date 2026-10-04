<?php

namespace App;

enum FormaPagamento: string
{
    case Dinheiro = 'dinheiro';
    case Pix = 'pix';
    case CartaoDebito = 'cartao_debito';
    case CartaoCredito = 'cartao_credito';

    public function nome(): string
    {
        return match ($this) {
            self::Dinheiro => 'Dinheiro',
            self::Pix => 'Pix',
            self::CartaoDebito => 'Cartão de débito',
            self::CartaoCredito => 'Cartão de crédito',
        };
    }
}
