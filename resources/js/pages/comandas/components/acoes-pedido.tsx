import { router, useForm } from '@inertiajs/react';
import { Ban, Send } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { store as cancelarPedido } from '@/routes/pedidos/cancelamentos';
import { store as enviarPedido } from '@/routes/pedidos/envios';
import type { Pedido } from '../types';

export function AcoesPedido({ pedido }: { pedido: Pedido }) {
    if (pedido.status === 'rascunho') {
        return (
            <Button
                size="sm"
                onClick={() => {
                    if (window.confirm('Enviar este pedido à produção?')) {
                        router.post(
                            enviarPedido.url(pedido.id),
                            {},
                            {
                                preserveScroll: true,
                            },
                        );
                    }
                }}
            >
                <Send /> Enviar à produção
            </Button>
        );
    }

    if (!['enviado', 'em_preparo', 'pronto'].includes(pedido.status)) {
        return null;
    }

    return <CancelarPedidoDialog pedido={pedido} />;
}

function CancelarPedidoDialog({ pedido }: { pedido: Pedido }) {
    const formulario = useForm({ motivo: '', pedido: '' });

    function cancelar(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        formulario.post(cancelarPedido.url(pedido.id), {
            preserveScroll: true,
            onSuccess: () => formulario.reset(),
        });
    }

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button size="sm" variant="destructive">
                    <Ban /> Cancelar pedido
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Cancelar pedido #{pedido.id}</DialogTitle>
                    <DialogDescription>
                        O motivo, o responsável e o horário ficarão registrados.
                    </DialogDescription>
                </DialogHeader>
                <form className="grid gap-4" onSubmit={cancelar}>
                    <div className="grid gap-2">
                        <Label htmlFor={`motivo-cancelamento-${pedido.id}`}>
                            Motivo do cancelamento
                        </Label>
                        <textarea
                            id={`motivo-cancelamento-${pedido.id}`}
                            value={formulario.data.motivo}
                            onChange={(event) =>
                                formulario.setData('motivo', event.target.value)
                            }
                            rows={4}
                            maxLength={500}
                            required
                            className="resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                            placeholder="Ex.: cliente desistiu do item"
                        />
                        <InputError
                            message={
                                formulario.errors.motivo ??
                                formulario.errors.pedido
                            }
                        />
                    </div>
                    <Button
                        type="submit"
                        variant="destructive"
                        disabled={formulario.processing}
                    >
                        Confirmar cancelamento
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    );
}
