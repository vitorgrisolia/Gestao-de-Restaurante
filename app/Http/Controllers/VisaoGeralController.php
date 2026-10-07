<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\CategoriaCardapio;
use App\Models\Comanda;
use App\Models\Ingrediente;
use App\Models\Inventario;
use App\Models\ItemCardapio;
use App\Models\Mesa;
use App\Models\Pagamento;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\SetorProducao;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class VisaoGeralController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $papel = $request->user()->papel;
        $secoes = [];

        if ($papel->podeAbrirComanda()) {
            $secoes[] = $this->secao('salao', 'Salão e comandas', 'Ocupação atual e atendimentos em andamento.', 'salao.index', [
                'Mesas livres' => Mesa::query()->where('estado', 'livre')->count(),
                'Mesas ocupadas' => Mesa::query()->whereIn('estado', ['ocupada', 'aguardando_pagamento'])->count(),
                'Comandas abertas' => Comanda::query()->where('ativa', true)->count(),
                'Pedidos em rascunho' => Pedido::query()->where('status', 'rascunho')->whereHas('comanda', fn ($query) => $query->where('ativa', true))->count(),
            ]);
        }

        if ($papel->podeOperarProducao()) {
            $secoes[] = $this->secao('producao', 'Produção', 'Os indicadores representam itens de pedido.', 'producao.index', [
                'Recebidos' => PedidoItem::query()->where('status', 'enviado')->count(),
                'Em preparo' => PedidoItem::query()->where('status', 'em_preparo')->count(),
                'Prontos' => PedidoItem::query()->where('status', 'pronto')->count(),
                'Recebidos há mais de 15 min' => PedidoItem::query()->where('status', 'enviado')->where('enviado_em', '<', now()->subMinutes(15))->count(),
                'Entregues hoje' => PedidoItem::query()->where('status', 'entregue')->whereDate('entregue_em', today())->count(),
            ]);
        }

        if ($papel->podeReceberPagamento()) {
            $recebido = (int) Pagamento::query()->whereNull('estornado_em')->whereDate('pago_em', today())->sum('valor_centavos');
            $secoes[] = $this->secao('caixa', 'Conta e caixa', 'Pagamentos recebidos hoje, sem os estornados.', 'caixas.index', [
                'Situação do caixa' => Caixa::query()->where('aberto', true)->exists() ? 'Aberto' : 'Fechado — abra para receber',
                'Recebido hoje' => $this->dinheiro($recebido),
                'Pagamentos hoje' => Pagamento::query()->whereNull('estornado_em')->whereDate('pago_em', today())->count(),
                'Comandas a receber' => Comanda::query()->where('ativa', true)->count(),
            ]);
        }

        if ($papel->podeOperarEstoque()) {
            $secoes[] = $this->secao('estoque', 'Estoque', 'Ingredientes, contagens e reposição.', 'estoque.index', [
                'Ingredientes ativos' => Ingrediente::query()->where('ativo', true)->count(),
                'No mínimo ou abaixo' => Ingrediente::query()->where('ativo', true)->whereColumn('estoque_atual', '<=', 'estoque_minimo')->count(),
                'Inventários abertos' => Inventario::query()->where('status', 'aberto')->count(),
            ]);
        }

        if ($papel->podeAdministrarCadastros()) {
            $secoes[] = $this->secao('cardapio', 'Cardápio', 'Categorias e disponibilidade de produtos.', 'cardapio.index', [
                'Categorias' => CategoriaCardapio::query()->count(),
                'Itens disponíveis' => ItemCardapio::query()->where('disponivel', true)->count(),
                'Itens indisponíveis' => ItemCardapio::query()->where('disponivel', false)->count(),
            ]);
            $secoes[] = $this->secao('setores', 'Setores de produção', 'Organização da cozinha e do bar.', 'setores-producao.index', [
                'Setores ativos' => SetorProducao::query()->where('ativo', true)->count(),
                'Setores inativos' => SetorProducao::query()->where('ativo', false)->count(),
            ]);
            $usuarios = [];
            foreach (PapelUsuario::cases() as $papelUsuario) {
                $usuarios[$papelUsuario->nome()] = User::query()->where('papel', $papelUsuario->value)->count();
            }
            $secoes[] = $this->secao('usuarios', 'Usuários', 'Equipe cadastrada por função.', 'usuarios.index', $usuarios);
            $backups = collect(File::glob(storage_path('app/private/backups/*.sqlite')))->sortDesc();
            $ultimo = $backups->first();
            $data = $ultimo ? filemtime($ultimo) : false;
            $secoes[] = $this->secao('infraestrutura', 'Infraestrutura', 'Fila e backups locais. Consulte o monitoramento para detalhes.', 'monitoramento.show', [
                'Trabalhos com falha' => DB::table('failed_jobs')->count(),
                'Último backup local' => $data !== false ? date('d/m/Y H:i', $data) : 'Nenhum backup local encontrado',
            ]);
        }

        return Inertia::render('dashboard', ['secoes' => $secoes, 'atualizadoEm' => now()->toIso8601String()]);
    }

    /** @param array<string, int|string> $indicadores
     * @return array{id:string,titulo:string,descricao:string,url:string,indicadores:array<string,int|string>}
     */
    private function secao(string $id, string $titulo, string $descricao, string $rota, array $indicadores): array
    {
        return ['id' => $id, 'titulo' => $titulo, 'descricao' => $descricao, 'url' => route($rota), 'indicadores' => $indicadores];
    }

    private function dinheiro(int $centavos): string
    {
        return 'R$ '.number_format($centavos / 100, 2, ',', '.');
    }
}
