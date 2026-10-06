import { Head, useForm } from '@inertiajs/react';
import { ClipboardCheck, Download, PackagePlus, Scale } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Unidade = {
    id: number;
    nome: string;
    sigla: string;
    casas_decimais: number;
    ativa: boolean;
};
type Ingrediente = {
    id: number;
    nome: string;
    estoque_atual: string;
    estoque_minimo: string;
    custo_medio_centavos: number;
    ativo: boolean;
    unidade_medida: Unidade;
};
type Movimento = {
    id: number;
    tipo: string;
    quantidade: string;
    saldo_posterior: string;
    motivo: string | null;
    registrada_em: string;
    ingrediente: Ingrediente;
    usuario: { name: string };
};
type ItemInventario = {
    id: number;
    quantidade_sistema: string;
    ingrediente: Ingrediente;
};
type ItemCardapio = {
    id: number;
    nome: string;
    ficha_tecnica: {
        ingrediente_id: number;
        quantidade: string;
        ingrediente: Ingrediente;
    }[];
};
type Props = {
    podeAdministrar: boolean;
    unidades: Unidade[];
    ingredientes: Ingrediente[];
    itensCardapio: ItemCardapio[];
    movimentacoes: Movimento[];
    inventarioAberto: { id: number; itens: ItemInventario[] } | null;
    resumo: {
        itens_ativos: number;
        abaixo_minimo: number;
        valor_estoque_centavos: number;
    };
};
const moeda = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

