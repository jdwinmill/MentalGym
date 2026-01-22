import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { AlertTriangle, Trophy, Play } from 'lucide-react';

interface SkillPattern {
    skill: string;
    currentRate: number;
    baselineRate: number | null;
    trend: string;
    primaryIssue: string | null;
    practiceMode: string | null;
}

interface HighlightCardProps {
    skill: string | null;
    details: SkillPattern | null;
}

function formatSkillName(skill: string): string {
    return skill.charAt(0).toUpperCase() + skill.slice(1);
}

function formatCriteria(criteria: string): string {
    const labels: Record<string, string> = {
        hedging: 'Hedging',
        ran_long: 'Running long',
        filler_phrases: 'Filler phrases',
        structure_followed: 'Missing structure',
        direct_opening: 'Buried lead',
        calm_tone: 'Composure issues',
    };
    return labels[criteria] || criteria;
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
            <CardContent>
                <div className="text-2xl font-bold text-red-700 dark:text-red-400">
                    {formatSkillName(skill)}
                </div>
                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    {details.primaryIssue
                        ? `${formatCriteria(details.primaryIssue)} in ${Math.round(details.currentRate * 100)}% of responses`
                        : `${Math.round(details.currentRate * 100)}% failure rate`}
                </p>
            </CardContent>
            <CardFooter className="pt-0">
                <Button asChild variant="outline" size="sm" className="w-full">
                    <Link href={details.practiceMode
                        ? `/practice-modes/${details.practiceMode}/train`
                        : '/practice-modes'
                    }>
                        <Play className="h-4 w-4 mr-2" />
                        Train This
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
            <CardContent>
                <div className="text-2xl font-bold text-green-700 dark:text-green-400">
                    {formatSkillName(skill)}
                </div>
                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                    {improvement
                        ? `Improved ${improvement}% recently`
                        : `Now at ${Math.round((1 - details.currentRate) * 100)}% success rate`}
                </p>
            </CardContent>
        </Card>
    );
}
