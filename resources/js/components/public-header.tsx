import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

import { Button } from '@/components/ui/button';

export function PublicHeader() {
    const { auth } = usePage<SharedData>().props;
    const isLoggedIn = !!auth.user;

    return (
        <header className="border-b border-border">
            <div className="px-4 md:px-6 py-4 max-w-4xl mx-auto flex flex-col min-[400px]:flex-row items-start min-[400px]:items-center min-[400px]:justify-between gap-3 min-[400px]:gap-4 md:gap-6">
                <Link
                    href="/"
                    className="inline-block text-xl md:text-2xl tracking-tight hover:opacity-80 transition-opacity"
                >
                    <span className="font-bold text-foreground">SharpStack</span>
                    <span className="text-muted-foreground font-normal mx-2 hidden md:inline">|</span>
                    <span className="text-muted-foreground font-medium text-base md:text-lg hidden md:inline">Daily Mental Training</span>
                </Link>
                <nav className="flex items-center gap-4 self-end min-[400px]:self-auto">
                    <Link
                        href="/pricing"
                        className="text-sm text-foreground hover:text-primary transition-colors font-medium"
                    >
                        Pricing
                    </Link>
                    {isLoggedIn ? (
                        <Button asChild variant="default" size="sm">
                            <Link href="/dashboard">Dashboard</Link>
                        </Button>
                    ) : (
                        <>
                            <Link
                                href="/login"
                                className="text-sm text-foreground hover:text-primary transition-colors font-medium"
                            >
                                Log In
                            </Link>
                            <Link
                                href="/register"
                                className="px-3 py-1.5 text-sm bg-primary text-primary-foreground rounded-lg font-semibold hover:opacity-90 transition-opacity"
                            >
                                Sign Up
                            </Link>
                        </>
                    )}
                </nav>
            </div>
        </header>
    );
}
