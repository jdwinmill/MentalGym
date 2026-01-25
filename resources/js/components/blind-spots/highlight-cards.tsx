import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { AlertTriangle, Trophy, Play, Lightbulb, TrendingUp } from 'lucide-react';
import { cn } from '@/lib/utils';

interface SkillPattern {
    skill: string;
    name: string;
    category: string;
    description: string | null;
    averageScore: number;
    scoreLevel: 'low' | 'mid' | 'high';
    sampleSize: number;
    trend: 'improving' | 'stable' | 'slipping' | 'new';
    suggestion: string | null;
}

interface HighlightCardProps {
    skill: string | null;
    details: SkillPattern | null;
}

function getScoreColor(score: number): string {
    if (score <= 4) return 'text-red-600 dark:text-red-400';
    if (score <= 6) return 'text-amber-600 dark:text-amber-400';
    return 'text-green-600 dark:text-green-400';
}

function getScoreBarColor(score: number): string {
    if (score <= 4) return 'bg-red-500';
    if (score <= 6) return 'bg-amber-500';
    return 'bg-green-500';
}

export function BiggestGapCard({ skill, details }: HighlightCardProps) {
    if (!skill || !details) {
        return (
            <Card className="border-neutral-200 dark:border-neutral-800">
                <CardHeader className="pb-2">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <AlertTriangle className="h-5 w-5 text-neutral-400" />
                        Biggest Gap
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-neutral-500 dark:text-neutral-400">
                        Keep training to identify your biggest gap.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card className="border-red-200 bg-red-50/50 dark:border-red-900 dark:bg-red-950/20">
            <CardHeader className="pb-2">
                <CardTitle className="flex items-center gap-2 text-base">
                    <AlertTriangle className="h-5 w-5 text-red-500" />
                    Biggest Gap
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                <div>
                    <div className="text-2xl font-bold text-red-700 dark:text-red-400">
                        {details.name}
                    </div>
                    {details.description && (
                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                            {details.description}
                        </p>
                    )}
                </div>

                {/* Score display */}
                <div className="rounded-md bg-red-100/50 dark:bg-red-900/20 p-3 space-y-2">
                    <div className="flex items-center justify-between">
                        <span className="text-sm font-medium text-red-800 dark:text-red-300">
                            Average Score
                        </span>
                        <span className={cn('text-lg font-bold', getScoreColor(details.averageScore))}>
                            {details.averageScore}/10
                        </span>
                    </div>
                    <div className="h-2 bg-red-200 dark:bg-red-800/50 rounded-full overflow-hidden">
                        <div
                            className={cn('h-full rounded-full', getScoreBarColor(details.averageScore))}
                            style={{ width: `${details.averageScore * 10}%` }}
                        />
                    </div>
                </div>

                {/* AI Suggestion */}
                {details.suggestion && (
                    <div className="flex items-start gap-2 text-sm">
                        <Lightbulb className="h-4 w-4 mt-0.5 text-amber-500 shrink-0" />
                        <span className="text-neutral-600 dark:text-neutral-400">
                            {details.suggestion}
                        </span>
                    </div>
                )}
            </CardContent>
            <CardFooter className="pt-0">
                <Button asChild size="sm" className="w-full bg-red-600 hover:bg-red-700 text-white">
                    <Link href="/practice-modes">
                        <Play className="h-4 w-4 mr-2" />
                        Train {details.name}
                    </Link>
                </Button>
            </CardFooter>
        </Card>
    );
}

export function BiggestWinCard({ skill, details }: HighlightCardProps) {
    if (!skill || !details) {
        return (
            <Card className="border-neutral-200 dark:border-neutral-800">
                <CardHeader className="pb-2">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <Trophy className="h-5 w-5 text-neutral-400" />
                        Biggest Win
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p className="text-neutral-500 dark:text-neutral-400">
                        Keep training to identify your biggest win.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card className="border-green-200 bg-green-50/50 dark:border-green-900 dark:bg-green-950/20">
            <CardHeader className="pb-2">
                <CardTitle className="flex items-center gap-2 text-base">
                    <Trophy className="h-5 w-5 text-green-500" />
                    Biggest Win
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                <div>
                    <div className="text-2xl font-bold text-green-700 dark:text-green-400">
                        {details.name}
                    </div>
                    {details.description && (
                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                            {details.description}
                        </p>
                    )}
                </div>

                <div className="rounded-md bg-green-100/50 dark:bg-green-900/20 p-3 space-y-2">
                    <div className="flex items-center justify-between">
                        <span className="text-sm font-medium text-green-800 dark:text-green-300">
                            {details.trend === 'improving'
                                ? 'Improving — keep it up!'
                                : 'Your strongest skill'}
                        </span>
                        <span className={cn('text-lg font-bold', getScoreColor(details.averageScore))}>
                            {details.averageScore}/10
                        </span>
                    </div>
                    <div className="h-2 bg-green-200 dark:bg-green-800/50 rounded-full overflow-hidden">
                        <div
                            className={cn('h-full rounded-full', getScoreBarColor(details.averageScore))}
                            style={{ width: `${details.averageScore * 10}%` }}
                        />
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

export function GrowthEdgeCard({ skill, details }: HighlightCardProps) {
    if (!skill || !details) {
        return null;
    }

    return (
        <Card className="border-blue-200 bg-blue-50/50 dark:border-blue-900 dark:bg-blue-950/20">
            <CardHeader className="pb-2">
                <CardTitle className="flex items-center gap-2 text-base">
                    <TrendingUp className="h-5 w-5 text-blue-500" />
                    Growth Edge
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                <div>
                    <div className="text-2xl font-bold text-blue-700 dark:text-blue-400">
                        {details.name}
                    </div>
                    {details.description && (
                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                            {details.description}
                        </p>
                    )}
                </div>

                <div className="rounded-md bg-blue-100/50 dark:bg-blue-900/20 p-3 space-y-2">
                    <div className="flex items-center justify-between">
                        <span className="text-sm font-medium text-blue-800 dark:text-blue-300">
                            Good foundation — room to grow
                        </span>
                        <span className={cn('text-lg font-bold', getScoreColor(details.averageScore))}>
                            {details.averageScore}/10
                        </span>
                    </div>
                    <div className="h-2 bg-blue-200 dark:bg-blue-800/50 rounded-full overflow-hidden">
                        <div
                            className={cn('h-full rounded-full', getScoreBarColor(details.averageScore))}
                            style={{ width: `${details.averageScore * 10}%` }}
                        />
                    </div>
                </div>

                {details.suggestion && (
                    <div className="flex items-start gap-2 text-sm">
                        <Lightbulb className="h-4 w-4 mt-0.5 text-amber-500 shrink-0" />
                        <span className="text-neutral-600 dark:text-neutral-400">
                            <span className="font-medium text-neutral-700 dark:text-neutral-300">Next level: </span>
                            {details.suggestion}
                        </span>
                    </div>
                )}
            </CardContent>
            <CardFooter className="pt-0">
                <Button asChild variant="outline" size="sm" className="w-full border-blue-300 text-blue-700 hover:bg-blue-100 dark:border-blue-700 dark:text-blue-300 dark:hover:bg-blue-900/30">
                    <Link href="/practice-modes">
                        <Play className="h-4 w-4 mr-2" />
                        Sharpen {details.name}
                    </Link>
                </Button>
            </CardFooter>
        </Card>
    );
}
