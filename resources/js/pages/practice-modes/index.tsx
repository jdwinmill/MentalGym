import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type PracticeModesIndexProps, type PracticeMode } from '@/types/training';
import { Head, Link } from '@inertiajs/react';
import { Dumbbell, Lock, Play, RotateCcw } from 'lucide-react';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Practice',
        href: '/practice-modes',
    },
];

function getLevelLabel(level: number): string {
    const labels: Record<number, string> = {
        1: 'Beginner',
        2: 'Novice',
        3: 'Intermediate',
        4: 'Advanced',
        5: 'Expert',
    };
    return labels[level] || `Level ${level}`;
}

function getLevelColor(level: number): string {
    const colors: Record<number, string> = {
        1: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        2: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        3: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        4: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        5: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
    };
    return colors[level] || 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300';
}

function ModeCard({ mode }: { mode: PracticeMode }) {
    const currentLevel = mode.progress?.current_level ?? 1;
    const hasProgress = mode.progress !== null;

    return (
        <Card className="flex flex-col h-full transition-shadow hover:shadow-md">
            <CardHeader className="pb-3">
                <div className="flex items-start justify-between gap-2">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                            <Dumbbell className="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <div>
                            <h3 className="font-semibold text-neutral-900 dark:text-neutral-100">
                                {mode.name}
                            </h3>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                {mode.tagline}
                            </p>
                        </div>
                    </div>
                    {!mode.can_access && mode.required_plan && (
                        <Badge variant="outline" className="shrink-0 gap-1 text-amber-600 border-amber-300 dark:text-amber-400 dark:border-amber-700">
                            <Lock className="h-3 w-3" />
                            {mode.required_plan}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="flex-1 pt-0">
                <div className="flex flex-wrap gap-1.5 mb-4">
                    {mode.tags.map((tag) => (
                        <Badge
                            key={tag.id}
                            variant="secondary"
                            className="text-xs"
                            style={{
                                backgroundColor: `${tag.color}20`,
                                color: tag.color,
                            }}
                        >
                            {tag.name}
                        </Badge>
                    ))}
                </div>

                {hasProgress && (
                    <div className="space-y-2">
                        <div className="flex items-center justify-between text-sm">
                            <span className="text-neutral-500 dark:text-neutral-400">Your Level</span>
                            <Badge className={getLevelColor(currentLevel)}>
                                {getLevelLabel(currentLevel)}
                            </Badge>
                        </div>
                        <div className="flex items-center justify-between text-sm text-neutral-500 dark:text-neutral-400">
                            <span>Total Exchanges</span>
                            <span className="font-medium text-neutral-700 dark:text-neutral-300">
                                {mode.progress?.total_exchanges ?? 0}
                            </span>
                        </div>
                    </div>
                )}

                {!hasProgress && (
                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                        Start training to track your progress
                    </p>
                )}
            </CardContent>

            <CardFooter className="pt-0">
                {mode.can_access ? (
                    <Button asChild>
                        <Link href={`/practice-modes/${mode.slug}/train`}>
                            {mode.has_active_session ? (
                                <>
                                    <RotateCcw className="h-4 w-4 mr-2" />
                                    Continue Training
                                </>
                            ) : (
                                <>
                                    <Play className="h-4 w-4 mr-2" />
                                    Begin Training
                                </>
                            )}
                        </Link>
                    </Button>
                ) : (
                    <Button variant="outline" disabled>
                        <Lock className="h-4 w-4 mr-2" />
                        Upgrade to Access
                    </Button>
                )}
            </CardFooter>
        </Card>
    );
}

export default function PracticeModesIndex({ modes }: PracticeModesIndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Practice Modes" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                        Practice Modes
                    </h1>
                    <p className="mt-1 text-neutral-500 dark:text-neutral-400">
                        Choose a mode to begin your training session
                    </p>
                </div>

                {modes.length === 0 ? (
                    <div className="flex flex-1 items-center justify-center">
                        <div className="text-center">
                            <Dumbbell className="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
                            <h2 className="mt-4 text-lg font-semibold text-neutral-700 dark:text-neutral-300">
                                No Practice Modes Available
                            </h2>
                            <p className="mt-2 text-neutral-500 dark:text-neutral-400">
                                Check back soon for new training modes.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {modes.map((mode) => (
                            <ModeCard key={mode.id} mode={mode} />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
