import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Play, Lightbulb } from 'lucide-react';
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

interface PatternDetailsProps {
    blindSpots: SkillPattern[];
    improving: SkillPattern[];
    stable: SkillPattern[];
    slipping: SkillPattern[];
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

function PatternSection({
    pattern,
    type,
    defaultOpen = false,
}: {
    pattern: SkillPattern;
    type: 'blind-spot' | 'improving' | 'stable' | 'slipping';
    defaultOpen?: boolean;
}) {
    const [isOpen, setIsOpen] = useState(defaultOpen);

    const typeConfig = {
        'blind-spot': {
            label: 'Needs Work',
            badgeClass: 'bg-red-500 text-white',
            borderClass: 'border-red-200 bg-red-50/50 dark:border-red-900 dark:bg-red-950/20',
        },
        improving: {
            label: 'Improving',
            badgeClass: 'bg-green-500 text-white',
            borderClass: 'border-green-200 bg-green-50/50 dark:border-green-900 dark:bg-green-950/20',
        },
        stable: {
            label: 'Stable',
            badgeClass: 'bg-neutral-500 text-white',
            borderClass: 'border-neutral-200 dark:border-neutral-800',
        },
        slipping: {
            label: 'Slipping',
            badgeClass: 'bg-amber-500 text-white',
            borderClass: 'border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20',
        },
    };

    const config = typeConfig[type];

    return (
        <div className={cn('rounded-lg border overflow-hidden', config.borderClass)}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="w-full flex items-center gap-3 p-4 text-left hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
            >
                {isOpen ? (
                    <ChevronDown className="h-4 w-4 text-neutral-500 shrink-0" />
                ) : (
                    <ChevronRight className="h-4 w-4 text-neutral-500 shrink-0" />
                )}
                <div className="flex-1 min-w-0">
                    <span className="font-semibold text-neutral-900 dark:text-neutral-100">
                        {pattern.name}
                    </span>
                    {pattern.description && (
                        <p className="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                            {pattern.description}
                        </p>
                    )}
                </div>
                <div className="flex items-center gap-2 shrink-0">
                    <span className={cn('text-sm font-medium', getScoreColor(pattern.averageScore))}>
                        {pattern.averageScore}/10
                    </span>
                    <Badge className={cn('shrink-0', config.badgeClass)}>{config.label}</Badge>
                </div>
            </button>

            {isOpen && (
                <div className="px-4 pb-4 pl-11 space-y-4">
                    {/* Score bar visualization */}
                    <div className="space-y-1.5">
                        <div className="flex items-center justify-between text-xs">
                            <span className="text-neutral-500">Average Score</span>
                            <span className={cn('font-medium', getScoreColor(pattern.averageScore))}>
                                {pattern.averageScore}/10
                            </span>
                        </div>
                        <div className="h-2 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                            <div
                                className={cn('h-full rounded-full transition-all', getScoreBarColor(pattern.averageScore))}
                                style={{ width: `${pattern.averageScore * 10}%` }}
                            />
                        </div>
                    </div>

                    {/* Trend indicator */}
                    {pattern.trend !== 'new' && (
                        <p className={cn(
                            'text-sm',
                            pattern.trend === 'improving' && 'text-green-600 dark:text-green-400',
                            pattern.trend === 'slipping' && 'text-amber-600 dark:text-amber-400',
                            pattern.trend === 'stable' && 'text-neutral-500 dark:text-neutral-400',
                        )}>
                            {pattern.trend === 'improving' && '↗ Trending upward recently'}
                            {pattern.trend === 'slipping' && '↘ Trending downward recently'}
                            {pattern.trend === 'stable' && '→ Holding steady'}
                        </p>
                    )}

                    {/* AI Suggestion */}
                    {pattern.suggestion && (type === 'blind-spot' || type === 'slipping') && (
                        <div className="space-y-2">
                            <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 flex items-center gap-1">
                                <Lightbulb className="h-3.5 w-3.5 text-amber-500" />
                                Suggestion:
                            </p>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400 pl-4 border-l-2 border-amber-300 dark:border-amber-700">
                                {pattern.suggestion}
                            </p>
                        </div>
                    )}

                    {/* Category badge */}
                    <div className="flex items-center gap-2">
                        <span className="text-xs text-neutral-400">Category:</span>
                        <Badge variant="outline" className="text-xs capitalize">
                            {pattern.category.replace('_', ' ')}
                        </Badge>
                    </div>

                    {/* Sample size */}
                    <p className="text-xs text-neutral-400">
                        Based on {pattern.sampleSize} responses
                    </p>

                    {/* Train button */}
                    {(type === 'blind-spot' || type === 'slipping') && (
                        <Button asChild size="sm" className={cn(
                            type === 'blind-spot' && 'bg-red-600 hover:bg-red-700 text-white',
                            type === 'slipping' && 'bg-amber-600 hover:bg-amber-700 text-white'
                        )}>
                            <Link href="/practice-modes">
                                <Play className="h-4 w-4 mr-2" />
                                Train {pattern.name}
                            </Link>
                        </Button>
                    )}
                </div>
            )}
        </div>
    );
}

export function PatternDetails({ blindSpots, improving, stable, slipping }: PatternDetailsProps) {
    const hasPatterns = blindSpots.length > 0 || improving.length > 0 || stable.length > 0 || slipping.length > 0;

    if (!hasPatterns) {
        return null;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-lg">All Skills</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                {blindSpots.map((pattern) => (
                    <PatternSection
                        key={pattern.skill}
                        pattern={pattern}
                        type="blind-spot"
                        defaultOpen={true}
                    />
                ))}
                {slipping.map((pattern) => (
                    <PatternSection key={pattern.skill} pattern={pattern} type="slipping" defaultOpen={true} />
                ))}
                {improving.map((pattern) => (
                    <PatternSection key={pattern.skill} pattern={pattern} type="improving" />
                ))}
                {stable.map((pattern) => (
                    <PatternSection key={pattern.skill} pattern={pattern} type="stable" />
                ))}
            </CardContent>
        </Card>
    );
}
