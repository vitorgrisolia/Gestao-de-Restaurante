<?php

namespace App\Http\Controllers;

use App\Actions\CalcularContaComanda;
use App\Actions\ConsultarBackupVerificado;
use App\EstadoMesa;
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
use App\StatusItemPedido;
use App\StatusPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VisaoGeralController extends Controller
{
    public function __invoke(Request $request, ConsultarBackupVerificado $consultarBackup, CalcularContaComanda $calcularConta): Response
    {
        $papel = $request->user()->papel;
        $secoes = [];
        $comandas = ($papel->podeAbrirComanda() || $papel->podeReceberPagamento())
            ? Comanda::query()->where('ativa', true)->with(['pedidos.itens', 'pagamentos'])->get()
            : collect();

        if ($papel->podeAbrirComanda()) {
            $secoes[] = $this->secao('salao', 'Salão e comandas', 'Ocupação atual e atendimentos em andamento.', 'salao.index', [
                'Mesas livres' => Mesa::query()->where('estado', EstadoMesa::Livre)->count(),
                'Mesas ocupadas' => Mesa::query()->whereIn('estado', [EstadoMesa::Ocupada, EstadoMesa::AguardandoPagamento])->count(),
                'Comandas abertas' => $comandas->count(),
                'Pedidos em rascunho' => Pedido::query()->where('status', StatusPedido::Rascunho)->whereHas('comanda', fn ($query) => $query->where('ativa', true))->count(),
            ]);
        }

        if ($papel->podeOperarProducao()) {
            $secoes[] = $this->secao('producao', 'Produção', 'Os indicadores representam itens de pedido.', 'producao.index', [
                'Recebidos' => PedidoItem::query()->where('status', StatusItemPedido::Enviado)->count(),
                'Em preparo' => PedidoItem::query()->where('status', StatusItemPedido::EmPreparo)->count(),
                'Prontos' => PedidoItem::query()->where('status', StatusItemPedido::Pronto)->count(),
                'Recebidos há mais de 15 min' => PedidoItem::query()->where('status', StatusItemPedido::Enviado)->where('enviado_em', '<', now()->subMinutes(15))->count(),
                'Entregues hoje' => PedidoItem::query()->where('status', StatusItemPedido::Entregue)->whereDate('entregue_em', today())->count(),
            ]);
        }

        if ($papel->podeReceberPagamento()) {
            $pagamentos = Pagamento::query()->whereNull('estornado_em')->whereDate('pago_em', today())
                ->selectRaw('COUNT(*) as quantidade, COALESCE(SUM(valor_centavos), 0) as total')->toBase()->first();
            $recebido = (int) $pagamentos->total;
            $secoes[] = $this->secao('caixa', 'Conta e caixa', 'Pagamentos recebidos hoje, sem os estornados.', 'caixas.index', [
                'Situação do caixa' => Caixa::query()->where('aberto', true)->exists() ? 'Aberto' : 'Fechado — abra para receber',
                'Recebido hoje' => $this->dinheiro($recebido),
                'Pagamentos hoje' => (int) $pagamentos->quantidade,
                'Comandas a receber' => $comandas->filter(fn (Comanda $comanda): bool => $calcularConta->handle($comanda)['saldo'] > 0)->count(),
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
            $usaSqlite = config('database.default') === 'sqlite';
            $ultimo = $usaSqlite ? $consultarBackup->handle() : null;
            $data = $ultimo ? filemtime($ultimo) : false;
            $secoes[] = $this->secao('infraestrutura', 'Infraestrutura', 'Fila e backups locais. Consulte o monitoramento para detalhes.', 'monitoramento.show', [
                'Trabalhos com falha' => DB::table('failed_jobs')->count(),
                'Último backup local' => ! $usaSqlite ? 'Não se aplica — backup externo não verificado' : ($data !== false ? date('d/m/Y H:i', $data) : 'Nenhum backup local válido encontrado'),
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
