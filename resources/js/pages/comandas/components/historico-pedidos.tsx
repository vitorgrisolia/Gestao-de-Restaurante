import { router, useForm } from '@inertiajs/react';
import { Save, Trash2 } from 'lucide-react';
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
import { formatarCentavos } from '@/lib/formatters';
import {
    destroy as removerItemPedido,
    update as atualizarItemPedido,
} from '@/routes/pedido-itens';
import { AcoesPedido } from './acoes-pedido';
import type { ItemPedido, Pedido } from '../types';

export function HistoricoPedidos({ pedidos }: { pedidos: Pedido[] }) {
    return (
        <section className="grid gap-3">
            <h2 className="text-lg font-semibold">Pedidos da comanda</h2>
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
    );
}

function PedidoCard({ pedido }: { pedido: Pedido }) {
    const totalCentavos = pedido.itens.reduce(
        (total, item) =>
            item.status === 'cancelado'
                ? total
                : total + item.quantidade * item.preco_unitario_centavos,
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
            <div className="flex justify-end">
                <AcoesPedido pedido={pedido} />
            </div>
            <CardContent className="grid gap-3 px-4">
                {pedido.itens.map((item) =>
                    pedido.status === 'rascunho' &&
                    item.status === 'rascunho' ? (
                        <ItemEditavel key={item.id} item={item} />
                    ) : (
                        <ItemSomenteLeitura key={item.id} item={item} />
                    ),
                )}
                <div className="flex justify-between font-semibold">
                    <span>Total</span>
                    <span>{formatarCentavos(totalCentavos)}</span>
                </div>
            </CardContent>
        </Card>
    );
}

function ItemEditavel({ item }: { item: ItemPedido }) {
    const formulario = useForm<{
        quantidade: number;
        observacao: string;
        item?: string;
    }>({
        quantidade: item.quantidade,
        observacao: item.observacao ?? '',
    });

    function salvar(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        formulario.patch(atualizarItemPedido.url(item.id), {
            preserveScroll: true,
        });
    }

    function remover() {
        if (
            !window.confirm(
                `Remover ${item.nome_item} deste pedido em rascunho?`,
            )
        ) {
            return;
        }

        router.delete(removerItemPedido.url(item.id), {
            preserveScroll: true,
        });
    }

    const subtotalCentavos =
        formulario.data.quantidade * item.preco_unitario_centavos;

    return (
        <form className="grid gap-3 border-b pb-3" onSubmit={salvar}>
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-medium">{item.nome_item}</p>
                    <p className="text-xs text-muted-foreground">
                        {formatarCentavos(item.preco_unitario_centavos)} por
                        unidade
                    </p>
                </div>
                <strong className="text-sm whitespace-nowrap">
                    {formatarCentavos(subtotalCentavos)}
                </strong>
            </div>
            <div className="grid gap-3 sm:grid-cols-[6rem_minmax(0,1fr)]">
                <div className="grid gap-1.5">
                    <Label htmlFor={`quantidade-${item.id}`}>Quantidade</Label>
                    <Input
                        id={`quantidade-${item.id}`}
                        type="number"
                        min={1}
                        max={99}
                        value={formulario.data.quantidade}
                        onChange={(event) =>
                            formulario.setData(
                                'quantidade',
                                Number(event.target.value),
                            )
                        }
                        required
                    />
                </div>
                <div className="grid gap-1.5">
                    <Label htmlFor={`observacao-${item.id}`}>Observação</Label>
                    <Input
                        id={`observacao-${item.id}`}
                        value={formulario.data.observacao}
                        onChange={(event) =>
                            formulario.setData('observacao', event.target.value)
                        }
                        placeholder="Ex.: sem cebola"
                        maxLength={500}
                    />
                </div>
            </div>
            <InputError
                message={
                    formulario.errors.quantidade ??
                    formulario.errors.observacao ??
                    formulario.errors.item
                }
            />
            <div className="flex flex-wrap justify-end gap-2">
                <Button
                    type="button"
                    size="sm"
                    variant="destructive"
                    onClick={remover}
                    disabled={formulario.processing}
                >
                    <Trash2 /> Remover
                </Button>
                <Button
                    type="submit"
                    size="sm"
                    disabled={formulario.processing || !formulario.isDirty}
                >
                    <Save /> Salvar alteração
                </Button>
            </div>
        </form>
    );
}

function ItemSomenteLeitura({ item }: { item: ItemPedido }) {
    const subtotalCentavos = item.quantidade * item.preco_unitario_centavos;

    return (
        <div className="grid gap-1 border-b pb-3">
            <div className="flex justify-between text-sm">
                <span>
                    {item.quantidade}× {item.nome_item}
                </span>
                <strong>{formatarCentavos(subtotalCentavos)}</strong>
            </div>
            {item.observacao && (
                <p className="text-xs text-muted-foreground">
                    {item.observacao}
                </p>
            )}
            {item.status === 'cancelado' && item.motivo_cancelamento && (
                <p className="rounded-md bg-destructive/10 p-2 text-xs text-destructive">
                    Cancelado por {item.cancelado_por?.name ?? 'usuário'}:{' '}
                    {item.motivo_cancelamento}
                </p>
            )}
        </div>
    );
}
