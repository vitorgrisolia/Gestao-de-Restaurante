<?php

namespace App;

enum StatusItemPedido: string
{
    case Rascunho = 'rascunho';
    case Enviado = 'enviado';
    case EmPreparo = 'em_preparo';
    case Pronto = 'pronto';
    case Entregue = 'entregue';
    case Cancelado = 'cancelado';
}
