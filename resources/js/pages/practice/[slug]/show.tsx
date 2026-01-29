import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import {
    type PracticeModeShowProps,
    type ModeDetailDrill,
    type UserPattern,
} from '@/types/training';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Circle,
    Clock,
    Lock,
    Sparkles,
    TrendingUp,
    Target,
    AlertTriangle,
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

function getCategoryBadge(category: UserPattern['category']) {
    switch (category) {
        case 'strength':
            return (
                <Badge className="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <Sparkles className="h-3 w-3 mr-1" />
                    Strength
                </Badge>
            );
        case 'tendency':
            return (
                <Badge className="bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    <TrendingUp className="h-3 w-3 mr-1" />
                    Developing
                </Badge>
            );
        case 'improve':
            return (
                <Badge className="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                    <Target className="h-3 w-3 mr-1" />
                    Focus Area
                </Badge>
            );
    }
}

function DrillItem({
    drill,
    isCompleted,
}: {
    drill: ModeDetailDrill;
    isCompleted: boolean;
}) {
    return (
        <div className="flex items-center justify-between py-2">
            <div className="flex items-center gap-3">
                {isCompleted ? (
                    <CheckCircle2 className="h-5 w-5 text-emerald-500" />
                ) : (
                    <Circle className="h-5 w-5 text-neutral-300 dark:text-neutral-600" />
                )}
                <span
                    className={
                        isCompleted
                            ? 'text-neutral-500 dark:text-neutral-400'
                            : 'text-neutral-900 dark:text-neutral-100'
                    }
                >
                    {drill.name}
                </span>
            </div>
            {drill.timer_seconds && (
                <Badge variant="outline" className="text-xs">
                    <Clock className="h-3 w-3 mr-1" />
                    {drill.timer_seconds}s
                </Badge>
            )}
        </div>
    );
}

