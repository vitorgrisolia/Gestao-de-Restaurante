<?php

namespace App;

enum StatusComanda: string
{
    case Aberta = 'aberta';
    case Fechada = 'fechada';
    case Cancelada = 'cancelada';
}
