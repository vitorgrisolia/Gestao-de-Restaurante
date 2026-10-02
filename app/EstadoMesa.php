<?php

namespace App;

enum EstadoMesa: string
{
    case Livre = 'livre';
    case Ocupada = 'ocupada';
    case Reservada = 'reservada';
    case AguardandoPagamento = 'aguardando_pagamento';
}