export default function PracticeModeShowPage({
    mode,
    drills,
    estimatedMinutes,
    completedDrillCount,
    userPatterns,
    modeDimensions,
    hasPatternHistory,
    userPlan,
    hasActiveSession,
    canAccess,
}: PracticeModeShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Practice', href: '/practice-modes' },
        { title: mode.name, href: `/practice/${mode.slug}` },
    ];

    const getCtaButton = () => {
        if (!canAccess) {
            return (
                <Button asChild size="lg" className="w-full sm:w-auto">
                    <Link href="/pricing">
                        <Lock className="h-4 w-4 mr-2" />
                        Upgrade to Access
                    </Link>
                </Button>
            );
        }

        if (hasActiveSession) {
            return (
                <Button asChild size="lg" className="w-full sm:w-auto">
                    <Link href={`/practice-modes/${mode.slug}/train`}>
                        Jump Back In
                    </Link>
                </Button>
            );
        }

        return (
            <Button asChild size="lg" className="w-full sm:w-auto">
                <Link href={`/practice-modes/${mode.slug}/train`}>
                    I'm Ready
                </Link>
            </Button>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={mode.name} />

            <div className="flex flex-col h-full overflow-hidden">
                {/* Main content area */}
                <div className="flex-1 overflow-y-auto p-4 md:p-6 pb-24">
                    <div className="max-w-2xl mx-auto space-y-6">
                        {/* Header Section */}
                        <div>
                            <Link
                                href="/practice-modes"
                                className="inline-flex items-center text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 mb-4"
                            >
                                <ArrowLeft className="h-4 w-4 mr-1" />
                                Back to Practice Modes
                            </Link>
                            <h1 className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                                {mode.name}
                            </h1>
                            {mode.tagline && (
                                <p className="mt-1 text-neutral-500 dark:text-neutral-400">
                                    {mode.tagline}
                                </p>
                            )}
                            {mode.description && (
                                <p className="mt-3 text-neutral-600 dark:text-neutral-300">
                                    {mode.description}
                                </p>
                            )}
                        </div>

                        {/* Sample Scenario Card */}
                        {mode.sample_scenario && (
                            <Card className="mt-2 border-dashed border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900/50">
                                <CardContent className="pt-4">
                                    <p className="text-neutral-700 dark:text-neutral-300 italic leading-relaxed">
                                        "{mode.sample_scenario}"
                                    </p>
                                </CardContent>
                            </Card>
                        )}

                        {/* Drill Progression Card */}
                        <Card>
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-base font-semibold">
                                        Drill Progression
                                    </CardTitle>
                                    <div className="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
                                        <span>{drills.length} drills</span>
                                        <span className="text-neutral-300 dark:text-neutral-600">
                                            |
                                        </span>
                                        <span className="flex items-center">
                                            <Clock className="h-4 w-4 mr-1" />
                                            ~{estimatedMinutes} min
                                        </span>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                    {drills.map((drill) => (
                                        <DrillItem
                                            key={drill.id}
                                            drill={drill}
                                            isCompleted={
                                                drill.position < completedDrillCount
                                            }
                                        />
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* User Patterns Card - Three States */}
                        {modeDimensions.length > 0 && (
                            <>
                                {/* State 1: Free User - Teaser */}
                                {userPlan === 'free' && (
                                    <Card className="bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900">
                                        <CardHeader className="pb-3">
                                            <div className="flex items-center gap-2">
                                                <CardTitle className="text-base font-semibold text-blue-900 dark:text-blue-100">
                                                    Your Patterns
                                                </CardTitle>
                                                <Badge className="bg-blue-600 text-white text-xs">
                                                    Pro
                                                </Badge>
                                            </div>
                                            <p className="text-sm text-blue-700/70 dark:text-blue-300/70">
                                                See where you're strong and where to focus
                                            </p>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="space-y-2 mb-4">
                                                {modeDimensions.slice(0, 5).map((dim) => (
                                                    <div
                                                        key={dim.key}
                                                        className="text-neutral-700 dark:text-neutral-300"
                                                    >
                                                        {dim.label}
                                                    </div>
                                                ))}
                                            </div>
                                            <Link
                                                href="/pricing"
                                                className="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                            >
                                                Upgrade to track patterns
                                                <ArrowRight className="h-4 w-4 ml-1" />
                                            </Link>
                                        </CardContent>
                                    </Card>
                                )}

                                {/* State 2: Pro User, No History - Empty State */}
                                {userPlan !== 'free' && !hasPatternHistory && (
                                    <Card className="bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900">
                                        <CardHeader className="pb-3">
                                            <CardTitle className="text-base font-semibold text-blue-900 dark:text-blue-100">
                                                Your Patterns
                                            </CardTitle>
                                            <p className="text-sm text-blue-700/70 dark:text-blue-300/70">
                                                Skills this mode targets
                                            </p>
                                        </CardHeader>
                                        <CardContent>
                                            <div className="space-y-2 mb-4">
                                                {modeDimensions.slice(0, 5).map((dim) => (
                                                    <div
                                                        key={dim.key}
                                                        className="text-neutral-700 dark:text-neutral-300"
                                                    >
                                                        {dim.label}
                                                    </div>
                                                ))}
                                            </div>
                                            <p className="text-sm text-blue-600/70 dark:text-blue-400/70">
                                                Complete a session to start tracking your patterns
                                            </p>
                                        </CardContent>
                                    </Card>
                                )}

                                {/* State 3: Pro User, Has History - Full Patterns */}
                                {userPlan !== 'free' &&
                                    hasPatternHistory &&
                                    userPatterns &&
                                    userPatterns.patterns.length > 0 && (
                                        <Card className="bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900">
                                            <CardHeader className="pb-3">
                                                <CardTitle className="text-base font-semibold text-blue-900 dark:text-blue-100">
                                                    Your Patterns
                                                </CardTitle>
                                                <p className="text-sm text-blue-700/70 dark:text-blue-300/70">
                                                    Skills this mode targets based on your history
                                                </p>
                                            </CardHeader>
                                            <CardContent>
                                                <div className="space-y-3">
                                                    {userPatterns.patterns.slice(0, 5).map((pattern) => (
                                                        <div
                                                            key={pattern.dimension_key}
                                                            className="flex items-center justify-between"
                                                        >
                                                            <span className="text-neutral-900 dark:text-neutral-100">
                                                                {pattern.label}
                                                            </span>
                                                            {getCategoryBadge(pattern.category)}
                                                        </div>
                                                    ))}
                                                </div>
                                            </CardContent>
                                        </Card>
                                    )}
                            </>
                        )}

                        {/* Access Warning */}
                        {!canAccess && (
                            <Card className="border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/50">
                                <CardContent className="pt-4">
                                    <div className="flex items-start gap-3">
                                        <AlertTriangle className="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <p className="font-medium text-amber-800 dark:text-amber-200">
                                                Plan Upgrade Required
                                            </p>
                                            <p className="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                                This practice mode requires the{' '}
                                                <span className="font-semibold">
                                                    {mode.required_plan}
                                                </span>{' '}
                                                plan. Upgrade to unlock access.
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>

                {/* CTA Section */}
                <div className="shrink-0 border-t border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 p-4">
                    <div className="max-w-2xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Takes about {estimatedMinutes} minutes
                        </p>
                        {getCtaButton()}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
