import { Form, Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { store, update, index } from '@/routes/caixas';
import { formatarCentavos } from '@/lib/formatters';
type Caixa = {
    id: number;
    valor_abertura_centavos: number;
    aberto_em: string;
    aberto_por: { name: string };
    movimentos: {
        id: number;
        tipo: string;
        descricao: string;
        valor_centavos: number;
        registrado_em: string;
    }[];
};
export default function CaixaIndex({ caixa }: { caixa: Caixa | null }) {
    return (
        <>
            <Head title="Caixa" />
            <div className="grid gap-5 p-6">
                <h1 className="text-3xl font-semibold">Caixa</h1>
                {!caixa ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>Abrir caixa</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form {...store.form()} className="flex gap-2">
                                <Input
                                    name="valor_abertura"
                                    type="number"
                                    step="0.01"
                                    placeholder="Valor inicial"
                                    required
                                />
                                <Button>Abrir</Button>
                            </Form>
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Caixa aberto por {caixa.aberto_por.name}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-3">
                                <p>
                                    Fundo inicial:{' '}
                                    {formatarCentavos(
                                        caixa.valor_abertura_centavos,
                                    )}
                                </p>
                                <Form
                                    {...update.form(caixa.id)}
                                    className="flex gap-2"
                                >
                                    <Input
                                        name="valor_informado"
                                        type="number"
                                        step="0.01"
                                        placeholder="Valor contado"
                                        required
                                    />
                                    <Button>Fechar e conferir</Button>
                                </Form>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle>Movimentos</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-2">
                                {caixa.movimentos.map((m) => (
                                    <div
                                        key={m.id}
                                        className="flex justify-between border-b py-2"
                                    >
                                        <span>{m.descricao}</span>
                                        <strong>
                                            {formatarCentavos(m.valor_centavos)}
                                        </strong>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </>
                )}
            </div>
        </>
    );
}
CaixaIndex.layout = { breadcrumbs: [{ title: 'Caixa', href: index() }] };
