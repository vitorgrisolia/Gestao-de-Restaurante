import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Users } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { precoPorPorcao, type DadosVenda } from '@/lib/venda';
import { store as registrarPedido } from '@/routes/comandas/pedidos';
import { index as salao } from '@/routes/salao';
import { FechamentoCard } from './components/fechamento-card';
import { HistoricoPedidos } from './components/historico-pedidos';
import {
    NovoPedidoCard,
    type NovoPedidoForm,
} from './components/novo-pedido-card';
import { ProdutoCard } from './components/produto-card';
import type { ComandaPageProps } from './types';

export default function ComandaShow({
    comanda,
    categorias,
    pedidos,
    totalComandaCentavos,
    podeFecharComanda,
    formasPagamento,
    conta,
}: ComandaPageProps) {
    const formulario = useForm<NovoPedidoForm>({
        comanda: '',
        observacao: '',
        itens: [],
    });
    const produtos = categorias.flatMap((categoria) => categoria.itens);
    const totalNovoPedidoCentavos = formulario.data.itens.reduce(
        (total, item) => {
            const produto = produtos.find(
                ({ id }) => id === item.item_cardapio_id,
            );

            return (
                total +
                (produto
                    ? precoPorPorcao(
                          produto.tipo_venda,
                          produto.preco_centavos,
                          item,
                      )
                    : 0) *
                    item.quantidade
            );
        },
        0,
    );

    function mudarQuantidade(produtoId: number, diferenca: number) {
        const itemAtual = formulario.data.itens.find(
            (item) => item.item_cardapio_id === produtoId,
        );
        const novaQuantidade = (itemAtual?.quantidade ?? 0) + diferenca;

        if (novaQuantidade <= 0) {
            formulario.setData(
                'itens',
                formulario.data.itens.filter(
                    (item) => item.item_cardapio_id !== produtoId,
                ),
            );
            return;
        }

        if (itemAtual) {
            formulario.setData(
                'itens',
                formulario.data.itens.map((item) =>
                    item.item_cardapio_id === produtoId
                        ? { ...item, quantidade: novaQuantidade }
                        : item,
                ),
            );
            return;
        }

        formulario.setData('itens', [
            ...formulario.data.itens,
            {
                item_cardapio_id: produtoId,
                quantidade: 1,
                observacao: '',
                peso_gramas: '',
                cobrar_excesso_carne: produtos.find(
                    (produto) => produto.id === produtoId,
                )?.permite_excesso_carne
                    ? null
                    : false,
                adicional_carne: '',
            },
        ]);
    }

    function mudarObservacao(produtoId: number, observacao: string) {
        formulario.setData(
            'itens',
            formulario.data.itens.map((item) =>
                item.item_cardapio_id === produtoId
                    ? { ...item, observacao }
                    : item,
            ),
        );
    }

    function mudarVenda(produtoId: number, dados: Partial<DadosVenda>) {
        formulario.setData(
            'itens',
            formulario.data.itens.map((item) =>
                item.item_cardapio_id === produtoId
                    ? { ...item, ...dados }
                    : item,
            ),
        );
    }

    function registrarNovoPedido(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        formulario.post(registrarPedido.url(comanda.id), {
            preserveScroll: true,
            onSuccess: () => formulario.reset(),
        });
    }

    return (
        <>
            <Head title={`Comanda da mesa ${comanda.mesa.numero}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="grid gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            asChild
                            className="w-fit px-0"
                        >
                            <Link href={salao()}>
                                <ArrowLeft /> Voltar ao salão
                            </Link>
                        </Button>
                        <div>
                            <p className="text-sm font-medium text-primary">
                                Comanda #{comanda.id}
                            </p>
                            <h1 className="text-3xl font-semibold">
                                Mesa {comanda.mesa.numero}
                            </h1>
                        </div>
                    </div>
                    <Badge
                        variant="secondary"
                        className="w-fit gap-2 px-3 py-1.5"
                    >
                        <Users /> {comanda.quantidade_pessoas} pessoas
                    </Badge>
                </header>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
                    <section className="grid content-start gap-6">
                        {categorias.map((categoria) => (
                            <div key={categoria.id} className="grid gap-3">
                                <h2 className="text-lg font-semibold">
                                    {categoria.nome}
                                </h2>
                                <div className="grid gap-3 md:grid-cols-2">
                                    {categoria.itens.map((produto) => (
                                        <ProdutoCard
                                            key={produto.id}
                                            produto={produto}
                                            itemSelecionado={formulario.data.itens.find(
                                                (item) =>
                                                    item.item_cardapio_id ===
                                                    produto.id,
                                            )}
                                            onMudarQuantidade={mudarQuantidade}
                                            onMudarObservacao={mudarObservacao}
                                            onMudarVenda={mudarVenda}
                                        />
                                    ))}
                                </div>
                            </div>
                        ))}
                    </section>
                    <aside className="grid content-start gap-4">
                        <NovoPedidoCard
                            formulario={formulario}
                            produtos={produtos}
                            totalCentavos={totalNovoPedidoCentavos}
                            onSubmit={registrarNovoPedido}
                        />
                        <FechamentoCard
                            comandaId={comanda.id}
                            quantidadePessoas={comanda.quantidade_pessoas}
                            quantidadePedidos={pedidos.length}
                            totalCentavos={totalComandaCentavos}
                            podeFechar={podeFecharComanda}
                            formasPagamento={formasPagamento}
                            conta={conta}
                            itens={pedidos.flatMap((pedido) => pedido.itens)}
                        />
                    </aside>
                </div>

                <HistoricoPedidos pedidos={pedidos} />
            </div>
        </>
    );
}

ComandaShow.layout = {
    breadcrumbs: [
        { title: 'Salão e mesas', href: salao() },
        { title: 'Comanda' },
    ],
};
