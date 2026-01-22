import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { GatedView } from '@/components/blind-spots/gated-view';
import { SummaryStats } from '@/components/blind-spots/summary-stats';
import { BiggestGapCard, BiggestWinCard } from '@/components/blind-spots/highlight-cards';
import { SkillTrajectory } from '@/components/blind-spots/skill-trajectory';
import { PatternDetails } from '@/components/blind-spots/pattern-details';
import { UniversalPatterns } from '@/components/blind-spots/universal-patterns';
import { TrendChart } from '@/components/blind-spots/trend-chart';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Blind Spots', href: '/blind-spots' },
];

interface SkillPattern {
    skill: string;
    currentRate: number;
    baselineRate: number | null;
    trend: 'improving' | 'stable' | 'slipping' | 'stuck';
    primaryIssue: string | null;
    failingCriteria: string[];
    sampleSize: number;
    practiceMode: string | null;
}

interface UniversalPattern {
    criteria: string;
    rate: number;
    count: number;
    total: number;
}

interface Analysis {
    hasEnoughData: boolean;
    totalSessions: number;
    totalResponses: number;
    blindSpotCount: number;
    hasBlindSpots: boolean;
    isUnlocked: boolean;
    requiredPlan: string;
    gateReason: string | null;
    sessionsUntilInsights: number;
    blindSpots: SkillPattern[] | null;
    improving: SkillPattern[] | null;
    slipping: SkillPattern[] | null;
    stable: SkillPattern[] | null;
    universalPatterns: UniversalPattern[] | null;
    biggestGap: string | null;
    biggestWin: string | null;
    analyzedAt: string;
}

interface WeekData {
    week: string;
    data: Record<string, number | null> | null;
    sessions: number;
    responses: number;
}

interface BlindSpotsIndexProps {
    analysis: Analysis;
    history: WeekData[] | null;
    isPro: boolean;
}

export default function BlindSpotsIndex({ analysis, history, isPro }: BlindSpotsIndexProps) {
    const allSkills = [
        ...(analysis.blindSpots || []),
        ...(analysis.improving || []),
        ...(analysis.slipping || []),
        ...(analysis.stable || []),
    ].sort((a, b) => b.currentRate - a.currentRate);

    const getSkillDetails = (skillName: string | null) => {
        if (!skillName) return null;
        return allSkills.find(s => s.skill === skillName) || null;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Blind Spots" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                            Blind Spots
                        </h1>
                        <p className="mt-1 text-neutral-500 dark:text-neutral-400">
                            Patterns in your training that may be holding you back
                        </p>
                    </div>
                    {isPro && (
                        <Badge className="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            Pro
                        </Badge>
                    )}
                </div>

                {!analysis.isUnlocked ? (
                    <GatedView
                        blindSpotCount={analysis.blindSpotCount}
                        totalSessions={analysis.totalSessions}
                        hasEnoughData={analysis.hasEnoughData}
                        sessionsUntilInsights={analysis.sessionsUntilInsights}
                        gateReason={analysis.gateReason}
                    />
                ) : (
                    <div className="space-y-6">
                        <SummaryStats
                            totalSessions={analysis.totalSessions}
                            totalResponses={analysis.totalResponses}
                        />

                        <div className="grid gap-4 md:grid-cols-2">
                            <BiggestGapCard
                                skill={analysis.biggestGap}
                                details={getSkillDetails(analysis.biggestGap)}
                            />
                            <BiggestWinCard
                                skill={analysis.biggestWin}
                                details={getSkillDetails(analysis.biggestWin)}
                            />
                        </div>

                        <SkillTrajectory skills={allSkills} />

                        <PatternDetails
                            blindSpots={analysis.blindSpots || []}
                            improving={analysis.improving || []}
                            stable={analysis.stable || []}
                            slipping={analysis.slipping || []}
                        />

                        {analysis.universalPatterns && analysis.universalPatterns.length > 0 && (
                            <UniversalPatterns patterns={analysis.universalPatterns} />
                        )}

                        {history && history.some(h => h.data !== null) && (
                            <TrendChart history={history} />
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
