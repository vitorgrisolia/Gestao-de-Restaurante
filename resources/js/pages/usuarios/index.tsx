import { Form, Head } from '@inertiajs/react';
import { ShieldCheck, UserPlus, Users } from 'lucide-react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    index as usuarios,
    store as cadastrarUsuario,
} from '@/routes/usuarios';

type Usuario = {
    id: number;
    name: string;
    email: string;
    papel: string;
    email_verified_at: string | null;
    created_at: string;
};

type Papel = { valor: string; nome: string };

type Props = {
    usuarios: Usuario[];
    papeis: Papel[];
};

export default function UsuariosIndex({
    usuarios: listaUsuarios,
    papeis,
}: Props) {
    const nomesPapeis = Object.fromEntries(
        papeis.map((papel) => [papel.valor, papel.nome]),
    );

    return (
        <>
            <Head title="Cadastro de usuários" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="grid gap-1">
                    <p className="text-sm font-medium text-primary">
                        Administração
                    </p>
                    <h1 className="text-2xl font-semibold tracking-tight md:text-3xl">
                        Cadastro de usuários
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Crie acessos para a equipe e defina a função de cada
                        pessoa.
                    </p>
                </header>

                <div className="grid items-start gap-6 xl:grid-cols-[24rem_minmax(0,1fr)]">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UserPlus /> Novo usuário
                            </CardTitle>
                            <CardDescription>
                                A conta ficará pronta para entrar imediatamente.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...cadastrarUsuario.form()}
                                resetOnSuccess={[
                                    'name',
                                    'email',
                                    'password',
                                    'password_confirmation',
                                ]}
                                className="grid gap-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="usuario-nome">
                                                Nome completo
                                            </Label>
                                            <Input
                                                id="usuario-nome"
                                                name="name"
                                                required
                                                autoComplete="name"
                                                placeholder="Nome do funcionário"
                                            />
                                            <InputError message={errors.name} />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="usuario-email">
                                                E-mail
                                            </Label>
                                            <Input
                                                id="usuario-email"
                                                name="email"
                                                type="email"
                                                required
                                                autoComplete="email"
                                                placeholder="nome@restaurante.com"
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="usuario-papel">
                                                Papel de acesso
                                            </Label>
                                            <Select name="papel" required>
                                                <SelectTrigger
                                                    id="usuario-papel"
                                                    className="w-full"
                                                >
                                                    <SelectValue placeholder="Selecione" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {papeis.map((papel) => (
                                                        <SelectItem
                                                            key={papel.valor}
                                                            value={papel.valor}
                                                        >
                                                            {papel.nome}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.papel}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="usuario-senha">
                                                Senha inicial
                                            </Label>
                                            <PasswordInput
                                                id="usuario-senha"
                                                name="password"
                                                required
                                                autoComplete="new-password"
                                                placeholder="Mínimo de 8 caracteres"
                                            />
                                            <InputError
                                                message={errors.password}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="usuario-confirmacao">
                                                Confirmar senha
                                            </Label>
                                            <PasswordInput
                                                id="usuario-confirmacao"
                                                name="password_confirmation"
                                                required
                                                autoComplete="new-password"
                                                placeholder="Repita a senha"
                                            />
                                            <InputError
                                                message={
                                                    errors.password_confirmation
                                                }
                                            />
                                        </div>
                                        <Button disabled={processing}>
                                            Cadastrar usuário
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    <section className="grid gap-3">
                        <div className="flex items-center justify-between gap-3">
                            <div>
                                <h2 className="text-lg font-semibold">
                                    Equipe cadastrada
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    {listaUsuarios.length} usuários com acesso
                                    ao sistema.
                                </p>
                            </div>
                            <div className="rounded-lg bg-muted p-2">
                                <Users className="size-5" />
                            </div>
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            {listaUsuarios.map((usuario) => (
                                <Card key={usuario.id} className="gap-3 py-4">
                                    <CardHeader className="px-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <CardTitle>
                                                    {usuario.name}
                                                </CardTitle>
                                                <CardDescription>
                                                    {usuario.email}
                                                </CardDescription>
                                            </div>
                                            <Badge variant="secondary">
                                                {nomesPapeis[usuario.papel] ??
                                                    usuario.papel}
                                            </Badge>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="flex items-center gap-2 px-4 text-xs text-muted-foreground">
                                        <ShieldCheck className="size-4 text-emerald-500" />
                                        Conta verificada e liberada
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}

UsuariosIndex.layout = {
    breadcrumbs: [{ title: 'Cadastro de usuários', href: usuarios() }],
};
