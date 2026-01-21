import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface UniversalPattern {
    criteria: string;
    rate: number;
    count: number;
    total: number;
}

interface UniversalPatternsProps {
    patterns: UniversalPattern[];
}

function formatCriteria(criteria: string): string {
    const labels: Record<string, string> = {
        hedging: 'Hedging',
        filler_phrases: 'Filler phrases',
        apologies: 'Apologies',
        ran_long: 'Running long',
    };
    return labels[criteria] || criteria;
}

function getBarWidth(rate: number): number {
    return Math.min(rate * 100, 100);
}

function getBarColor(rate: number): string {
    if (rate > 0.5) return 'bg-red-500';
    if (rate > 0.3) return 'bg-amber-500';
    return 'bg-green-500';
}

export function UniversalPatterns({ patterns }: UniversalPatternsProps) {
    if (patterns.length === 0) {
        return null;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-lg">Universal Patterns</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                {patterns.map((pattern) => (
                    <div key={pattern.criteria} className="space-y-2">
                        <div className="flex items-center justify-between text-sm">
                            <span className="font-medium text-neutral-900 dark:text-neutral-100">
                                {formatCriteria(pattern.criteria)}
                            </span>
                            <span className="text-neutral-500 dark:text-neutral-400">
                                {Math.round(pattern.rate * 100)}% of responses ({pattern.count}/{pattern.total})
                            </span>
                        </div>
                        <div className="h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                            <div
                                className={`h-full rounded-full transition-all duration-300 ${getBarColor(pattern.rate)}`}
                                style={{ width: `${getBarWidth(pattern.rate)}%` }}
                            />
                        </div>
                    </div>
                ))}
            </CardContent>
        </Card>
    );
}
