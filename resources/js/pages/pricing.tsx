import { PublicHeader } from '@/components/public-header';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Check, Lock } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface PricingFeature {
    text: string;
    locked?: boolean;
}

interface PricingTier {
    name: string;
    price: string;
    tagline: string;
    features: PricingFeature[];
    cta: string;
    href: string;
    highlighted?: boolean;
}

export default function Pricing() {
    const { auth } = usePage<SharedData>().props;
    const isLoggedIn = !!auth.user;
    const currentPlan = auth.plan ?? 'free';

    const tiers: PricingTier[] = [
        {
            name: 'FREE',
            price: '$0',
            tagline: 'Enough to know if this is real. Not enough to fix anything.',
            features: [
                { text: '15 exchanges per day' },
                { text: 'All practice modes' },
                { text: 'Blind spots detected (details locked)', locked: true },
            ],
            cta: isLoggedIn && currentPlan === 'free' ? 'Current Plan' : 'Start Free',
            href: isLoggedIn ? '#' : '/register',
        },
        {
            name: 'PRO',
            price: '$30/mo',
            tagline: 'The full system. Train daily. Track patterns. Actually improve.',
            features: [
                { text: '100 exchanges per day' },
                { text: 'All practice modes' },
                { text: 'Full Blind Spots dashboard' },
                { text: 'Weekly reports on what you\'re still getting wrong' },
            ],
            cta: isLoggedIn
                ? currentPlan === 'pro' || currentPlan === 'unlimited'
                    ? 'Current Plan'
                    : 'Upgrade'
                : 'Go Pro',
            href: isLoggedIn
                ? currentPlan === 'pro' || currentPlan === 'unlimited'
                    ? '#'
                    : '/settings/usage'
                : '/register?plan=pro',
            highlighted: true,
        },
    ];

    const isCurrentPlan = (tier: PricingTier) => {
        if (!isLoggedIn) return false;
        if (tier.name === 'FREE' && currentPlan === 'free') return true;
        if (tier.name === 'PRO' && (currentPlan === 'pro' || currentPlan === 'unlimited')) return true;
        return false;
    };

    return (
        <>
            <Head title="Pricing" />

            <div className="flex min-h-svh flex-col bg-background">
                <PublicHeader />

                {/* Main Content */}
                <main className="flex-1">
                    <div className="mx-auto max-w-4xl px-6 py-16 md:py-24">
                        {/* Headline */}
                        <div className="mb-12 text-center md:mb-16">
                            <h1 className="text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                                Pick your level of commitment.
                            </h1>
                            <p className="mt-4 text-lg text-muted-foreground">
                                No tiers to decode. No features hidden in fine print. Train or don't.
                            </p>
                        </div>

                        {/* Pricing Cards */}
                        <div className="grid gap-6 md:grid-cols-2 md:gap-8">
                            {tiers.map((tier) => {
                                const isCurrent = isCurrentPlan(tier);
                                const isDeemphasized = isLoggedIn && !isCurrent && !tier.highlighted;

                                return (
                                    <Card
                                        key={tier.name}
                                        className={cn(
                                            'relative flex flex-col',
                                            tier.highlighted && 'border-primary/50 shadow-md',
                                            isDeemphasized && 'opacity-60'
                                        )}
                                    >
                                        {isCurrent && (
                                            <Badge
                                                className="absolute -top-3 left-6"
                                                variant={tier.highlighted ? 'default' : 'secondary'}
                                            >
                                                Current Plan
                                            </Badge>
                                        )}

                                        <CardHeader className="pb-2">
                                            <div className="flex items-baseline gap-3">
                                                <span className="text-sm font-semibold tracking-wider text-muted-foreground">
                                                    {tier.name}
                                                </span>
                                                <span className="text-3xl font-bold text-foreground">
                                                    {tier.price}
                                                </span>
                                            </div>
                                            <p className="mt-2 text-sm text-muted-foreground">
                                                {tier.tagline}
                                            </p>
                                        </CardHeader>

                                        <CardContent className="flex-1">
                                            <ul className="space-y-3">
                                                {tier.features.map((feature, index) => (
                                                    <li key={index} className="flex items-start gap-3">
                                                        {feature.locked ? (
                                                            <Lock className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                                                        ) : (
                                                            <Check className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                                                        )}
                                                        <span
                                                            className={cn(
                                                                'text-sm',
                                                                feature.locked
                                                                    ? 'text-muted-foreground'
                                                                    : 'text-foreground'
                                                            )}
                                                        >
                                                            {feature.text}
                                                        </span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </CardContent>

                                        <CardFooter>
                                            {isCurrent ? (
                                                <Button
                                                    className="w-full"
                                                    variant="secondary"
                                                    disabled
                                                >
                                                    {tier.cta}
                                                </Button>
                                            ) : (
                                                <Button
                                                    asChild
                                                    className="w-full"
                                                    variant={tier.highlighted ? 'default' : 'outline'}
                                                >
                                                    <Link href={tier.href}>{tier.cta}</Link>
                                                </Button>
                                            )}
                                        </CardFooter>
                                    </Card>
                                );
                            })}
                        </div>

                        {/* Footer */}
                        <div className="mt-12 space-y-1 text-center text-sm text-muted-foreground">
                            <p>No annual discounts. No enterprise sales calls. Just training.</p>
                            <p>Cancel anytime. Your data will be here when you get back.</p>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
