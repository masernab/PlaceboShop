import { Link, usePage } from '@inertiajs/react';
import { Menu, Search, Sparkles, User as UserIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { LanguageSwitcher } from '@/components/shop/language-switcher';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { UserMenuContent } from '@/components/user-menu-content';
import { useTranslation } from '@/hooks/use-translation';
import { login, register } from '@/routes';

type ShopLayoutProps = {
    children: ReactNode;
};

function ShopLogo() {
    return (
        <Link href="/" className="flex items-center gap-1.5 font-semibold">
            <Sparkles className="size-5 text-pink-500" />
            <span className="text-lg tracking-tight">PlaceboShop</span>
        </Link>
    );
}

export default function ShopLayout({ children }: ShopLayoutProps) {
    const { auth } = usePage().props;
    const { t } = useTranslation();

    const navItems = [
        { label: t('nav.home'), href: '/' },
        { label: t('nav.shop'), href: '/products' },
    ];

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <header className="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
                <div className="mx-auto flex h-16 w-full max-w-7xl items-center gap-4 px-4 sm:px-6">
                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="md:hidden"
                                aria-label={t('nav.menu')}
                            >
                                <Menu />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" className="w-72">
                            <SheetHeader>
                                <SheetTitle>
                                    <ShopLogo />
                                </SheetTitle>
                            </SheetHeader>
                            <nav className="flex flex-col gap-1 px-4">
                                {navItems.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className="rounded-md px-3 py-2 text-sm font-medium hover:bg-accent"
                                    >
                                        {item.label}
                                    </Link>
                                ))}
                                {!auth.user && (
                                    <>
                                        <Link
                                            href={login()}
                                            className="rounded-md px-3 py-2 text-sm font-medium hover:bg-accent"
                                        >
                                            {t('nav.login')}
                                        </Link>
                                        <Link
                                            href={register()}
                                            className="rounded-md px-3 py-2 text-sm font-medium hover:bg-accent"
                                        >
                                            {t('nav.register')}
                                        </Link>
                                    </>
                                )}
                            </nav>
                        </SheetContent>
                    </Sheet>

                    <ShopLogo />

                    <nav className="hidden items-center gap-1 md:flex">
                        {navItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className="rounded-md px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>

                    <div className="ml-auto flex items-center gap-1.5">
                        <form
                            action="/products"
                            method="get"
                            className="relative hidden lg:block"
                        >
                            <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                name="q"
                                placeholder={t('nav.search_placeholder')}
                                className="w-56 pl-8"
                            />
                        </form>

                        <LanguageSwitcher />

                        {auth.user ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label={auth.user.name}
                                    >
                                        <UserIcon />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    className="w-56"
                                >
                                    <UserMenuContent user={auth.user} />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <div className="hidden items-center gap-1.5 md:flex">
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href={login()}>{t('nav.login')}</Link>
                                </Button>
                                <Button size="sm" asChild>
                                    <Link href={register()}>
                                        {t('nav.register')}
                                    </Link>
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            </header>

            <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6">
                {children}
            </main>

            <footer className="border-t">
                <div className="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-8 text-sm text-muted-foreground sm:px-6">
                    <p className="font-medium text-foreground">
                        {t('footer.tagline')}
                    </p>
                    <p className="max-w-2xl">{t('footer.disclaimer')}</p>
                </div>
            </footer>
        </div>
    );
}
