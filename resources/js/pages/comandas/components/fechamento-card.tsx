import { useForm } from '@inertiajs/react';
import { CreditCard } from 'lucide-react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatarCentavos } from '@/lib/formatters';
import { store as registrarPagamento } from '@/routes/comandas/pagamentos';
import type { FormaPagamento } from '../types';

type FechamentoCardProps = {
    comandaId: number;
    quantidadePessoas: number;
    quantidadePedidos: number;
    totalCentavos: number;
    podeFechar: boolean;
    formasPagamento: FormaPagamento[];
};

export function FechamentoCard({
    comandaId,
    quantidadePessoas,
    quantidadePedidos,
    totalCentavos,
    podeFechar,
    formasPagamento,
}: FechamentoCardProps) {
    const formulario = useForm<{
        forma_pagamento: string;
        comanda?: string;
    }>({ forma_pagamento: '' });

    function finalizar(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!window.confirm('Confirmar o recebimento e liberar esta mesa?')) {
            return;
        }

        formulario.post(registrarPagamento.url(comandaId), {
            preserveScroll: true,
        });
    }

    return (
        <Card className="xl:sticky xl:top-4">
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <CreditCard /> Fechamento da comanda
                </CardTitle>
                <CardDescription>
                    Confira o total antes de receber o pagamento.
                </CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4">
                <div className="grid grid-cols-2 gap-3 text-sm">
                    <Resumo titulo="Pedidos" valor={quantidadePedidos} />
                    <Resumo titulo="Pessoas" valor={quantidadePessoas} />
                </div>
                <div className="flex items-end justify-between border-y py-4">
                    <span className="font-medium">Total da comanda</span>
                    <strong className="text-2xl">
                        {formatarCentavos(totalCentavos)}
                    </strong>
                </div>
                {podeFechar ? (
                    <form className="grid gap-3" onSubmit={finalizar}>
                        <Label htmlFor="forma-pagamento">
                            Forma de pagamento
                        </Label>
                        <Select
                            value={formulario.data.forma_pagamento}
                            onValueChange={(valor) =>
                                formulario.setData('forma_pagamento', valor)
                            }
                            required
                        >
                            <SelectTrigger
                                id="forma-pagamento"
                                className="w-full"
                            >
                                <SelectValue placeholder="Selecione" />
                            </SelectTrigger>
                            <SelectContent>
                                {formasPagamento.map((forma) => (
                                    <SelectItem
                                        key={forma.valor}
                                        value={forma.valor}
                                    >
                                        {forma.nome}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError
                            message={
                                formulario.errors.forma_pagamento ??
                                formulario.errors.comanda
                            }
                        />
                        <Button
                            type="submit"
                            disabled={
                                formulario.processing ||
                                !formulario.data.forma_pagamento ||
                                totalCentavos <= 0
                            }
                        >
                            Receber e fechar comanda
                        </Button>
                        <p className="text-xs text-muted-foreground">
                            Ao confirmar, a mesa será liberada automaticamente.
                        </p>
                    </form>
                ) : (
                    <p className="rounded-lg border border-dashed p-3 text-sm text-muted-foreground">
                        O pagamento pode ser recebido pelo caixa, gerente ou
                        proprietário.
                    </p>
                )}
            </CardContent>
        </Card>
    );
}

function Resumo({ titulo, valor }: { titulo: string; valor: number }) {
    return (
        <div className="rounded-lg border p-3">
            <span className="text-muted-foreground">{titulo}</span>
            <p className="text-lg font-semibold">{valor}</p>
        </div>
    );
}
