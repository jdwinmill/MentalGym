import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

interface ModeProgress {
    mode: string;
    slug: string | null;
    level: number;
    exchanges: number;
    sessions: number;
    last_trained_at: string | null;
}

interface UsageData {
    today: {
        exchanges: number;
        sessions: number;
    };
    weekly: {
        exchanges: number;
        sessions: number;
    };
    monthly: {
        exchanges: number;
        sessions: number;
    };
    allTime: {
        exchanges: number;
        sessions: number;
    };
    modeProgress: ModeProgress[];
}

interface UsageProps {
    usage: UsageData;
    limits: {
        monthly_drills: number | null;
        daily_exchanges: number | null;
        max_level: number;
    };
    streak: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usage',
        href: '/settings/usage',
    },
];

function StatCard({
    title,
    value,
    subtitle,
}: {
    title: string;
    value: string | number;
    subtitle?: string;
}) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{value}</div>
                {subtitle && (
                    <p className="text-xs text-muted-foreground">{subtitle}</p>
                )}
            </CardContent>
        </Card>
    );
}

export default function Usage({ usage, limits, streak }: UsageProps) {
    // Determine if user has monthly or daily limits
    const hasMonthlyLimit = limits.monthly_drills !== null;
    const limitValue = hasMonthlyLimit ? limits.monthly_drills! : limits.daily_exchanges!;
    const currentUsage = hasMonthlyLimit ? usage.monthly.exchanges : usage.today.exchanges;
    const limitLabel = hasMonthlyLimit ? 'Drills This Month' : 'Exchanges Today';

    const usagePercentage = Math.min(100, (currentUsage / limitValue) * 100);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Usage" />

            <h1 className="sr-only">Usage Statistics</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Usage"
                        description="Track your training activity and progress"
                    />

                    {/* Usage Limit Progress */}
                    <div className="space-y-4">
                        <h3 className="text-sm font-medium">{hasMonthlyLimit ? 'This Month' : 'Today'}</h3>
                        <div className="space-y-2">
                            <div className="flex justify-between text-sm">
                                <span>{limitLabel}</span>
                                <span>
                                    {currentUsage} / {limitValue}
                                </span>
                            </div>
                            <Progress value={usagePercentage} />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <StatCard
                                title="Sessions"
                                value={hasMonthlyLimit ? usage.monthly.sessions : usage.today.sessions}
                            />
                            <StatCard title="Streak" value={`${streak} days`} />
                        </div>
                    </div>

                    {/* Weekly Stats */}
                    <div className="space-y-4">
                        <h3 className="text-sm font-medium">This Week</h3>
                        <div className="grid grid-cols-2 gap-4">
                            <StatCard
                                title="Exchanges"
                                value={usage.weekly.exchanges}
                            />
                            <StatCard
                                title="Sessions"
                                value={usage.weekly.sessions}
                            />
                        </div>
                    </div>

                    {/* All-Time Stats */}
                    <div className="space-y-4">
                        <h3 className="text-sm font-medium">All Time</h3>
                        <div className="grid grid-cols-2 gap-4">
                            <StatCard
                                title="Exchanges"
                                value={usage.allTime.exchanges}
                            />
                            <StatCard
                                title="Sessions"
                                value={usage.allTime.sessions}
                            />
                        </div>
                    </div>

                    {/* Progress by Mode */}
                    {usage.modeProgress.length > 0 && (
                        <div className="space-y-4">
                            <h3 className="text-sm font-medium">
                                Progress by Mode
                            </h3>
                            <div className="space-y-3">
                                {usage.modeProgress.map((mode, index) => (
                                    <Card key={index}>
                                        <CardContent className="flex items-center justify-between py-4">
                                            <div>
                                                <p className="font-medium">
                                                    {mode.mode}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {mode.exchanges} exchanges
                                                    {mode.last_trained_at &&
                                                        ` · ${mode.last_trained_at}`}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-lg font-bold">
                                                    Level {mode.level}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    / {limits.max_level}
                                                </p>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
