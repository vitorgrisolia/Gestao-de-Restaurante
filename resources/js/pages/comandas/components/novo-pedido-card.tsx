import type { InertiaFormProps } from '@inertiajs/react';
import { ShoppingCart } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { formatarCentavos } from '@/lib/formatters';
import { precoPorPorcao } from '@/lib/venda';
import type { ItemNovoPedido, Produto } from '../types';

export type NovoPedidoForm = {
    comanda: string;
    observacao: string;
    itens: ItemNovoPedido[];
};

type NovoPedidoCardProps = {
    formulario: InertiaFormProps<NovoPedidoForm>;
    produtos: Produto[];
    totalCentavos: number;
    onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
};

export function NovoPedidoCard({
    formulario,
    produtos,
    totalCentavos,
    onSubmit,
}: NovoPedidoCardProps) {
    const { data, setData, processing, errors } = formulario;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex gap-2">
                    <ShoppingCart /> Novo pedido
                </CardTitle>
                <CardDescription>Revise itens e valores.</CardDescription>
            </CardHeader>
            <CardContent>
                <form className="grid gap-4" onSubmit={onSubmit}>
                    {data.itens.length === 0 ? (
                        <p className="rounded-lg border border-dashed p-4 text-center text-sm text-muted-foreground">
                            Selecione itens no cardápio.
                        </p>
                    ) : (
                        <div className="grid gap-3">
                            {data.itens.map((item) => {
                                const produto = produtos.find(
                                    ({ id }) => id === item.item_cardapio_id,
                                );

                                if (!produto) {
                                    return null;
                                }

                                return (
                                    <div
                                        key={produto.id}
                                        className="flex justify-between gap-3 text-sm"
                                    >
                                        <span>
                                            {item.quantidade}× {produto.nome}
                                        </span>
                                        <strong>
                                            {formatarCentavos(
                                                precoPorPorcao(
                                                    produto.tipo_venda,
                                                    produto.preco_centavos,
                                                    item,
                                                ) * item.quantidade,
                                            )}
                                        </strong>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                    <div className="grid gap-2">
                        <Label htmlFor="observacao">Observação geral</Label>
                        <textarea
                            id="observacao"
                            rows={3}
                            value={data.observacao}
                            onChange={(event) =>
                                setData('observacao', event.target.value)
                            }
                            className="resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                    </div>
                    {Object.entries(errors).map(([campo, mensagem]) => (
                        <InputError key={campo} message={mensagem} />
                    ))}
                    <div className="flex justify-between border-t pt-4 font-semibold">
                        <span>Total</span>
                        <span>{formatarCentavos(totalCentavos)}</span>
                    </div>
                    <Button disabled={processing || data.itens.length === 0}>
                        Registrar pedido
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}
