import { Form, Head, router } from '@inertiajs/react';
import {
    Armchair,
    CircleDollarSign,
    Plus,
    Users,
    Utensils,
} from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { show as verComanda } from '@/routes/comandas';
import { store as abrirComanda } from '@/routes/comandas';
import { store as cadastrarMesa } from '@/routes/mesas';
import { index as salao } from '@/routes/salao';
import { store as cadastrarSetor } from '@/routes/setores-salao';

type EstadoMesa = 'livre' | 'ocupada' | 'reservada' | 'aguardando_pagamento';

type Comanda = {
    id: number;
    quantidade_pessoas: number;
    aberta_em: string;
};

type Mesa = {
    id: number;
    numero: string;
    capacidade: number;
    estado: EstadoMesa;
    comanda_ativa: Comanda | null;
};

type Setor = {
    id: number;
    nome: string;
    descricao: string | null;
    mesas: Mesa[];
};

type Props = {
    setores: Setor[];
    permissoes: {
        gerenciarSalao: boolean;
        abrirComanda: boolean;
    };
};

const estilosEstado: Record<
    EstadoMesa,
    { rotulo: string; borda: string; indicador: string }
> = {
    livre: {
        rotulo: 'Livre',
        borda: 'border-emerald-200 bg-emerald-50/70 hover:border-emerald-400 dark:border-emerald-900 dark:bg-emerald-950/30',
        indicador: 'bg-emerald-500',
    },
    ocupada: {
        rotulo: 'Ocupada',
        borda: 'border-amber-200 bg-amber-50/70 dark:border-amber-900 dark:bg-amber-950/30',
        indicador: 'bg-amber-500',
    },
    reservada: {
        rotulo: 'Reservada',
        borda: 'border-sky-200 bg-sky-50/70 dark:border-sky-900 dark:bg-sky-950/30',
        indicador: 'bg-sky-500',
    },
    aguardando_pagamento: {
        rotulo: 'Aguardando pagamento',
        borda: 'border-violet-200 bg-violet-50/70 dark:border-violet-900 dark:bg-violet-950/30',
        indicador: 'bg-violet-500',
    },
};

export default function SalaoIndex({ setores, permissoes }: Props) {
    const [mesaSelecionada, setMesaSelecionada] = useState<Mesa | null>(null);
    const totalMesas = setores.reduce(
        (total, setor) => total + setor.mesas.length,
        0,
    );
    const mesasLivres = setores.reduce(
        (total, setor) =>
            total +
            setor.mesas.filter((mesa) => mesa.estado === 'livre').length,
        0,
    );

    return (
        <>
            <Head title="Salão e mesas" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="grid gap-1">
                        <p className="text-sm font-medium text-primary">
                            Operação do salão
                        </p>
                        <h1 className="text-2xl font-semibold tracking-tight md:text-3xl">
                            Mesas em tempo real
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Acompanhe a ocupação e inicie atendimentos sem
                            perder o contexto.
                        </p>
                    </div>

                    {permissoes.gerenciarSalao && (
                        <div className="flex flex-wrap gap-2">
                            <NovoSetor />
                            <NovaMesa setores={setores} />
                        </div>
                    )}
                </header>

                <div className="grid gap-3 sm:grid-cols-3">
                    <Resumo
                        titulo="Mesas"
                        valor={totalMesas}
                        icone={<Armchair className="size-4" />}
                    />
                    <Resumo
                        titulo="Livres"
                        valor={mesasLivres}
                        icone={<Utensils className="size-4" />}
                    />
                    <Resumo
                        titulo="Em atendimento"
                        valor={totalMesas - mesasLivres}
                        icone={<CircleDollarSign className="size-4" />}
                    />
                </div>

                {setores.length === 0 ? (
                    <Card className="border-dashed py-12 text-center">
                        <CardContent className="grid justify-items-center gap-3">
                            <div className="rounded-full bg-muted p-3">
                                <Utensils className="size-6 text-muted-foreground" />
                            </div>
                            <div>
                                <p className="font-medium">
                                    O salão ainda não foi configurado
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Cadastre um setor e depois adicione as
                                    mesas.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-6">
                        {setores.map((setor) => (
                            <section key={setor.id} className="grid gap-3">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <h2 className="text-lg font-semibold">
                                            {setor.nome}
                                        </h2>
                                        {setor.descricao && (
                                            <p className="text-sm text-muted-foreground">
                                                {setor.descricao}
                                            </p>
                                        )}
                                    </div>
                                    <Badge variant="secondary">
                                        {setor.mesas.length} mesas
                                    </Badge>
                                </div>

                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                    {setor.mesas.map((mesa) => {
                                        const estilo =
                                            estilosEstado[mesa.estado];
                                        const podeAcessar =
                                            permissoes.abrirComanda &&
                                            (mesa.estado === 'livre' ||
                                                (mesa.estado === 'ocupada' &&
                                                    mesa.comanda_ativa !==
                                                        null));

                                        return (
                                            <button
                                                key={mesa.id}
                                                type="button"
                                                disabled={!podeAcessar}
                                                onClick={() => {
                                                    if (
                                                        mesa.estado === 'livre'
                                                    ) {
                                                        setMesaSelecionada(
                                                            mesa,
                                                        );
                                                    } else if (
                                                        mesa.comanda_ativa
                                                    ) {
                                                        router.visit(
                                                            verComanda(
                                                                mesa
                                                                    .comanda_ativa
                                                                    .id,
                                                            ).url,
                                                        );
                                                    }
                                                }}
                                                className={`group rounded-xl border p-4 text-left transition ${estilo.borda} disabled:cursor-default`}
                                            >
                                                <div className="flex items-start justify-between gap-3">
                                                    <div className="grid gap-1">
                                                        <span className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                                            Mesa
                                                        </span>
                                                        <strong className="text-2xl">
                                                            {mesa.numero}
                                                        </strong>
                                                    </div>
                                                    <span
                                                        className={`mt-1 size-2.5 rounded-full ${estilo.indicador}`}
                                                    />
                                                </div>
                                                <div className="mt-5 flex items-center justify-between gap-2 text-sm">
                                                    <span className="flex items-center gap-1.5 text-muted-foreground">
                                                        <Users className="size-4" />
                                                        {mesa.capacidade}{' '}
                                                        lugares
                                                    </span>
                                                    <span className="font-medium">
                                                        {estilo.rotulo}
                                                    </span>
                                                </div>
                                                {mesa.comanda_ativa && (
                                                    <p className="mt-2 text-xs text-muted-foreground">
                                                        Comanda #
                                                        {mesa.comanda_ativa.id}{' '}
                                                        ·{' '}
                                                        {
                                                            mesa.comanda_ativa
                                                                .quantidade_pessoas
                                                        }{' '}
                                                        pessoas
                                                    </p>
                                                )}
                                            </button>
                                        );
                                    })}
                                </div>
                            </section>
                        ))}
                    </div>
                )}
            </div>

            <AbrirMesaDialog
                mesa={mesaSelecionada}
                onClose={() => setMesaSelecionada(null)}
            />
        </>
    );
}

