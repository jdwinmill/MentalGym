import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { AlertTriangle, ArrowRight, Lock, TrendingDown, TrendingUp, Minus } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blind Spots', href: '/blind-spots' },
];

interface PrimaryBlindSpot {
    dimensionKey: string;
    label: string;
    category: string;
    description: string | null;
    averageScore: number;
    occurrences: number;
    sessionsWithDimension: number;
    trend: 'improving' | 'stable' | 'slipping' | 'new';
    recentSuggestions: Array<{
        drillName: string | null;
        suggestion: string;
    }>;
    recommendedDrill: {
        id: number;
        name: string;
        practiceMode: {
            id: number;
            name: string;
            slug: string;
        };
    } | null;
}

interface PageData {
    hasEnoughData: boolean;
    totalSessions: number;
    totalResponses: number;
    requiredSessions: number;
    isUnlocked: boolean;
    gateReason: 'insufficient_data' | 'requires_pro' | null;
    primaryBlindSpot: PrimaryBlindSpot | null;
}

interface BlindSpotsIndexProps {
    pageData: PageData;
}

function TrendIcon({ trend }: { trend: string }) {
    switch (trend) {
        case 'improving':
            return <TrendingUp className="h-4 w-4 text-green-500" />;
        case 'slipping':
            return <TrendingDown className="h-4 w-4 text-red-500" />;
        default:
            return <Minus className="h-4 w-4 text-neutral-400" />;
    }
}

