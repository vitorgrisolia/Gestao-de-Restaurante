<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><title>Pedido #{{ $impressao->pedido_id }}</title>
<style>
@@page { size: 80mm auto; margin: 3mm; }
* { box-sizing: border-box; } body { width: 72mm; margin: 0; font: 12px/1.35 monospace; color: #000; }
.centro { text-align: center; } .linha { border-top: 1px dashed #000; margin: 8px 0; } .obs { padding-left: 12px; font-weight: bold; }
@@media screen { body { margin: 20px auto; } .acoes { display: flex; gap: 8px; margin-top: 20px; } }
@@media print { .acoes { display: none; } }
</style></head><body>
<header class="centro"><strong>GESTÃO RESTAURANTE</strong><br>SETOR: {{ mb_strtoupper($impressao->setorProducao->nome) }}</header>
<div class="linha"></div><strong>PEDIDO #{{ $impressao->pedido_id }}</strong><br>
Mesa: {{ $impressao->pedido->comanda->mesa->numero }}<br>Emissão: {{ $impressao->solicitada_em->format('d/m/Y H:i') }}<br>Via: {{ $impressao->sequencia }} ({{ $impressao->tipo }})<div class="linha"></div>
@foreach ($impressao->pedido->itens as $item)
<div><strong>{{ $item->quantidade }}x {{ $item->nome_item }}</strong></div>
@if ($item->peso_gramas !== null)
<div>{{ $item->peso_gramas }} g por porção</div>
@endif
@if ($item->cobrar_excesso_carne)
<div>Excesso de carne: R$ {{ number_format($item->adicional_carne_centavos / 100, 2, ',', '.') }} por porção/pessoa</div>
@endif
@if ($item->observacao)
<div class="obs">OBS: {{ $item->observacao }}</div>
@endif
@endforeach
<div class="linha"></div><div class="centro">Responsável: {{ $impressao->solicitadaPor->name }}<br>Chave: {{ $impressao->chave_idempotencia }}</div>
<form class="acoes" method="post" action="{{ route('impressoes-termicas.update', $impressao) }}">@csrf @method('PATCH')<button type="button" onclick="window.print()">Imprimir</button><button type="submit">Confirmar impressão</button></form>
</body></html>
