import { Link } from '@inertiajs/react';
import {
    LayoutGrid,
    Package,
    Shapes,
    ShoppingBag,
    Sparkles,
    Store,
    TicketPercent,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
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
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as couponsIndex } from '@/routes/admin/coupons';
import { index as ordersIndex } from '@/routes/admin/orders';
import { index as productsIndex } from '@/routes/admin/products';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: adminDashboard(), icon: LayoutGrid },
    { title: 'Products', href: productsIndex(), icon: Package },
    { title: 'Categories', href: categoriesIndex(), icon: Shapes },
    { title: 'Orders', href: ordersIndex(), icon: ShoppingBag },
    { title: 'Coupons', href: couponsIndex(), icon: TicketPercent },
];

const footerNavItems: NavItem[] = [
    { title: 'Back to shop', href: '/', icon: Store },
];

export default function AdminLayout({ children }: { children: ReactNode }) {
    return (
        <AppShell variant="sidebar">
            <Sidebar collapsible="icon" variant="inset">
                <SidebarHeader>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton size="lg" asChild>
                                <Link href={adminDashboard()} prefetch>
                                    <Sparkles className="size-5 text-pink-500" />
                                    <span className="font-semibold tracking-tight">
                                        PlaceboShop Admin
                                    </span>
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
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={[]} />
                <div className="flex flex-1 flex-col gap-6 p-4">{children}</div>
            </AppContent>
        </AppShell>
    );
}
