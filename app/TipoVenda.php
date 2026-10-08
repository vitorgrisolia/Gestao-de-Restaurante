<?php

namespace App;

enum TipoVenda: string
{
    case Unidade = 'unidade';
    case Peso = 'peso';
    case Pessoa = 'pessoa';
}
