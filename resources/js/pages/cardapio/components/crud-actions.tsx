import { Form, router } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    destroy as excluirCategoria,
    update as atualizarCategoria,
} from '@/routes/categorias-cardapio';
import {
    destroy as excluirItem,
    update as atualizarItem,
} from '@/routes/itens-cardapio';
import type { Categoria, ItemCardapio, SetorProducao } from '../index';

export function AcoesCategoria({ categoria }: { categoria: Categoria }) {
    function excluir() {
        if (window.confirm(`Excluir a categoria ${categoria.nome}?`)) {
            router.delete(excluirCategoria.url(categoria.id), {
                preserveScroll: true,
            });
        }
    }

    return (
        <div className="mb-2 flex justify-end gap-2">
            <Dialog>
                <DialogTrigger asChild>
                    <Button size="sm" variant="outline">
                        <Pencil /> Editar
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Editar categoria</DialogTitle>
                        <DialogDescription>
                            Atualize os dados da categoria.
                        </DialogDescription>
                    </DialogHeader>
                    <Form
                        {...atualizarCategoria.form(categoria.id)}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <Campo
                                    name="nome"
                                    label="Nome"
                                    defaultValue={categoria.nome}
                                    error={errors.nome}
                                />
                                <CampoTexto
                                    name="descricao"
                                    label="Descrição"
                                    defaultValue={categoria.descricao ?? ''}
                                    error={errors.descricao}
                                />
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <CampoSelecao
                                        name="ativa"
                                        label="Situação"
                                        defaultValue={
                                            categoria.ativa ? '1' : '0'
                                        }
                                        options={[
                                            ['1', 'Ativa'],
                                            ['0', 'Inativa'],
                                        ]}
                                        error={errors.ativa}
                                    />
                                    <Campo
                                        name="ordem"
                                        label="Ordem"
                                        type="number"
                                        min="0"
                                        max="999"
                                        defaultValue={categoria.ordem}
                                        error={errors.ordem}
                                    />
                                </div>
                                <Button disabled={processing}>
                                    Salvar categoria
                                </Button>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
            <Button size="sm" variant="destructive" onClick={excluir}>
                <Trash2 /> Excluir
            </Button>
        </div>
    );
}

export function AcoesItem({
    item,
    categoriaId,
    categorias,
    setoresProducao,
}: {
    item: ItemCardapio;
    categoriaId: number;
    categorias: Categoria[];
    setoresProducao: SetorProducao[];
}) {
    function excluir() {
        if (window.confirm(`Excluir o item ${item.nome}?`)) {
            router.delete(excluirItem.url(item.id), { preserveScroll: true });
        }
    }

    return (
        <div className="flex shrink-0 gap-2">
            <Dialog>
                <DialogTrigger asChild>
                    <Button
                        size="icon"
                        variant="outline"
                        aria-label={`Editar ${item.nome}`}
                    >
                        <Pencil />
                    </Button>
                </DialogTrigger>
                <DialogContent className="max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Editar item</DialogTitle>
                        <DialogDescription>
                            Atualize produto, preço e disponibilidade.
                        </DialogDescription>
                    </DialogHeader>
                    <Form
                        {...atualizarItem.form(item.id)}
                        className="grid gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <CampoSelecao
                                    name="categoria_cardapio_id"
                                    label="Categoria"
                                    defaultValue={String(categoriaId)}
                                    options={categorias.map((categoria) => [
                                        String(categoria.id),
                                        categoria.nome,
                                    ])}
                                    error={errors.categoria_cardapio_id}
                                />
                                <CampoSelecao
                                    name="setor_producao_id"
                                    label="Setor de produção"
                                    defaultValue={String(
                                        item.setor_producao_id ?? '',
                                    )}
                                    options={setoresProducao.map((setor) => [
                                        String(setor.id),
                                        `${setor.nome}${setor.ativo ? '' : ' (inativo)'}`,
                                    ])}
                                    error={errors.setor_producao_id}
                                />
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <Campo
                                        name="nome"
                                        label="Nome"
                                        defaultValue={item.nome}
                                        error={errors.nome}
                                    />
                                    <Campo
                                        name="preco"
                                        label="Preço (R$)"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        defaultValue={(
                                            item.preco_centavos / 100
                                        ).toFixed(2)}
                                        error={errors.preco}
                                    />
                                </div>
                                <CampoTexto
                                    name="descricao"
                                    label="Descrição"
                                    defaultValue={item.descricao ?? ''}
                                    error={errors.descricao}
                                />
                                <Campo
                                    name="imagem"
                                    label="URL da imagem"
                                    type="url"
                                    defaultValue={item.imagem ?? ''}
                                    error={errors.imagem}
                                    required={false}
                                />
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <CampoSelecao
                                        name="disponivel"
                                        label="Disponibilidade"
                                        defaultValue={
                                            item.disponivel ? '1' : '0'
                                        }
                                        options={[
                                            ['1', 'Disponível'],
                                            ['0', 'Indisponível'],
                                        ]}
                                        error={errors.disponivel}
                                    />
                                    <Campo
                                        name="ordem"
                                        label="Ordem"
                                        type="number"
                                        min="0"
                                        max="999"
                                        defaultValue={item.ordem}
                                        error={errors.ordem}
                                    />
                                </div>
                                <Button disabled={processing}>
                                    Salvar item
                                </Button>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
            <Button
                size="icon"
                variant="destructive"
                onClick={excluir}
                aria-label={`Excluir ${item.nome}`}
            >
                <Trash2 />
            </Button>
        </div>
    );
}

function Campo({
    label,
    error,
    ...props
}: React.ComponentProps<typeof Input> & { label: string; error?: string }) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <Input required {...props} />
            <InputError message={error} />
        </div>
    );
}

function CampoTexto({
    name,
    label,
    defaultValue,
    error,
}: {
    name: string;
    label: string;
    defaultValue: string;
    error?: string;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <textarea
                name={name}
                rows={3}
                defaultValue={defaultValue}
                className="resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm"
            />
            <InputError message={error} />
        </div>
    );
}

function CampoSelecao({
    name,
    label,
    defaultValue,
    options,
    error,
}: {
    name: string;
    label: string;
    defaultValue: string;
    options: [string, string][];
    error?: string;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <Select name={name} defaultValue={defaultValue} required>
                <SelectTrigger className="w-full">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {options.map(([value, text]) => (
                        <SelectItem key={value} value={value}>
                            {text}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <InputError message={error} />
        </div>
    );
}
