import { useForm } from '@inertiajs/react';
import { CreditCard } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatarCentavos } from '@/lib/formatters';
import { update as ajustar } from '@/routes/comandas/conta';
import { store as pagar } from '@/routes/comandas/pagamentos';
import type { FormaPagamento, ItemPedido } from '../types';
type Conta = {
    subtotal: number;
    servico: number;
    couvert: number;
    desconto: number;
    acrescimo: number;
    total: number;
    pago: number;
    saldo: number;
};
export function FechamentoCard({
    comandaId,
    quantidadePessoas,
    podeFechar,
    formasPagamento,
    conta,
    itens,
}: {
    comandaId: number;
    quantidadePessoas: number;
    quantidadePedidos: number;
    totalCentavos: number;
    podeFechar: boolean;
    formasPagamento: FormaPagamento[];
    conta: Conta;
    itens: ItemPedido[];
}) {
    const f = useForm({
        forma_pagamento: '',
        tipo_divisao: 'integral',
        valor: '',
        itens: [] as number[],
    });
    const a = useForm({
        servico_percentual: '0',
        couvert: '0',
        desconto: '0',
        acrescimo: '0',
    });
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex gap-2">
                    <CreditCard />
                    Conta e pagamentos
                </CardTitle>
            </CardHeader>
            <CardContent className="grid gap-4">
                <div className="grid grid-cols-2 gap-2">
                    {Object.entries(conta).map(([n, v]) => (
                        <div
                            key={n}
                            className="flex justify-between rounded border p-2 text-sm"
                        >
                            <span>{n}</span>
                            <strong>{formatarCentavos(v)}</strong>
                        </div>
                    ))}
                </div>
                {podeFechar && (
                    <>
                        <form
                            className="grid grid-cols-2 gap-2"
                            onSubmit={(e) => {
                                e.preventDefault();
                                a.patch(ajustar.url(comandaId), {
                                    preserveScroll: true,
                                });
                            }}
                        >
                            {(
                                [
                                    'servico_percentual',
                                    'couvert',
                                    'desconto',
                                    'acrescimo',
                                ] as const
                            ).map((n) => (
                                <Input
                                    key={n}
                                    placeholder={n}
                                    value={a.data[n]}
                                    onChange={(e) =>
                                        a.setData(n, e.target.value)
                                    }
                                />
                            ))}
                            <Button className="col-span-2" variant="outline">
                                Recalcular
                            </Button>
                        </form>
                        <form
                            className="grid gap-2"
                            onSubmit={(e) => {
                                e.preventDefault();
                                f.post(pagar.url(comandaId), {
                                    preserveScroll: true,
                                });
                            }}
                        >
                            <Select
                                value={f.data.tipo_divisao}
                                onValueChange={(v) =>
                                    f.setData('tipo_divisao', v)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="integral">
                                        Saldo integral
                                    </SelectItem>
                                    <SelectItem value="pessoa">
                                        Por pessoa (
                                        {formatarCentavos(
                                            Math.ceil(
                                                conta.saldo / quantidadePessoas,
                                            ),
                                        )}
                                        )
                                    </SelectItem>
                                    <SelectItem value="valor">
                                        Por valor
                                    </SelectItem>
                                    <SelectItem value="itens">
                                        Por itens
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {f.data.tipo_divisao === 'itens' &&
                                itens
                                    .filter((i) => i.status !== 'cancelado')
                                    .map((i) => (
                                        <label
                                            key={i.id}
                                            className="flex gap-2 text-sm"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={f.data.itens.includes(
                                                    i.id,
                                                )}
                                                onChange={() =>
                                                    f.setData(
                                                        'itens',
                                                        f.data.itens.includes(
                                                            i.id,
                                                        )
                                                            ? f.data.itens.filter(
                                                                  (x) =>
                                                                      x !==
                                                                      i.id,
                                                              )
                                                            : [
                                                                  ...f.data
                                                                      .itens,
                                                                  i.id,
                                                              ],
                                                    )
                                                }
                                            />
                                            {i.quantidade}× {i.nome_item}
                                        </label>
                                    ))}
                            {f.data.tipo_divisao !== 'integral' &&
                                f.data.tipo_divisao !== 'itens' && (
                                    <Input
                                        type="number"
                                        step="0.01"
                                        placeholder="Valor"
                                        value={f.data.valor}
                                        onChange={(e) =>
                                            f.setData('valor', e.target.value)
                                        }
                                    />
                                )}
                            <Select
                                value={f.data.forma_pagamento}
                                onValueChange={(v) =>
                                    f.setData('forma_pagamento', v)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Forma" />
                                </SelectTrigger>
                                <SelectContent>
                                    {formasPagamento.map((x) => (
                                        <SelectItem
                                            key={x.valor}
                                            value={x.valor}
                                        >
                                            {x.nome}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Button disabled={f.processing || conta.saldo <= 0}>
                                Registrar pagamento
                            </Button>
                        </form>
                    </>
                )}
            </CardContent>
        </Card>
    );
}
