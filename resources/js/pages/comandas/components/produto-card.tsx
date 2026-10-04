import { Minus, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { formatarCentavos } from '@/lib/formatters';
import type { ItemNovoPedido, Produto } from '../types';

type ProdutoCardProps = {
    produto: Produto;
    itemSelecionado?: ItemNovoPedido;
    onMudarQuantidade: (produtoId: number, diferenca: number) => void;
    onMudarObservacao: (produtoId: number, observacao: string) => void;
};

export function ProdutoCard({
    produto,
    itemSelecionado,
    onMudarQuantidade,
    onMudarObservacao,
}: ProdutoCardProps) {
    return (
        <Card className="gap-4 py-4">
            <CardHeader className="px-4">
                <div className="flex justify-between gap-4">
                    <div>
                        <CardTitle>{produto.nome}</CardTitle>
                        <CardDescription>{produto.descricao}</CardDescription>
                    </div>
                    <strong>{formatarCentavos(produto.preco_centavos)}</strong>
                </div>
            </CardHeader>
            <CardContent className="grid gap-3 px-4">
                <div className="flex items-center justify-between">
                    <span className="text-sm text-muted-foreground">
                        Quantidade
                    </span>
                    <div className="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            disabled={!itemSelecionado}
                            onClick={() => onMudarQuantidade(produto.id, -1)}
                            aria-label={`Diminuir quantidade de ${produto.nome}`}
                        >
                            <Minus />
                        </Button>
                        <strong className="w-6 text-center">
                            {itemSelecionado?.quantidade ?? 0}
                        </strong>
                        <Button
                            type="button"
                            size="icon"
                            onClick={() => onMudarQuantidade(produto.id, 1)}
                            aria-label={`Aumentar quantidade de ${produto.nome}`}
                        >
                            <Plus />
                        </Button>
                    </div>
                </div>
                {itemSelecionado && (
                    <Input
                        value={itemSelecionado.observacao}
                        placeholder="Ex.: sem cebola"
                        onChange={(event) =>
                            onMudarObservacao(produto.id, event.target.value)
                        }
                        aria-label={`Observação de ${produto.nome}`}
                    />
                )}
            </CardContent>
        </Card>
    );
}
