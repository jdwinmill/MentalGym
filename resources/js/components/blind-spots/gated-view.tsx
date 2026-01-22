import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { Lock, Target } from 'lucide-react';

interface GatedViewProps {
    blindSpotCount: number;
    totalSessions: number;
    hasEnoughData: boolean;
    sessionsUntilInsights: number;
    gateReason: string | null;
}

export function GatedView({
    blindSpotCount,
    totalSessions,
    hasEnoughData,
    sessionsUntilInsights,
}: GatedViewProps) {
    if (!hasEnoughData) {
        return (
            <div className="flex flex-1 items-center justify-center">
                <Card className="max-w-md text-center">
                    <CardContent className="pt-8 pb-8">
                        <Target className="mx-auto h-12 w-12 text-neutral-400" />
                        <h2 className="mt-4 text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                            Not Enough Data Yet
                        </h2>
                        <p className="mt-2 text-neutral-500 dark:text-neutral-400">
                            Complete {sessionsUntilInsights} more {sessionsUntilInsights === 1 ? 'session' : 'sessions'} to unlock blind spot analysis.
                        </p>
                        <p className="mt-4 text-sm text-neutral-400">
                            You've completed {totalSessions} of 5 required sessions.
                        </p>
                        <Button asChild className="mt-6">
                            <Link href="/practice-modes">Start Training</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        );
    }

    return (
        <div className="flex flex-1 items-center justify-center">
            <Card className="max-w-lg border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 dark:border-amber-900 dark:from-amber-950/20 dark:to-orange-950/20">
                <CardContent className="pt-8 pb-8 text-center">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <Lock className="h-8 w-8 text-amber-600 dark:text-amber-400" />
                    </div>

                    <h2 className="mt-6 text-2xl font-bold text-amber-700 dark:text-amber-300">
                        {blindSpotCount} Blind {blindSpotCount === 1 ? 'Spot' : 'Spots'} Identified
                    </h2>

                    <p className="mt-3 text-neutral-600 dark:text-neutral-400">
                        You've completed {totalSessions} sessions. We've found patterns in your responses that could be holding you back.
                    </p>

                    <div className="mt-6 rounded-lg border border-dashed border-amber-300 bg-amber-100/50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                        <div className="space-y-2">
                            {Array.from({ length: Math.min(blindSpotCount, 5) }).map((_, i) => (
                                <div
                                    key={i}
                                    className="h-3 rounded bg-amber-200/70 dark:bg-amber-800/50"
                                    style={{ width: `${90 - i * 10}%` }}
                                />
                            ))}
                        </div>
                    </div>

                    <p className="mt-6 text-sm text-neutral-500 dark:text-neutral-400">
                        Pro members see exactly what these patterns are, where they show up, and how to fix them.
                    </p>

                    <Button asChild className="mt-6 w-full bg-amber-600 hover:bg-amber-700">
                        <Link href="/settings/subscription">
                            Unlock Blind Spots — Upgrade to Pro
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}
