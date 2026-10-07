import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, ChefHat, CreditCard, Package, RefreshCw, Server, Users, Utensils, BookOpen } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

type Secao = { id: string; titulo: string; descricao: string; url: string; indicadores: Record<string, number | string> };
const icones: Record<string, typeof Utensils> = { salao: Utensils, producao: ChefHat, caixa: CreditCard, estoque: Package, cardapio: BookOpen, setores: ChefHat, usuarios: Users, infraestrutura: Server };

export default function Dashboard({ secoes, atualizadoEm }: { secoes: Secao[]; atualizadoEm: string }) {
    return <>
        <Head title="Visão geral" />
        <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <header className="flex flex-wrap items-center justify-between gap-4">
                <div className="grid gap-1">
                    <p className="text-sm font-medium text-primary">Gestão Restaurante</p>
                    <h1 className="text-3xl font-semibold tracking-tight">Visão geral</h1>
                    <p className="text-sm text-muted-foreground">Acompanhe a operação e acesse as áreas do sistema.</p>
                    <p className="text-xs text-muted-foreground">Atualizado em {new Date(atualizadoEm).toLocaleString('pt-BR')}</p>
                </div>
                <Button variant="outline" onClick={() => router.reload({ only: ['secoes', 'atualizadoEm'] })}><RefreshCw className="size-4" />Atualizar resumo</Button>
            </header>
            <div className="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-3">
                {secoes.map(secao => {
                    const Icone = icones[secao.id] ?? Server;
                    return <Card key={secao.id} className="h-full">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2"><Icone className="size-5 text-primary" />{secao.titulo}</CardTitle>
                            <CardDescription>{secao.descricao}</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4">
                            <dl className="grid gap-3">{Object.entries(secao.indicadores).map(([nome, valor]) => <div key={nome} className="flex items-start justify-between gap-4 border-b pb-2"><dt className="text-sm text-muted-foreground">{nome}</dt><dd className="text-right text-sm font-semibold">{valor}</dd></div>)}</dl>
                            <Button asChild variant="outline" className="w-full">{secao.id === 'infraestrutura' ? <a href={secao.url}>Consultar monitoramento<ArrowRight className="size-4" /></a> : <Link href={secao.url}>Acessar {secao.titulo.toLowerCase()}<ArrowRight className="size-4" /></Link>}</Button>
                        </CardContent>
                    </Card>;
                })}
            </div>
        </div>
    </>;
}

Dashboard.layout = { breadcrumbs: [{ title: 'Visão geral', href: dashboard() }] };
