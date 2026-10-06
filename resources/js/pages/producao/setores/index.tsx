import { Form, Head, router } from '@inertiajs/react';
import { ChefHat, Pencil, Plus, Trash2 } from 'lucide-react';
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
import { destroy, index, store, update } from '@/routes/setores-producao';

type SetorProducao = {
    id: number;
    nome: string;
    descricao: string | null;
    ativo: boolean;
    ordem: number;
    itens_cardapio_count: number;
};

export default function SetoresProducaoIndex({
    setores,
}: {
    setores: SetorProducao[];
}) {
    return (
        <>
            <Head title="Setores de produção" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div className="grid gap-1">
                        <p className="text-sm font-medium text-primary">
                            Administração
                        </p>
                        <h1 className="text-2xl font-semibold md:text-3xl">
                            Setores de produção
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Organize cozinha, bar e outras estações de preparo.
                        </p>
                    </div>
                    <FormularioSetor />
                </header>

                {setores.length === 0 ? (
                    <Card className="border-dashed py-12 text-center">
                        <CardContent className="grid justify-items-center gap-2">
                            <ChefHat className="size-7 text-muted-foreground" />
                            <p className="font-medium">
                                Nenhum setor cadastrado
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Comece cadastrando Cozinha e Bar.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {setores.map((setor) => (
                            <SetorCard key={setor.id} setor={setor} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

function SetorCard({ setor }: { setor: SetorProducao }) {
    function excluir() {
        if (window.confirm(`Excluir o setor ${setor.nome}?`)) {
            router.delete(destroy.url(setor.id), { preserveScroll: true });
        }
    }

    return (
        <Card>
            <CardHeader>
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <CardTitle>{setor.nome}</CardTitle>
                        <CardDescription>{setor.descricao}</CardDescription>
                    </div>
                    <Badge variant={setor.ativo ? 'secondary' : 'outline'}>
                        {setor.ativo ? 'Ativo' : 'Inativo'}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent className="grid gap-4">
                <p className="text-sm text-muted-foreground">
                    {setor.itens_cardapio_count} itens vinculados
                </p>
                <div className="flex gap-2">
                    <FormularioSetor setor={setor} />
                    <Button variant="destructive" size="sm" onClick={excluir}>
                        <Trash2 /> Excluir
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}

function FormularioSetor({ setor }: { setor?: SetorProducao }) {
    const editando = setor !== undefined;

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button
                    variant={editando ? 'outline' : 'default'}
                    size={editando ? 'sm' : 'default'}
                >
                    {editando ? <Pencil /> : <Plus />}
                    {editando ? 'Editar' : 'Novo setor'}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {editando ? 'Editar setor' : 'Novo setor de produção'}
                    </DialogTitle>
                    <DialogDescription>
                        Defina o nome, a situação e a ordem de exibição.
                    </DialogDescription>
                </DialogHeader>
                <Form
                    {...(editando ? update.form(setor.id) : store.form())}
                    resetOnSuccess={!editando}
                    className="grid gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <Campo
                                name="nome"
                                label="Nome"
                                defaultValue={setor?.nome}
                                placeholder="Ex.: Cozinha"
                                error={errors.nome}
                            />
                            <div className="grid gap-2">
                                <Label>Descrição</Label>
                                <textarea
                                    name="descricao"
                                    rows={3}
                                    defaultValue={setor?.descricao ?? ''}
                                    className="resize-none rounded-md border border-input bg-transparent px-3 py-2 text-sm"
                                />
                                <InputError message={errors.descricao} />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label>Situação</Label>
                                    <Select
                                        name="ativo"
                                        defaultValue={
                                            setor?.ativo === false ? '0' : '1'
                                        }
                                        required
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">
                                                Ativo
                                            </SelectItem>
                                            <SelectItem value="0">
                                                Inativo
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.ativo} />
                                </div>
                                <Campo
                                    name="ordem"
                                    label="Ordem"
                                    type="number"
                                    min="0"
                                    max="999"
                                    defaultValue={setor?.ordem ?? 0}
                                    error={errors.ordem}
                                />
                            </div>
                            <Button disabled={processing}>
                                {editando
                                    ? 'Salvar alterações'
                                    : 'Cadastrar setor'}
                            </Button>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
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
            <Input name={props.name} required {...props} />
            <InputError message={error} />
        </div>
    );
}

SetoresProducaoIndex.layout = {
    breadcrumbs: [{ title: 'Setores de produção', href: index() }],
};
