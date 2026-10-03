import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Minus, Plus, ShoppingCart, Users } from 'lucide-react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as registrarPedido } from '@/routes/comandas/pedidos';
import { index as salao } from '@/routes/salao';

type Produto = {
    id: number;
    nome: string;
    descricao: string | null;
    preco_centavos: number;
};
type Categoria = { id: number; nome: string; itens: Produto[] };
type ItemPedido = {
    id: number;
    nome_item: string;
    quantidade: number;
    preco_unitario_centavos: number;
    observacao: string | null;
};
type Pedido = {
    id: number;
    status: string;
    criado_por: { name: string };
    itens: ItemPedido[];
};
type Props = {
    comanda: {
        id: number;
        quantidade_pessoas: number;
        mesa: { numero: string };
    };
    categorias: Categoria[];
    pedidos: Pedido[];
};
type ItemForm = {
    item_cardapio_id: number;
    quantidade: number;
    observacao: string;
};
const moeda = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

export default function ComandaShow({ comanda, categorias, pedidos }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm<{
        observacao: string;
        itens: ItemForm[];
    }>({ observacao: '', itens: [] });
    const produtos = categorias.flatMap((categoria) => categoria.itens);
    const total = data.itens.reduce(
        (soma, item) =>
            soma +
            (produtos.find((produto) => produto.id === item.item_cardapio_id)
                ?.preco_centavos ?? 0) *
                item.quantidade,
        0,
    );

    function mudarQuantidade(produtoId: number, diferenca: number) {
        const atual = data.itens.find(
            (item) => item.item_cardapio_id === produtoId,
        );
        const nova = (atual?.quantidade ?? 0) + diferenca;
        if (nova <= 0)
            setData(
                'itens',
                data.itens.filter(
                    (item) => item.item_cardapio_id !== produtoId,
                ),
            );
        else if (atual)
            setData(
                'itens',
                data.itens.map((item) =>
                    item.item_cardapio_id === produtoId
                        ? { ...item, quantidade: nova }
                        : item,
                ),
            );
        else
            setData('itens', [
                ...data.itens,
                { item_cardapio_id: produtoId, quantidade: 1, observacao: '' },
            ]);
    }

    function enviar(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post(registrarPedido.url(comanda.id), {
            preserveScroll: true,
            onSuccess: () => reset(),
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
                                    {categoria.itens.map((produto) => {
                                        const item = data.itens.find(
                                            (selecionado) =>
                                                selecionado.item_cardapio_id ===
                                                produto.id,
                                        );
                                        return (
                                            <Card
                                                key={produto.id}
                                                className="gap-4 py-4"
                                            >
                                                <CardHeader className="px-4">
                                                    <div className="flex justify-between gap-4">
                                                        <div>
                                                            <CardTitle>
                                                                {produto.nome}
                                                            </CardTitle>
                                                            <CardDescription>
                                                                {
                                                                    produto.descricao
                                                                }
                                                            </CardDescription>
                                                        </div>
                                                        <strong>
                                                            {moeda.format(
                                                                produto.preco_centavos /
                                                                    100,
                                                            )}
                                                        </strong>
                                                    </div>
                                                </CardHeader>
                                                <CardContent className="grid gap-3 px-4">
                                                    <div className="flex items-center justify-between">
                                                        <span className="text-sm text-muted-foreground">
                                                            Quantidade
                                                        </span>
                                                        <div className="flex items-center gap-2">
                                                            <Button
                                                                type="button"
                                                                variant="outline"
                                                                size="icon"
                                                                disabled={!item}
                                                                onClick={() =>
                                                                    mudarQuantidade(
                                                                        produto.id,
                                                                        -1,
                                                                    )
                                                                }
                                                            >
                                                                <Minus />
                                                            </Button>
                                                            <strong className="w-6 text-center">
                                                                {item?.quantidade ??
                                                                    0}
                                                            </strong>
                                                            <Button
                                                                type="button"
                                                                size="icon"
                                                                onClick={() =>
                                                                    mudarQuantidade(
                                                                        produto.id,
                                                                        1,
                                                                    )
                                                                }
                                                            >
                                                                <Plus />
                                                            </Button>
                                                        </div>
                                                    </div>
                                                    {item && (
                                                        <Input
                                                            value={
                                                                item.observacao
                                                            }
                                                            placeholder="Ex.: sem cebola"
                                                            onChange={(event) =>
                                                                setData(
                                                                    'itens',
                                                                    data.itens.map(
                                                                        (
                                                                            linha,
                                                                        ) =>
                                                                            linha.item_cardapio_id ===
                                                                            produto.id
                                                                                ? {
                                                                                      ...linha,
                                                                                      observacao:
                                                                                          event
                                                                                              .target
                                                                                              .value,
                                                                                  }
                                                                                : linha,
                                                                    ),
                                                                )
                                                            }
                                                        />
                                                    )}
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </section>
                    <aside>
                        <Card className="sticky top-4">
                            <CardHeader>
                                <CardTitle className="flex gap-2">
                                    <ShoppingCart /> Novo pedido
                                </CardTitle>
                                <CardDescription>
                                    Revise itens e valores.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form className="grid gap-4" onSubmit={enviar}>
                                    {data.itens.length === 0 ? (
                                        <p className="rounded-lg border border-dashed p-4 text-center text-sm text-muted-foreground">
                                            Selecione itens no cardápio.
                                        </p>
                                    ) : (
                                        <div className="grid gap-3">
                                            {data.itens.map((item) => {
                                                const produto = produtos.find(
                                                    (p) =>
                                                        p.id ===
                                                        item.item_cardapio_id,
                                                );
                                                return (
                                                    produto && (
                                                        <div
                                                            key={produto.id}
                                                            className="flex justify-between gap-3 text-sm"
                                                        >
                                                            <span>
                                                                {
                                                                    item.quantidade
                                                                }
                                                                × {produto.nome}
                                                            </span>
                                                            <strong>
                                                                {moeda.format(
                                                                    (produto.preco_centavos *
                                                                        item.quantidade) /
                                                                        100,
                                                                )}
                                                            </strong>
                                                        </div>
                                                    )
                                                );
                                            })}
                                        </div>
                                    )}
                                    <div className="grid gap-2">
                                        <Label htmlFor="observacao">
                                            Observação geral
                                        </Label>
                                        <textarea
                                            id="observacao"
                                            rows={3}
                                            value={data.observacao}
                                            onChange={(event) =>
                                                setData(
                                                    'observacao',
                                                    event.target.value,
                                                )
                                            }
                                            className="resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-3 focus-visible:ring-ring/50"
                                        />
                                    </div>
                                    <InputError
                                        message={errors.itens ?? errors.comanda}
                                    />
                                    <div className="flex justify-between border-t pt-4 font-semibold">
                                        <span>Total</span>
                                        <span>{moeda.format(total / 100)}</span>
                                    </div>
                                    <Button
                                        disabled={
                                            processing || !data.itens.length
                                        }
                                    >
                                        Registrar pedido
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </aside>
                </div>

                <section className="grid gap-3">
                    <h2 className="text-lg font-semibold">
                        Pedidos da comanda
                    </h2>
                    {pedidos.length === 0 ? (
                        <Card className="border-dashed py-8 text-center">
                            <CardContent className="text-muted-foreground">
                                Nenhum pedido registrado.
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-3 lg:grid-cols-2">
                            {pedidos.map((pedido) => (
                                <PedidoCard key={pedido.id} pedido={pedido} />
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

function PedidoCard({ pedido }: { pedido: Pedido }) {
    const total = pedido.itens.reduce(
        (soma, item) => soma + item.quantidade * item.preco_unitario_centavos,
        0,
    );
    return (
        <Card className="gap-4 py-4">
            <CardHeader className="px-4">
                <div className="flex justify-between">
                    <CardTitle>Pedido #{pedido.id}</CardTitle>
                    <Badge variant="outline">{pedido.status}</Badge>
                </div>
                <CardDescription>
                    Registrado por {pedido.criado_por.name}
                </CardDescription>
            </CardHeader>
            <CardContent className="grid gap-3 px-4">
                {pedido.itens.map((item) => (
                    <div key={item.id} className="grid gap-1 border-b pb-3">
                        <div className="flex justify-between text-sm">
                            <span>
                                {item.quantidade}× {item.nome_item}
                            </span>
                            <strong>
                                {moeda.format(
                                    (item.quantidade *
                                        item.preco_unitario_centavos) /
                                        100,
                                )}
                            </strong>
                        </div>
                        {item.observacao && (
                            <p className="text-xs text-muted-foreground">
                                {item.observacao}
                            </p>
                        )}
                    </div>
                ))}
                <div className="flex justify-between font-semibold">
                    <span>Total</span>
                    <span>{moeda.format(total / 100)}</span>
                </div>
            </CardContent>
        </Card>
    );
}

ComandaShow.layout = {
    breadcrumbs: [
        { title: 'Salão e mesas', href: salao() },
        { title: 'Comanda' },
    ],
};
