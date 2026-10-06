import { Head, router } from '@inertiajs/react';
import {
    AlertTriangle,
    ChefHat,
    Clock3,
    Printer,
    UserRound,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index as producaoIndex } from '@/routes/producao';
import { store as solicitarImpressao } from '@/routes/producao/impressoes';
import { update as atualizarStatus } from '@/routes/pedido-itens/status';

type Status = 'enviado' | 'em_preparo' | 'pronto' | 'entregue';

type Setor = { id: number; nome: string };

type ItemProducao = {
    id: number;
    pedido_id: number;
    mesa: string;
    nome_item: string;
    quantidade: number;
    observacao: string | null;
    observacao_pedido: string | null;
    status: Status;
    enviado_em: string | null;
    iniciado_em: string | null;
    pronto_em: string | null;
    entregue_em: string | null;
    responsavel: string | null;
    atrasado: boolean;
};

const colunas: {
    status: Status;
    titulo: string;
    proximo?: Status;
    acao?: string;
}[] = [
    {
        status: 'enviado',
        titulo: 'Recebido',
        proximo: 'em_preparo',
        acao: 'Iniciar preparo',
    },
    {
        status: 'em_preparo',
        titulo: 'Em preparo',
        proximo: 'pronto',
        acao: 'Marcar pronto',
    },
    {
        status: 'pronto',
        titulo: 'Pronto',
        proximo: 'entregue',
        acao: 'Marcar entregue',
    },
    { status: 'entregue', titulo: 'Entregue' },
];

export default function PainelProducao({
    setores,
    setorSelecionado,
    itens,
}: {
    setores: Setor[];
    setorSelecionado: number;
    itens: ItemProducao[];
}) {
    const setor = setores.find((item) => item.id === setorSelecionado);
    const pedidos = useMemo(
        () => [...new Set(itens.map((item) => item.pedido_id))],
        [itens],
    );

    useEffect(() => {
        const intervalo = window.setInterval(
            () => router.reload({ only: ['itens'] }),
            30_000,
        );
        return () => window.clearInterval(intervalo);
    }, []);

    return (
        <>
            <Head title="Painel de produção" />
            <div className="flex flex-1 flex-col gap-5 p-4 md:p-6 print:p-0">
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between print:hidden">
                    <div>
                        <p className="text-sm font-medium text-primary">
                            Operação
                        </p>
                        <h1 className="text-3xl font-semibold">
                            Painel de produção
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Atualização automática a cada 30 segundos. Pedidos
                            recebidos há mais de 15 minutos são destacados.
                        </p>
                    </div>
                    <Button variant="outline" onClick={() => window.print()}>
                        <Printer /> Imprimir painel
                    </Button>
                </header>

                <nav className="flex flex-wrap gap-2 print:hidden">
                    {setores.map((item) => (
                        <Button
                            key={item.id}
                            variant={
                                item.id === setorSelecionado
                                    ? 'default'
                                    : 'outline'
                            }
                            onClick={() =>
                                router.get(
                                    producaoIndex.url(),
                                    { setor: item.id },
                                    { preserveState: true },
                                )
                            }
                        >
                            <ChefHat /> {item.nome}
                        </Button>
                    ))}
                </nav>

                {setores.length === 0 ? (
                    <Card className="border-dashed">
                        <CardContent className="py-12 text-center text-muted-foreground">
                            Cadastre e ative um setor de produção para começar.
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <div className="hidden print:block">
                            <h1 className="text-2xl font-bold">
                                Produção — {setor?.nome}
                            </h1>
                            <p>
                                Emitido em {new Date().toLocaleString('pt-BR')}
                            </p>
                        </div>
                        <div className="grid gap-4 xl:grid-cols-4">
                            {colunas.map((coluna) => (
                                <Coluna
                                    key={coluna.status}
                                    coluna={coluna}
                                    itens={itens.filter(
                                        (item) => item.status === coluna.status,
                                    )}
                                    setorId={setorSelecionado}
                                />
                            ))}
                        </div>
                        <div className="flex flex-wrap gap-2 print:hidden">
                            {pedidos.map((pedidoId) => (
                                <BotaoReimpressao
                                    key={pedidoId}
                                    pedidoId={pedidoId}
                                    setorId={setorSelecionado}
                                />
                            ))}
                        </div>
                    </>
                )}
            </div>
        </>
    );
}