function InsufficientDataView({ totalSessions, requiredSessions }: { totalSessions: number; requiredSessions: number }) {
    const progress = (totalSessions / requiredSessions) * 100;
    const remaining = requiredSessions - totalSessions;

    return (
        <div className="flex flex-1 items-center justify-center">
            <Card className="max-w-md">
                <CardHeader className="text-center">
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800">
                        <AlertTriangle className="h-6 w-6 text-neutral-500" />
                    </div>
                    <CardTitle>Not Enough Data Yet</CardTitle>
                    <CardDescription>
                        Complete {remaining} more {remaining === 1 ? 'session' : 'sessions'} to unlock your blind spot analysis.
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div>
                        <div className="mb-2 flex justify-between text-sm">
                            <span className="text-neutral-500">Progress</span>
                            <span className="font-medium">{totalSessions} / {requiredSessions}</span>
                        </div>
                        <Progress value={progress} className="h-2" />
                    </div>
                    <Button asChild className="w-full">
                        <Link href="/practice">Start Training</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}

function LockedView({ totalSessions }: { totalSessions: number }) {
    return (
        <div className="flex flex-1 items-center justify-center">
            <Card className="max-w-md">
                <CardHeader className="text-center">
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <Lock className="h-6 w-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <CardTitle>Upgrade to Pro</CardTitle>
                    <CardDescription>
                        You have {totalSessions} completed sessions. Upgrade to Pro to see your blind spot analysis.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button asChild className="w-full">
                        <Link href="/settings/subscription">Upgrade Now</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}

function NoBlindSpotView() {
    return (
        <div className="flex flex-1 items-center justify-center">
            <Card className="max-w-md">
                <CardHeader className="text-center">
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <TrendingUp className="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <CardTitle>No Blind Spots Detected</CardTitle>
                    <CardDescription>
                        Great work! Keep training to maintain your skills and we'll let you know if any patterns emerge.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button asChild className="w-full">
                        <Link href="/practice">Continue Training</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}

function PrimaryBlindSpotView({ blindSpot }: { blindSpot: PrimaryBlindSpot }) {
    const trendLabel = {
        improving: 'Improving',
        slipping: 'Getting worse',
        stable: 'Stuck',
        new: 'New pattern',
    }[blindSpot.trend];

    return (
        <div className="mx-auto max-w-2xl space-y-6">
            {/* Main Blind Spot Card */}
            <Card className="border-red-200 bg-red-50/50 dark:border-red-900/50 dark:bg-red-950/20">
                <CardHeader>
                    <div className="flex items-start justify-between">
                        <div>
                            <Badge variant="outline" className="mb-2 border-red-300 text-red-700 dark:border-red-800 dark:text-red-400">
                                Primary Blind Spot
                            </Badge>
                            <CardTitle className="text-2xl">{blindSpot.label}</CardTitle>
                            {blindSpot.description && (
                                <CardDescription className="mt-1">
                                    {blindSpot.description}
                                </CardDescription>
                            )}
                        </div>
                        <div className="text-right">
                            <div className="text-3xl font-bold text-red-600 dark:text-red-400">
                                {blindSpot.averageScore.toFixed(1)}
                            </div>
                            <div className="text-sm text-neutral-500">avg score</div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent className="space-y-4">
                    {/* Why it's a blind spot */}
                    <div className="rounded-lg bg-white/60 p-4 dark:bg-neutral-900/40">
                        <h4 className="mb-2 font-medium text-neutral-900 dark:text-neutral-100">
                            Why this is your blind spot
                        </h4>
                        <ul className="space-y-1 text-sm text-neutral-600 dark:text-neutral-400">
                            <li>
                                Scored low <strong>{blindSpot.occurrences} times</strong> across {blindSpot.sessionsWithDimension} different drills
                            </li>
                            <li className="flex items-center gap-1">
                                <TrendIcon trend={blindSpot.trend} />
                                <span>{trendLabel}</span>
                                {blindSpot.trend === 'stable' && (
                                    <span className="text-neutral-500">— you keep hitting this wall</span>
                                )}
                                {blindSpot.trend === 'slipping' && (
                                    <span className="text-neutral-500">— trending in the wrong direction</span>
                                )}
                                {blindSpot.trend === 'new' && (
                                    <span className="text-neutral-500">— just emerged</span>
                                )}
                            </li>
                        </ul>
                    </div>

                    {/* Evidence - Recent Suggestions */}
                    {blindSpot.recentSuggestions.length > 0 && (
                        <div>
                            <h4 className="mb-3 font-medium text-neutral-900 dark:text-neutral-100">
                                Feedback you've received
                            </h4>
                            <div className="space-y-2">
                                {blindSpot.recentSuggestions.map((item, i) => (
                                    <div
                                        key={i}
                                        className="rounded-lg border border-neutral-200 bg-white p-3 text-sm dark:border-neutral-700 dark:bg-neutral-800"
                                    >
                                        {item.drillName && (
                                            <div className="mb-1 text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                                During "{item.drillName}"
                                            </div>
                                        )}
                                        <div className="text-neutral-700 dark:text-neutral-300">
                                            {item.suggestion}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Action - Work on this */}
                    {blindSpot.recommendedDrill && (
                        <div className="flex items-center justify-between rounded-lg bg-white/80 p-4 dark:bg-neutral-900/60">
                            <div>
                                <div className="font-medium text-neutral-900 dark:text-neutral-100">
                                    {blindSpot.recommendedDrill.name}
                                </div>
                                <div className="text-sm text-neutral-500">
                                    {blindSpot.recommendedDrill.practiceMode.name}
                                </div>
                            </div>
                            <Button asChild>
                                <Link href={`/practice-modes/${blindSpot.recommendedDrill.practiceMode.slug}/train`}>
                                    Work on this
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}

export default function BlindSpotsIndex({ pageData }: BlindSpotsIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Blind Spots" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                        Blind Spots
                    </h1>
                    <p className="mt-1 text-neutral-500 dark:text-neutral-400">
                        The one skill pattern holding you back the most
                    </p>
                </div>

                {!pageData.hasEnoughData ? (
                    <InsufficientDataView
                        totalSessions={pageData.totalSessions}
                        requiredSessions={pageData.requiredSessions}
                    />
                ) : !pageData.isUnlocked ? (
                    <LockedView totalSessions={pageData.totalSessions} />
                ) : !pageData.primaryBlindSpot ? (
                    <NoBlindSpotView />
                ) : (
                    <PrimaryBlindSpotView blindSpot={pageData.primaryBlindSpot} />
                )}
            </div>
        </AppLayout>
    );
}
