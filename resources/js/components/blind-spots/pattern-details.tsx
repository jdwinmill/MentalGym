import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Play, Target, Lightbulb } from 'lucide-react';
import { cn } from '@/lib/utils';

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
    failingCriteria: string[];
    sampleSize: number;
    practiceMode: string | null;
    name: string | null;
    description: string | null;
    target: string | null;
    tips: string[];
    failingCriteriaLabels: string[];
    contextBreakdown: ContextBreakdown[];
}

interface PatternDetailsProps {
    blindSpots: SkillPattern[];
    improving: SkillPattern[];
    stable: SkillPattern[];
    slipping: SkillPattern[];
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

    const skillName = pattern.name || pattern.skill.charAt(0).toUpperCase() + pattern.skill.slice(1);
    const needsWork = Math.round(pattern.currentRate * 100);

    const typeConfig = {
        'blind-spot': {
            label: 'Needs Work',
            badgeClass: 'bg-red-500 text-white',
            borderClass: 'border-red-200 bg-red-50/50 dark:border-red-900 dark:bg-red-950/20',
            statLabel: `${needsWork}% need work`,
            statClass: 'text-red-600 dark:text-red-400',
        },
        improving: {
            label: 'Improving',
            badgeClass: 'bg-green-500 text-white',
            borderClass: 'border-green-200 bg-green-50/50 dark:border-green-900 dark:bg-green-950/20',
            statLabel: `${Math.round((1 - pattern.currentRate) * 100)}% success`,
            statClass: 'text-green-600 dark:text-green-400',
        },
        stable: {
            label: 'Stable',
            badgeClass: 'bg-neutral-500 text-white',
            borderClass: 'border-neutral-200 dark:border-neutral-800',
            statLabel: `${Math.round((1 - pattern.currentRate) * 100)}% success`,
            statClass: 'text-neutral-600 dark:text-neutral-400',
        },
        slipping: {
            label: 'Slipping',
            badgeClass: 'bg-amber-500 text-white',
            borderClass: 'border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20',
            statLabel: `${needsWork}% need work`,
            statClass: 'text-amber-600 dark:text-amber-400',
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
                        {skillName}
                    </span>
                    {pattern.description && (
                        <p className="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                            {pattern.description}
                        </p>
                    )}
                </div>
                <span className={cn('text-sm font-medium shrink-0', config.statClass)}>
                    {config.statLabel}
                </span>
                <Badge className={cn('shrink-0', config.badgeClass)}>{config.label}</Badge>
            </button>

            {isOpen && (
                <div className="px-4 pb-4 pl-11 space-y-4">
                    {/* What's happening - specific issues */}
                    {pattern.failingCriteriaLabels.length > 0 && (
                        <div>
                            <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-2">
                                Specific issues detected:
                            </p>
                            <div className="flex flex-wrap gap-2">
                                {pattern.failingCriteriaLabels.map((label, i) => (
                                    <span
                                        key={i}
                                        className={cn(
                                            'inline-block px-2.5 py-1 text-xs rounded-md',
                                            type === 'blind-spot' && 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                            type === 'slipping' && 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
                                            type === 'improving' && 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                                            type === 'stable' && 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300'
                                        )}
                                    >
                                        {label}
                                    </span>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Progress info */}
                    {pattern.baselineRate !== null && pattern.trend === 'improving' && (
                        <p className="text-sm text-green-600 dark:text-green-400">
                            ↗ Improved from {Math.round(pattern.baselineRate * 100)}% to {needsWork}% need-work rate
                        </p>
                    )}
                    {pattern.baselineRate !== null && pattern.trend === 'slipping' && (
                        <p className="text-sm text-amber-600 dark:text-amber-400">
                            ↘ Slipped from {Math.round(pattern.baselineRate * 100)}% to {needsWork}% need-work rate
                        </p>
                    )}

                    {/* Target */}
                    {pattern.target && (type === 'blind-spot' || type === 'slipping') && (
                        <div className="flex items-start gap-2 text-sm bg-neutral-100 dark:bg-neutral-800/50 rounded-md p-3">
                            <Target className="h-4 w-4 mt-0.5 text-neutral-500 shrink-0" />
                            <div>
                                <span className="font-medium text-neutral-700 dark:text-neutral-300">Target: </span>
                                <span className="text-neutral-600 dark:text-neutral-400">{pattern.target}</span>
                            </div>
                        </div>
                    )}

                    {/* Context breakdown - performance by drill type */}
                    {pattern.contextBreakdown && pattern.contextBreakdown.length > 0 && (
                        <div className="space-y-2">
                            <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                Performance by drill:
                            </p>
                            <div className="space-y-1.5">
                                {pattern.contextBreakdown.map((ctx, i) => {
                                    const successRate = Math.round((1 - ctx.rate) * 100);
                                    return (
                                        <div key={i} className="flex items-center gap-2">
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center gap-2">
                                                    <span className="text-sm text-neutral-700 dark:text-neutral-300 truncate">
                                                        {ctx.phase}
                                                    </span>
                                                    <span className="text-xs text-neutral-400">
                                                        ({ctx.total})
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <div className="w-16 h-1.5 bg-neutral-200 dark:bg-neutral-700 rounded-full overflow-hidden">
                                                    <div
                                                        className={cn(
                                                            'h-full rounded-full',
                                                            successRate >= 80 ? 'bg-green-500' :
                                                            successRate >= 60 ? 'bg-blue-500' :
                                                            successRate >= 40 ? 'bg-amber-500' :
                                                            'bg-red-500'
                                                        )}
                                                        style={{ width: `${successRate}%` }}
                                                    />
                                                </div>
                                                <span className={cn(
                                                    'text-xs font-medium w-10 text-right',
                                                    successRate >= 80 ? 'text-green-600 dark:text-green-400' :
                                                    successRate >= 60 ? 'text-blue-600 dark:text-blue-400' :
                                                    successRate >= 40 ? 'text-amber-600 dark:text-amber-400' :
                                                    'text-red-600 dark:text-red-400'
                                                )}>
                                                    {successRate}%
                                                </span>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {/* Tips */}
                    {pattern.tips.length > 0 && (type === 'blind-spot' || type === 'slipping') && (
                        <div className="space-y-2">
                            <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 flex items-center gap-1">
                                <Lightbulb className="h-3.5 w-3.5 text-amber-500" />
                                Quick fixes:
                            </p>
                            <ul className="space-y-1.5">
                                {pattern.tips.map((tip, i) => (
                                    <li key={i} className="text-sm text-neutral-600 dark:text-neutral-400 pl-4 relative before:content-['•'] before:absolute before:left-0 before:text-amber-500">
                                        {tip}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

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
                            <Link href={pattern.practiceMode
                                ? `/practice-modes/${pattern.practiceMode}/train`
                                : '/practice-modes'
                            }>
                                <Play className="h-4 w-4 mr-2" />
                                Train {skillName}
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
