import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { AlertTriangle, Trophy, Play, Target, Lightbulb, TrendingUp } from 'lucide-react';

interface ContextBreakdown {
    phase: string;
    rate: number;
    total: number;
    practiceMode: string | null;
}

interface SkillPattern {
    skill: string;
    currentRate: number;
    baselineRate: number | null;
    trend: string;
    primaryIssue: string | null;
    practiceMode: string | null;
    name: string | null;
    description: string | null;
    target: string | null;
    tips: string[];
    failingCriteriaLabels: string[];
    contextBreakdown: ContextBreakdown[];
}

interface HighlightCardProps {
    skill: string | null;
    details: SkillPattern | null;
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

    const skillName = details.name || skill.charAt(0).toUpperCase() + skill.slice(1);
    const needsWork = Math.round(details.currentRate * 100);

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
                        {skillName}
                    </div>
                    {details.description && (
                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                            {details.description}
                        </p>
                    )}
                </div>

                {/* What's happening */}
                <div className="rounded-md bg-red-100/50 dark:bg-red-900/20 p-3">
                    <p className="text-sm font-medium text-red-800 dark:text-red-300">
                        Needs work in {needsWork}% of responses
                    </p>
                    {details.failingCriteriaLabels.length > 0 && (
                        <div className="mt-2 flex flex-wrap gap-1.5">
                            {details.failingCriteriaLabels.slice(0, 3).map((label, i) => (
                                <span
                                    key={i}
                                    className="inline-block px-2 py-0.5 text-xs rounded-full bg-red-200/50 dark:bg-red-800/30 text-red-700 dark:text-red-300"
                                >
                                    {label}
                                </span>
                            ))}
                        </div>
                    )}
                </div>

                {/* The target */}
                {details.target && (
                    <div className="flex items-start gap-2 text-sm">
                        <Target className="h-4 w-4 mt-0.5 text-neutral-500 shrink-0" />
                        <span className="text-neutral-600 dark:text-neutral-400">
                            <span className="font-medium text-neutral-700 dark:text-neutral-300">Target: </span>
                            {details.target}
                        </span>
                    </div>
                )}

                {/* Quick tip */}
                {details.tips.length > 0 && (
                    <div className="flex items-start gap-2 text-sm">
                        <Lightbulb className="h-4 w-4 mt-0.5 text-amber-500 shrink-0" />
                        <span className="text-neutral-600 dark:text-neutral-400">
                            <span className="font-medium text-neutral-700 dark:text-neutral-300">Quick fix: </span>
                            {details.tips[0]}
                        </span>
                    </div>
                )}
            </CardContent>
            <CardFooter className="pt-0">
                <Button asChild size="sm" className="w-full bg-red-600 hover:bg-red-700 text-white">
                    <Link href={details.practiceMode
                        ? `/practice-modes/${details.practiceMode}/train`
                        : '/practice-modes'
                    }>
                        <Play className="h-4 w-4 mr-2" />
                        Train {skillName}
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

    const skillName = details.name || skill.charAt(0).toUpperCase() + skill.slice(1);
    const successRate = Math.round((1 - details.currentRate) * 100);
    const improvement = details.baselineRate
        ? Math.round((details.baselineRate - details.currentRate) * 100)
        : null;

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
                        {skillName}
                    </div>
                    {details.description && (
                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                            {details.description}
                        </p>
                    )}
                </div>

                <div className="rounded-md bg-green-100/50 dark:bg-green-900/20 p-3">
                    <p className="text-sm font-medium text-green-800 dark:text-green-300">
                        {improvement && improvement > 0
                            ? `Improved ${improvement}% recently`
                            : `${successRate}% success rate`}
                    </p>
                    {details.target && (
                        <p className="mt-1 text-xs text-green-700/70 dark:text-green-400/70">
                            You're hitting: {details.target}
                        </p>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

export function GrowthEdgeCard({ skill, details }: HighlightCardProps) {
    if (!skill || !details) {
        return null;
    }

    const skillName = details.name || skill.charAt(0).toUpperCase() + skill.slice(1);
    const needsWork = Math.round(details.currentRate * 100);
    const successRate = 100 - needsWork;

    // Find the worst drill phase for context
    const worstPhase = details.contextBreakdown?.[0];

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
                        {skillName}
                    </div>
                    {details.description && (
                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                            {details.description}
                        </p>
                    )}
                </div>

                <div className="rounded-md bg-blue-100/50 dark:bg-blue-900/20 p-3">
                    <p className="text-sm font-medium text-blue-800 dark:text-blue-300">
                        {successRate}% success — room to grow in {needsWork}%
                    </p>
                    {worstPhase && worstPhase.rate > details.currentRate && (
                        <p className="mt-1 text-xs text-blue-700/70 dark:text-blue-400/70">
                            Focus area: {worstPhase.phase} ({Math.round(worstPhase.rate * 100)}% need work)
                        </p>
                    )}
                </div>

                {/* Context breakdown - where specifically to focus */}
                {details.contextBreakdown && details.contextBreakdown.length > 1 && (
                    <div className="space-y-1.5">
                        <p className="text-xs font-medium text-neutral-500">Performance by drill:</p>
                        {details.contextBreakdown.slice(0, 3).map((ctx, i) => (
                            <div key={i} className="flex items-center gap-2 text-xs">
                                <div className="flex-1 truncate text-neutral-600 dark:text-neutral-400">
                                    {ctx.phase}
                                </div>
                                <div className={`font-medium ${
                                    ctx.rate > 0.3 ? 'text-amber-600 dark:text-amber-400' :
                                    ctx.rate > 0.15 ? 'text-blue-600 dark:text-blue-400' :
                                    'text-green-600 dark:text-green-400'
                                }`}>
                                    {Math.round((1 - ctx.rate) * 100)}%
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {details.tips.length > 0 && (
                    <div className="flex items-start gap-2 text-sm">
                        <Lightbulb className="h-4 w-4 mt-0.5 text-amber-500 shrink-0" />
                        <span className="text-neutral-600 dark:text-neutral-400">
                            <span className="font-medium text-neutral-700 dark:text-neutral-300">Next level: </span>
                            {details.tips[0]}
                        </span>
                    </div>
                )}
            </CardContent>
            <CardFooter className="pt-0">
                <Button asChild variant="outline" size="sm" className="w-full border-blue-300 text-blue-700 hover:bg-blue-100 dark:border-blue-700 dark:text-blue-300 dark:hover:bg-blue-900/30">
                    <Link href={worstPhase?.practiceMode
                        ? `/practice-modes/${worstPhase.practiceMode}/train`
                        : details.practiceMode
                        ? `/practice-modes/${details.practiceMode}/train`
                        : '/practice-modes'
                    }>
                        <Play className="h-4 w-4 mr-2" />
                        Sharpen {skillName}
                    </Link>
                </Button>
            </CardFooter>
        </Card>
    );
}
