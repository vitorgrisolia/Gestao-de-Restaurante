import { Link, usePage } from '@inertiajs/react';
import {
    ChefHat,
    CreditCard,
    LayoutDashboard,
    ListPlus,
    UserRoundCog,
    Utensils,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as cardapio } from '@/routes/cardapio';
import { index as caixas } from '@/routes/caixas';
import { index as producao } from '@/routes/producao';
import { index as setoresProducao } from '@/routes/setores-producao';
import { index as salao } from '@/routes/salao';
import { index as usuarios } from '@/routes/usuarios';
import type { Auth, NavItem } from '@/types';

const operationNavItems: NavItem[] = [
    {
        title: 'Visão geral',
        href: dashboard(),
        icon: LayoutDashboard,
    },
    {
        title: 'Salão e mesas',
        href: salao(),
        icon: Utensils,
    },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const mainNavItems = [...operationNavItems];

    if (['proprietario', 'gerente', 'cozinha'].includes(auth.user.papel)) {
        mainNavItems.push({
            title: 'Painel de produção',
            href: producao(),
            icon: ChefHat,
        });
    }

    if (['proprietario', 'gerente', 'caixa'].includes(auth.user.papel)) {
        mainNavItems.push({ title: 'Caixa', href: caixas(), icon: CreditCard });
    }

    if (auth.user.papel === 'proprietario') {
        mainNavItems.push(
            {
                title: 'Cadastro do cardápio',
                href: cardapio(),
                icon: ListPlus,
            },
            {
                title: 'Setores de produção',
                href: setoresProducao(),
                icon: ChefHat,
            },
            {
                title: 'Cadastro de usuários',
                href: usuarios(),
                icon: UserRoundCog,
            },
        );
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
