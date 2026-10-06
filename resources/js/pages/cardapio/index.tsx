import { Form, Head } from '@inertiajs/react';
import { CheckCircle2, PackagePlus, Tags, Utensils } from 'lucide-react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store as cadastrarCategoria } from '@/routes/categorias-cardapio';
import { index as cardapio } from '@/routes/cardapio';
import { store as cadastrarItem } from '@/routes/itens-cardapio';
import { AcoesCategoria, AcoesItem } from './components/crud-actions';

export type ItemCardapio = {
    id: number;
    setor_producao_id: number | null;
    nome: string;
    descricao: string | null;
    preco_centavos: number;
    imagem: string | null;
    disponivel: boolean;
    ordem: number;
};

export type SetorProducao = { id: number; nome: string; ativo: boolean };

export type Categoria = {
    id: number;
    nome: string;
    descricao: string | null;
    ativa: boolean;
    ordem: number;
    itens: ItemCardapio[];
};

type Props = {
    categorias: Categoria[];
    setoresProducao: SetorProducao[];
    resumo: {
        categorias: number;
        itens: number;
        disponiveis: number;
    };
};

const moeda = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

export default function CardapioIndex({
    categorias,
    setoresProducao,
    resumo,
}: Props) {
    return (
        <>
            <Head title="Cadastro do cardápio" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="grid gap-1">
                    <p className="text-sm font-medium text-primary">
                        Administração
                    </p>
                    <h1 className="text-2xl font-semibold tracking-tight md:text-3xl">
                        Cadastro do cardápio
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Organize categorias, produtos, preços e disponibilidade.
                    </p>
                </header>

                <div className="grid gap-3 sm:grid-cols-3">
                    <Resumo titulo="Categorias" valor={resumo.categorias} />
                    <Resumo titulo="Itens" valor={resumo.itens} />
                    <Resumo titulo="Disponíveis" valor={resumo.disponiveis} />
                </div>

                <div className="grid items-start gap-6 xl:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <PackagePlus /> Novo item
                            </CardTitle>
                            <CardDescription>
                                Cadastre um produto que poderá ser adicionado
                                aos pedidos.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {categorias.length === 0 ? (
                                <p className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                                    Cadastre uma categoria antes de criar itens.
                                </p>
                            ) : (
                                <Form
                                    {...cadastrarItem.form()}
                                    resetOnSuccess
                                    className="grid gap-4"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <Label htmlFor="item-categoria">
                                                    Categoria
                                                </Label>
                                                <Select
                                                    name="categoria_cardapio_id"
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="item-categoria"
                                                        className="w-full"
                                                    >
                                                        <SelectValue placeholder="Selecione" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {categorias.map(
                                                            (categoria) => (
                                                                <SelectItem
                                                                    key={
                                                                        categoria.id
                                                                    }
                                                                    value={String(
                                                                        categoria.id,
                                                                    )}
                                                                >
                                                                    {
                                                                        categoria.nome
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={
                                                        errors.categoria_cardapio_id
                                                    }
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="item-setor">
                                                    Setor de produção
                                                </Label>
                                                <Select
                                                    name="setor_producao_id"
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="item-setor"
                                                        className="w-full"
                                                    >
                                                        <SelectValue placeholder="Selecione" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {setoresProducao.map(
                                                            (setor) => (
                                                                <SelectItem
                                                                    key={
                                                                        setor.id
                                                                    }
                                                                    value={String(
                                                                        setor.id,
                                                                    )}
                                                                >
                                                                    {setor.nome}
                                                                    {setor.ativo
                                                                        ? ''
                                                                        : ' (inativo)'}
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={
                                                        errors.setor_producao_id
                                                    }
                                                />
                                            </div>

                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <Campo
                                                    id="item-nome"
                                                    name="nome"
                                                    label="Nome"
                                                    placeholder="Ex.: Lasanha"
                                                    error={errors.nome}
                                                />
                                                <Campo
                                                    id="item-preco"
                                                    name="preco"
                                                    label="Preço (R$)"
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                    placeholder="32.90"
                                                    error={errors.preco}
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="item-descricao">
                                                    Descrição
                                                </Label>
                                                <textarea
                                                    id="item-descricao"
                                                    name="descricao"
                                                    rows={3}
                                                    className="resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-3 focus-visible:ring-ring/50"
                                                    placeholder="Descreva ingredientes ou apresentação"
                                                />
                                                <InputError
                                                    message={errors.descricao}
                                                />
                                            </div>

                                            <Campo
                                                id="item-imagem"
                                                name="imagem"
                                                label="URL da imagem (opcional)"
                                                type="url"
                                                placeholder="https://..."
                                                error={errors.imagem}
                                                required={false}
                                            />

                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <div className="grid gap-2">
                                                    <Label htmlFor="item-disponivel">
                                                        Disponibilidade
                                                    </Label>
                                                    <Select
                                                        name="disponivel"
                                                        defaultValue="1"
                                                        required
                                                    >
                                                        <SelectTrigger
                                                            id="item-disponivel"
                                                            className="w-full"
                                                        >
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="1">
                                                                Disponível
                                                            </SelectItem>
                                                            <SelectItem value="0">
                                                                Indisponível
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError
                                                        message={
                                                            errors.disponivel
                                                        }
                                                    />
                                                </div>
                                                <Campo
                                                    id="item-ordem"
                                                    name="ordem"
                                                    label="Ordem"
                                                    type="number"
                                                    min="0"
                                                    max="999"
                                                    defaultValue="0"
                                                    error={errors.ordem}
                                                />
                                            </div>

                                            <Button disabled={processing}>
                                                Cadastrar item
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Tags /> Nova categoria
                            </CardTitle>
                            <CardDescription>
                                Categorias agrupam os itens exibidos na comanda.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...cadastrarCategoria.form()}
                                resetOnSuccess
                                className="grid gap-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <Campo
                                            id="categoria-nome"
                                            name="nome"
                                            label="Nome"
                                            placeholder="Ex.: Massas"
                                            error={errors.nome}
                                        />
                                        <div className="grid gap-2">
                                            <Label htmlFor="categoria-descricao">
                                                Descrição
                                            </Label>
                                            <textarea
                                                id="categoria-descricao"
                                                name="descricao"
                                                rows={3}
                                                className="resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-3 focus-visible:ring-ring/50"
                                                placeholder="Descrição opcional"
                                            />
                                            <InputError
                                                message={errors.descricao}
                                            />
                                        </div>
                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="categoria-ativa">
                                                    Situação
                                                </Label>
                                                <Select
                                                    name="ativa"
                                                    defaultValue="1"
                                                    required
                                                >
                                                    <SelectTrigger
                                                        id="categoria-ativa"
                                                        className="w-full"
                                                    >
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="1">
                                                            Ativa
                                                        </SelectItem>
                                                        <SelectItem value="0">
                                                            Inativa
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={errors.ativa}
                                                />
                                            </div>
                                            <Campo
                                                id="categoria-ordem"
                                                name="ordem"
                                                label="Ordem"
                                                type="number"
                                                min="0"
                                                max="999"
                                                defaultValue="0"
                                                error={errors.ordem}
                                            />
                                        </div>
                                        <Button disabled={processing}>
                                            Cadastrar categoria
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                </div>

                <section className="grid gap-3">
                    <div>
                        <h2 className="text-lg font-semibold">
                            Itens cadastrados
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Visualização do cardápio atualmente salvo.
                        </p>
                    </div>
                    {categorias.length === 0 ? (
                        <Card className="border-dashed py-10 text-center">
                            <CardContent className="text-muted-foreground">
                                Nenhuma categoria cadastrada.
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 lg:grid-cols-2">
                            {categorias.map((categoria) => (
                                <Card key={categoria.id} className="gap-4 py-4">
                                    <CardHeader className="px-4">
                                        <AcoesCategoria categoria={categoria} />
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <CardTitle>
                                                    {categoria.nome}
                                                </CardTitle>
                                                <CardDescription>
                                                    {categoria.descricao}
                                                </CardDescription>
                                            </div>
                                            <Badge
                                                variant={
                                                    categoria.ativa
                                                        ? 'secondary'
                                                        : 'outline'
                                                }
                                            >
                                                {categoria.ativa
                                                    ? 'Ativa'
                                                    : 'Inativa'}
                                            </Badge>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="grid gap-2 px-4">
                                        {categoria.itens.length === 0 ? (
                                            <p className="text-sm text-muted-foreground">
                                                Nenhum item nesta categoria.
                                            </p>
                                        ) : (
                                            categoria.itens.map((item) => (
                                                <div
                                                    key={item.id}
                                                    className="flex items-start justify-between gap-4 rounded-lg border p-3"
                                                >
                                                    <div className="grid gap-1">
                                                        <div className="flex flex-wrap items-center gap-2">
                                                            <strong>
                                                                {item.nome}
                                                            </strong>
                                                            {item.disponivel && (
                                                                <CheckCircle2 className="size-4 text-emerald-500" />
                                                            )}
                                                        </div>
                                                        <p className="text-xs text-muted-foreground">
                                                            {item.descricao ??
                                                                (item.disponivel
                                                                    ? 'Disponível'
                                                                    : 'Indisponível')}
                                                        </p>
                                                    </div>
                                                    <strong className="whitespace-nowrap">
                                                        {moeda.format(
                                                            item.preco_centavos /
                                                                100,
                                                        )}
                                                    </strong>
                                                    <AcoesItem
                                                        item={item}
                                                        categoriaId={
                                                            categoria.id
                                                        }
                                                        categorias={categorias}
                                                        setoresProducao={
                                                            setoresProducao
                                                        }
                                                    />
                                                </div>
                                            ))
                                        )}
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

function Resumo({ titulo, valor }: { titulo: string; valor: number }) {
    return (
        <Card className="py-4">
            <CardContent className="flex items-center justify-between px-4">
                <div>
                    <p className="text-sm text-muted-foreground">{titulo}</p>
                    <p className="text-2xl font-semibold">{valor}</p>
                </div>
                <div className="rounded-lg bg-muted p-2">
                    <Utensils className="size-4" />
                </div>
            </CardContent>
        </Card>
    );
}

function Campo({
    label,
    error,
    ...props
}: React.ComponentProps<typeof Input> & { label: string; error?: string }) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={props.id}>{label}</Label>
            <Input required {...props} />
            <InputError message={error} />
        </div>
    );
}

CardapioIndex.layout = {
    breadcrumbs: [{ title: 'Cadastro do cardápio', href: cardapio() }],
};