function Resumo({
    titulo,
    valor,
    icone,
}: {
    titulo: string;
    valor: number;
    icone: React.ReactNode;
}) {
    return (
        <Card className="py-4">
            <CardContent className="flex items-center justify-between px-4">
                <div>
                    <p className="text-sm text-muted-foreground">{titulo}</p>
                    <p className="text-2xl font-semibold">{valor}</p>
                </div>
                <div className="rounded-lg bg-primary/10 p-2.5 text-primary">
                    {icone}
                </div>
            </CardContent>
        </Card>
    );
}

function NovoSetor() {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="outline">
                    <Plus className="size-4" /> Novo setor
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Novo setor</DialogTitle>
                    <DialogDescription>
                        Organize as mesas por ambientes do restaurante.
                    </DialogDescription>
                </DialogHeader>
                <Form {...cadastrarSetor.form()} className="grid gap-4">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="setor-nome">Nome</Label>
                                <Input
                                    id="setor-nome"
                                    name="nome"
                                    placeholder="Ex.: Salão principal"
                                    required
                                />
                                <InputError message={errors.nome} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="setor-descricao">
                                    Descrição
                                </Label>
                                <Input
                                    id="setor-descricao"
                                    name="descricao"
                                    placeholder="Opcional"
                                />
                                <InputError message={errors.descricao} />
                            </div>
                            <Button disabled={processing}>
                                Cadastrar setor
                            </Button>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function NovaMesa({ setores }: { setores: Setor[] }) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button disabled={setores.length === 0}>
                    <Plus className="size-4" /> Nova mesa
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Nova mesa</DialogTitle>
                    <DialogDescription>
                        Informe onde a mesa fica e quantas pessoas acomoda.
                    </DialogDescription>
                </DialogHeader>
                <Form {...cadastrarMesa.form()} className="grid gap-4">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="mesa-setor">Setor</Label>
                                <Select name="setor_salao_id" required>
                                    <SelectTrigger id="mesa-setor">
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {setores.map((setor) => (
                                            <SelectItem
                                                key={setor.id}
                                                value={String(setor.id)}
                                            >
                                                {setor.nome}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.setor_salao_id} />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="mesa-numero">
                                        Identificação
                                    </Label>
                                    <Input
                                        id="mesa-numero"
                                        name="numero"
                                        placeholder="Ex.: 01"
                                        required
                                    />
                                    <InputError message={errors.numero} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="mesa-capacidade">
                                        Capacidade
                                    </Label>
                                    <Input
                                        id="mesa-capacidade"
                                        name="capacidade"
                                        type="number"
                                        min="1"
                                        max="99"
                                        defaultValue="4"
                                        required
                                    />
                                    <InputError message={errors.capacidade} />
                                </div>
                            </div>
                            <Button disabled={processing}>
                                Cadastrar mesa
                            </Button>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function AbrirMesaDialog({
    mesa,
    onClose,
}: {
    mesa: Mesa | null;
    onClose: () => void;
}) {
    return (
        <Dialog
            open={mesa !== null}
            onOpenChange={(aberto) => !aberto && onClose()}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Abrir mesa {mesa?.numero}</DialogTitle>
                    <DialogDescription>
                        Informe quantas pessoas participarão desta comanda.
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...abrirComanda.form()}
                    className="grid gap-4"
                    onSuccess={onClose}
                >
                    {({ processing, errors }) => (
                        <>
                            <input
                                type="hidden"
                                name="mesa_id"
                                value={mesa?.id ?? ''}
                            />
                            <div className="grid gap-2">
                                <Label htmlFor="quantidade-pessoas">
                                    Quantidade de pessoas
                                </Label>
                                <Input
                                    id="quantidade-pessoas"
                                    name="quantidade_pessoas"
                                    type="number"
                                    min="1"
                                    max="99"
                                    defaultValue="2"
                                    required
                                />
                                <InputError
                                    message={
                                        errors.quantidade_pessoas ??
                                        errors.mesa_id
                                    }
                                />
                            </div>
                            <Button disabled={processing}>
                                Iniciar atendimento
                            </Button>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

SalaoIndex.layout = {
    breadcrumbs: [
        {
            title: 'Salão e mesas',
            href: salao(),
        },
    ],
};