export default function EstoqueIndex({
    podeAdministrar,
    unidades,
    ingredientes,
    itensCardapio,
    movimentacoes,
    inventarioAberto,
    resumo,
}: Props) {
    const unidade = useForm({
        nome: '',
        sigla: '',
        casas_decimais: 3,
        ativa: true,
    });
    const ingrediente = useForm({
        nome: '',
        unidade_medida_id: '',
        estoque_minimo: '0',
        ativo: true,
    });
    const movimento = useForm({
        ingrediente_id: '',
        tipo: 'entrada',
        quantidade: '',
        custo_unitario: '',
        motivo: '',
    });
    const inventario = useForm({ observacao: '' });
    const contagem = useForm({
        itens: (inventarioAberto?.itens ?? []).map((item) => ({
            id: item.id,
            quantidade_contada: item.quantidade_sistema,
        })),
    });
    return (
        <>
            <Head title="Estoque e gestão" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p className="text-sm font-medium text-primary">
                            Operação e custos
                        </p>
                        <h1 className="text-3xl font-semibold">
                            Estoque e gestão
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Saldos, ficha técnica, inventário e trilha completa
                            de movimentações.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <a href="/estoque/relatorio.csv">
                            <Download className="mr-2 size-4" />
                            Exportar CSV
                        </a>
                    </Button>
                </header>
                <div className="grid gap-3 sm:grid-cols-3">
                    <Resumo
                        titulo="Ingredientes ativos"
                        valor={resumo.itens_ativos}
                    />
                    <Resumo
                        titulo="Abaixo do mínimo"
                        valor={resumo.abaixo_minimo}
                    />
                    <Resumo
                        titulo="Valor em estoque"
                        valor={moeda.format(
                            resumo.valor_estoque_centavos / 100,
                        )}
                    />
                </div>
                <div className="grid items-start gap-6 xl:grid-cols-2">
                    {podeAdministrar && (
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    <Scale className="mr-2 inline size-5" />
                                    Cadastros básicos
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-6">
                                <form
                                    className="grid gap-3 sm:grid-cols-4"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        unidade.post('/unidades-medida', {
                                            onSuccess: () => unidade.reset(),
                                        });
                                    }}
                                >
                                    <div className="sm:col-span-2">
                                        <Label>Unidade</Label>
                                        <Input
                                            value={unidade.data.nome}
                                            onChange={(e) =>
                                                unidade.setData(
                                                    'nome',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Quilograma"
                                        />
                                    </div>
                                    <div>
                                        <Label>Sigla</Label>
                                        <Input
                                            value={unidade.data.sigla}
                                            onChange={(e) =>
                                                unidade.setData(
                                                    'sigla',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="kg"
                                        />
                                    </div>
                                    <Button className="self-end">
                                        Cadastrar
                                    </Button>
                                </form>
                                <form
                                    className="grid gap-3 sm:grid-cols-2"
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        ingrediente.post('/ingredientes', {
                                            onSuccess: () =>
                                                ingrediente.reset(),
                                        });
                                    }}
                                >
                                    <div>
                                        <Label>Ingrediente</Label>
                                        <Input
                                            value={ingrediente.data.nome}
                                            onChange={(e) =>
                                                ingrediente.setData(
                                                    'nome',
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="Arroz branco"
                                        />
                                    </div>
                                    <div>
                                        <Label>Unidade</Label>
                                        <select
                                            className="h-9 w-full rounded-md border bg-background px-3"
                                            value={
                                                ingrediente.data
                                                    .unidade_medida_id
                                            }
                                            onChange={(e) =>
                                                ingrediente.setData(
                                                    'unidade_medida_id',
                                                    e.target.value,
                                                )
                                            }
                                        >
                                            <option value="">Selecione</option>
                                            {unidades
                                                .filter((u) => u.ativa)
                                                .map((u) => (
                                                    <option
                                                        key={u.id}
                                                        value={u.id}
                                                    >
                                                        {u.nome} ({u.sigla})
                                                    </option>
                                                ))}
                                        </select>
                                    </div>
                                    <div>
                                        <Label>Estoque mínimo</Label>
                                        <Input
                                            type="number"
                                            step="0.001"
                                            value={
                                                ingrediente.data.estoque_minimo
                                            }
                                            onChange={(e) =>
                                                ingrediente.setData(
                                                    'estoque_minimo',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </div>
                                    <Button className="self-end">
                                        <PackagePlus className="mr-2 size-4" />
                                        Cadastrar ingrediente
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    )}
                    <Card>
                        <CardHeader>
                            <CardTitle>Entrada, perda ou ajuste</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form
                                className="grid gap-3 sm:grid-cols-2"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    movimento.post(
                                        `/ingredientes/${movimento.data.ingrediente_id}/movimentacoes`,
                                        { onSuccess: () => movimento.reset() },
                                    );
                                }}
                            >
                                <select
                                    className="h-9 rounded-md border bg-background px-3"
                                    value={movimento.data.ingrediente_id}
                                    onChange={(e) =>
                                        movimento.setData(
                                            'ingrediente_id',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="">Ingrediente</option>
                                    {ingredientes
                                        .filter((i) => i.ativo)
                                        .map((i) => (
                                            <option key={i.id} value={i.id}>
                                                {i.nome}
                                            </option>
                                        ))}
                                </select>
                                <select
                                    className="h-9 rounded-md border bg-background px-3"
                                    value={movimento.data.tipo}
                                    onChange={(e) =>
                                        movimento.setData(
                                            'tipo',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="entrada">Entrada</option>
                                    <option value="perda">Perda</option>
                                    <option value="ajuste">Ajuste</option>
                                </select>
                                <Input
                                    type="number"
                                    step="0.001"
                                    placeholder="Quantidade"
                                    value={movimento.data.quantidade}
                                    onChange={(e) =>
                                        movimento.setData(
                                            'quantidade',
                                            e.target.value,
                                        )
                                    }
                                />
                                <Input
                                    type="number"
                                    step="0.01"
                                    placeholder="Custo unitário (entrada)"
                                    value={movimento.data.custo_unitario}
                                    onChange={(e) =>
                                        movimento.setData(
                                            'custo_unitario',
                                            e.target.value,
                                        )
                                    }
                                />
                                <Input
                                    className="sm:col-span-2"
                                    placeholder="Motivo ou documento"
                                    value={movimento.data.motivo}
                                    onChange={(e) =>
                                        movimento.setData(
                                            'motivo',
                                            e.target.value,
                                        )
                                    }
                                />
                                <Button
                                    className="sm:col-span-2"
                                    disabled={!movimento.data.ingrediente_id}
                                >
                                    Registrar movimentação
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
                {podeAdministrar && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Fichas técnicas</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 xl:grid-cols-2">
                            {itensCardapio.map((item) => (
                                <FichaTecnica
                                    key={item.id}
                                    item={item}
                                    ingredientes={ingredientes}
                                />
                            ))}
                        </CardContent>
                    </Card>
                )}
                <Card>
                    <CardHeader>
                        <CardTitle>
                            <ClipboardCheck className="mr-2 inline size-5" />
                            Inventário
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {!inventarioAberto ? (
                            <form
                                className="flex gap-3"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    inventario.post('/inventarios');
                                }}
                            >
                                <Input
                                    placeholder="Observação (opcional)"
                                    value={inventario.data.observacao}
                                    onChange={(e) =>
                                        inventario.setData(
                                            'observacao',
                                            e.target.value,
                                        )
                                    }
                                />
                                <Button>Iniciar contagem</Button>
                            </form>
                        ) : (
                            <form
                                className="grid gap-3"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    contagem.patch(
                                        `/inventarios/${inventarioAberto.id}`,
                                    );
                                }}
                            >
                                {inventarioAberto.itens.map((item, index) => (
                                    <div
                                        key={item.id}
                                        className="grid grid-cols-[1fr_8rem_8rem] items-center gap-3 border-b pb-2"
                                    >
                                        <span>
                                            {item.ingrediente.nome}{' '}
                                            <small className="text-muted-foreground">
                                                (
                                                {
                                                    item.ingrediente
                                                        .unidade_medida.sigla
                                                }
                                                )
                                            </small>
                                        </span>
                                        <span className="text-sm">
                                            Sistema: {item.quantidade_sistema}
                                        </span>
                                        <Input
                                            type="number"
                                            step="0.001"
                                            value={
                                                contagem.data.itens[index]
                                                    ?.quantidade_contada ?? ''
                                            }
                                            onChange={(e) =>
                                                contagem.setData(
                                                    'itens',
                                                    contagem.data.itens.map(
                                                        (v, i) =>
                                                            i === index
                                                                ? {
                                                                      ...v,
                                                                      quantidade_contada:
                                                                          e
                                                                              .target
                                                                              .value,
                                                                  }
                                                                : v,
                                                    ),
                                                )
                                            }
                                        />
                                    </div>
                                ))}
                                <Button>Concluir e ajustar saldos</Button>
                            </form>
                        )}
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Posição atual</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2">
                        {ingredientes.map((i) => (
                            <div
                                key={i.id}
                                className="grid grid-cols-[1fr_auto_auto] gap-4 rounded-md border p-3"
                            >
                                <span>{i.nome}</span>
                                <span
                                    className={
                                        Number(i.estoque_atual) <=
                                        Number(i.estoque_minimo)
                                            ? 'font-semibold text-destructive'
                                            : ''
                                    }
                                >
                                    {i.estoque_atual} {i.unidade_medida.sigla}
                                </span>
                                <span>
                                    {moeda.format(i.custo_medio_centavos / 100)}
                                    /{i.unidade_medida.sigla}
                                </span>
                            </div>
                        ))}
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Auditoria recente</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2">
                        {movimentacoes.map((m) => (
                            <div
                                key={m.id}
                                className="grid gap-1 border-b py-2 text-sm sm:grid-cols-5"
                            >
                                <b>{m.ingrediente.nome}</b>
                                <span>
                                    {m.tipo}: {m.quantidade}
                                </span>
                                <span>Saldo: {m.saldo_posterior}</span>
                                <span>{m.usuario.name}</span>
                                <span>
                                    {new Date(m.registrada_em).toLocaleString(
                                        'pt-BR',
                                    )}
                                </span>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
function Resumo({ titulo, valor }: { titulo: string; valor: string | number }) {
    return (
        <Card>
            <CardContent className="p-4">
                <p className="text-sm text-muted-foreground">{titulo}</p>
                <p className="text-2xl font-semibold">{valor}</p>
            </CardContent>
        </Card>
    );
}
function FichaTecnica({
    item,
    ingredientes,
}: {
    item: ItemCardapio;
    ingredientes: Ingrediente[];
}) {
    const ficha = useForm({
        ingredientes: item.ficha_tecnica.map((linha) => ({
            ingrediente_id: String(linha.ingrediente_id),
            quantidade: linha.quantidade,
        })),
    });
    const adicionar = () =>
        ficha.setData('ingredientes', [
            ...ficha.data.ingredientes,
            { ingrediente_id: '', quantidade: '' },
        ]);
    return (
        <form
            className="grid gap-2 rounded-md border p-3"
            onSubmit={(e) => {
                e.preventDefault();
                ficha.put(`/itens-cardapio/${item.id}/ficha-tecnica`);
            }}
        >
            <b>{item.nome}</b>
            {ficha.data.ingredientes.map((linha, index) => (
                <div
                    key={index}
                    className="grid grid-cols-[1fr_8rem_auto] gap-2"
                >
                    <select
                        className="h-9 rounded-md border bg-background px-2"
                        value={linha.ingrediente_id}
                        onChange={(e) =>
                            ficha.setData(
                                'ingredientes',
                                ficha.data.ingredientes.map((v, i) =>
                                    i === index
                                        ? {
                                              ...v,
                                              ingrediente_id: e.target.value,
                                          }
                                        : v,
                                ),
                            )
                        }
                    >
                        <option value="">Ingrediente</option>
                        {ingredientes
                            .filter((i) => i.ativo)
                            .map((i) => (
                                <option key={i.id} value={i.id}>
                                    {i.nome} ({i.unidade_medida.sigla})
                                </option>
                            ))}
                    </select>
                    <Input
                        type="number"
                        step="0.001"
                        placeholder="Qtd."
                        value={linha.quantidade}
                        onChange={(e) =>
                            ficha.setData(
                                'ingredientes',
                                ficha.data.ingredientes.map((v, i) =>
                                    i === index
                                        ? { ...v, quantidade: e.target.value }
                                        : v,
                                ),
                            )
                        }
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        onClick={() =>
                            ficha.setData(
                                'ingredientes',
                                ficha.data.ingredientes.filter(
                                    (_, i) => i !== index,
                                ),
                            )
                        }
                    >
                        Remover
                    </Button>
                </div>
            ))}
            <div className="flex gap-2">
                <Button type="button" variant="outline" onClick={adicionar}>
                    Adicionar ingrediente
                </Button>
                <Button>Salvar ficha</Button>
            </div>
        </form>
    );
}