function Coluna({
    coluna,
    itens,
    setorId,
}: {
    coluna: (typeof colunas)[number];
    itens: ItemProducao[];
    setorId: number;
}) {
    return (
        <section className="grid content-start gap-3 rounded-xl bg-muted/40 p-3">
            <div className="flex items-center justify-between">
                <h2 className="font-semibold">{coluna.titulo}</h2>
                <Badge variant="secondary">{itens.length}</Badge>
            </div>
            {itens.length === 0 ? (
                <p className="rounded-lg border border-dashed p-5 text-center text-sm text-muted-foreground">
                    Nenhum item
                </p>
            ) : (
                itens.map((item) => (
                    <ItemCard
                        key={item.id}
                        item={item}
                        proximo={coluna.proximo}
                        acao={coluna.acao}
                        setorId={setorId}
                    />
                ))
            )}
        </section>
    );
}

function ItemCard({
    item,
    proximo,
    acao,
}: {
    item: ItemProducao;
    proximo?: Status;
    acao?: string;
    setorId: number;
}) {
    const horario =
        item.entregue_em ??
        item.pronto_em ??
        item.iniciado_em ??
        item.enviado_em;
    return (
        <Card
            className={
                item.atrasado ? 'border-destructive bg-destructive/5' : ''
            }
        >
            <CardHeader className="pb-2">
                <div className="flex items-start justify-between gap-2">
                    <CardTitle className="text-base">
                        {item.quantidade}× {item.nome_item}
                    </CardTitle>
                    <Badge variant="outline">Mesa {item.mesa}</Badge>
                </div>
                <p className="text-xs text-muted-foreground">
                    Pedido #{item.pedido_id}
                </p>
            </CardHeader>
            <CardContent className="grid gap-3 text-sm">
                {item.atrasado && (
                    <p className="flex items-center gap-1 font-medium text-destructive">
                        <AlertTriangle className="size-4" /> Pedido atrasado
                    </p>
                )}
                {item.observacao && (
                    <p className="rounded-md bg-amber-500/10 p-2">
                        <strong>Item:</strong> {item.observacao}
                    </p>
                )}
                {item.observacao_pedido && (
                    <p className="rounded-md bg-muted p-2">
                        <strong>Pedido:</strong> {item.observacao_pedido}
                    </p>
                )}
                <div className="grid gap-1 text-xs text-muted-foreground">
                    {horario && (
                        <span className="flex items-center gap-1">
                            <Clock3 className="size-3" />{' '}
                            {new Date(horario).toLocaleString('pt-BR')}
                        </span>
                    )}
                    {item.responsavel && (
                        <span className="flex items-center gap-1">
                            <UserRound className="size-3" /> {item.responsavel}
                        </span>
                    )}
                </div>
                {proximo && (
                    <Button
                        className="print:hidden"
                        size="sm"
                        onClick={() =>
                            router.patch(
                                atualizarStatus.url(item.id),
                                { status: proximo },
                                { preserveScroll: true },
                            )
                        }
                    >
                        {acao}
                    </Button>
                )}
            </CardContent>
        </Card>
    );
}

function BotaoReimpressao({
    pedidoId,
    setorId,
}: {
    pedidoId: number;
    setorId: number;
}) {
    const [processando, setProcessando] = useState(false);
    const [chave, setChave] = useState(() => crypto.randomUUID());

    function reimprimir() {
        setProcessando(true);
        router.post(
            solicitarImpressao.url({
                pedido: pedidoId,
                setor_producao: setorId,
            }),
            { chave_idempotencia: chave },
            {
                preserveScroll: true,
                onSuccess: () => {
                    window.print();
                    setChave(crypto.randomUUID());
                },
                onFinish: () => setProcessando(false),
            },
        );
    }

    return (
        <Button
            variant="outline"
            size="sm"
            disabled={processando}
            onClick={reimprimir}
        >
            <Printer /> Reimprimir pedido #{pedidoId}
        </Button>
    );
}

PainelProducao.layout = {
    breadcrumbs: [{ title: 'Produção', href: producaoIndex() }],
};
