import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Play } from 'lucide-react';
import { cn } from '@/lib/utils';

interface SkillPattern {
    skill: string;
    currentRate: number;
    baselineRate: number | null;
    trend: string;
    primaryIssue: string | null;
    failingCriteria: string[];
    sampleSize: number;
    practiceMode: string | null;
}

interface PatternDetailsProps {
    blindSpots: SkillPattern[];
    improving: SkillPattern[];
    stable: SkillPattern[];
    slipping: SkillPattern[];
}

function formatSkillName(skill: string): string {
    return skill.charAt(0).toUpperCase() + skill.slice(1);
}

function formatCriteria(criteria: string): string {
    const labels: Record<string, string> = {
        hedging: 'Hedging language',
        ran_long: 'Running long',
        filler_phrases: 'Filler phrases',
        structure_followed: 'Missing structure',
        direct_opening: 'Buried lead',
        calm_tone: 'Composure issues',
        apologies: 'Unnecessary apologies',
    };
    return labels[criteria] || criteria;
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
            label: 'Blind Spot',
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
                    <ChevronDown className="h-4 w-4 text-neutral-500" />
                ) : (
                    <ChevronRight className="h-4 w-4 text-neutral-500" />
                )}
                <span className="font-semibold text-neutral-900 dark:text-neutral-100 flex-1">
                    {formatSkillName(pattern.skill)}
                </span>
                <Badge className={config.badgeClass}>{config.label}</Badge>
            </button>

            {isOpen && (
                <div className="px-4 pb-4 pl-11">
                    <ul className="space-y-2 text-sm text-neutral-600 dark:text-neutral-400">
                        {pattern.primaryIssue && (
                            <li>
                                {formatCriteria(pattern.primaryIssue)} detected in{' '}
                                {Math.round(pattern.currentRate * 100)}% of responses
                            </li>
                        )}
                        {pattern.baselineRate !== null && pattern.trend === 'improving' && (
                            <li>
                                Improved from {Math.round(pattern.baselineRate * 100)}% to{' '}
                                {Math.round(pattern.currentRate * 100)}%
                            </li>
                        )}
                        {pattern.baselineRate !== null && pattern.trend === 'slipping' && (
                            <li>
                                Slipped from {Math.round(pattern.baselineRate * 100)}% to{' '}
                                {Math.round(pattern.currentRate * 100)}%
                            </li>
                        )}
                        {pattern.failingCriteria?.length > 0 &&
                            pattern.failingCriteria.map((criteria) => (
                                <li key={criteria}>{formatCriteria(criteria)} needs attention</li>
                            ))}
                        <li className="text-neutral-400">
                            Based on {pattern.sampleSize} responses
                        </li>
                    </ul>

                    {(type === 'blind-spot' || type === 'slipping') && (
                        <Button asChild size="sm" className="mt-4">
                            <Link href={pattern.practiceMode
                                ? `/practice-modes/${pattern.practiceMode}/train`
                                : '/practice-modes'
                            }>
                                <Play className="h-4 w-4 mr-2" />
                                Train {formatSkillName(pattern.skill)}
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
                <CardTitle className="text-lg">Pattern Details</CardTitle>
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
                {improving.map((pattern) => (
                    <PatternSection key={pattern.skill} pattern={pattern} type="improving" />
                ))}
                {slipping.map((pattern) => (
                    <PatternSection key={pattern.skill} pattern={pattern} type="slipping" />
                ))}
                {stable.map((pattern) => (
                    <PatternSection key={pattern.skill} pattern={pattern} type="stable" />
                ))}
            </CardContent>
        </Card>
    );
}
