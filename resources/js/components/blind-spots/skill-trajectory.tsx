import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface SkillPattern {
    skill: string;
    currentRate: number;
    baselineRate: number | null;
    trend: 'improving' | 'stable' | 'slipping' | 'stuck';
}

interface SkillTrajectoryProps {
    skills: SkillPattern[];
}

function formatSkillName(skill: string): string {
    return skill.charAt(0).toUpperCase() + skill.slice(1);
}

function formatTrend(trend: string): string {
    const labels: Record<string, string> = {
        improving: 'Improving ↑',
        stable: 'Stable',
        slipping: 'Slipping ↓',
        stuck: 'Stuck',
    };
    return labels[trend] || trend;
}

function getTrendColor(trend: string): string {
    const colors: Record<string, string> = {
        improving: 'text-green-600 dark:text-green-400',
        stable: 'text-neutral-500 dark:text-neutral-400',
        slipping: 'text-amber-600 dark:text-amber-400',
        stuck: 'text-red-600 dark:text-red-400',
    };
    return colors[trend] || 'text-neutral-500';
}

function getBarColor(trend: string): string {
    const colors: Record<string, string> = {
        improving: 'bg-gradient-to-r from-green-500 to-green-400',
        stable: 'bg-gradient-to-r from-neutral-400 to-neutral-300',
        slipping: 'bg-gradient-to-r from-amber-500 to-amber-400',
        stuck: 'bg-gradient-to-r from-red-500 to-red-400',
    };
    return colors[trend] || 'bg-neutral-400';
}

export function SkillTrajectory({ skills }: SkillTrajectoryProps) {
    if (skills.length === 0) {
        return null;
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-lg">Skill Trajectory</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                {skills.map((skill) => {
                    const successRate = 1 - skill.currentRate;

                    return (
                        <div key={skill.skill} className="space-y-2">
                            <div className="flex items-center justify-between text-sm">
                                <span className="font-medium text-neutral-900 dark:text-neutral-100">
                                    {formatSkillName(skill.skill)}
                                </span>
                                <div className="flex items-center gap-4">
                                    <span className={cn('font-medium', getTrendColor(skill.trend))}>
                                        {formatTrend(skill.trend)}
                                    </span>
                                    <span className="text-neutral-500 dark:text-neutral-400 w-32 text-right">
                                        {Math.round(skill.currentRate * 100)}% failure
                                        {skill.baselineRate !== null && skill.trend !== 'stable' && (
                                            <span className="text-neutral-400 dark:text-neutral-500">
                                                {' '}(was {Math.round(skill.baselineRate * 100)}%)
                                            </span>
                                        )}
                                    </span>
                                </div>
                            </div>
                            <div className="h-3 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                <div
                                    className={cn('h-full rounded-full transition-all duration-300', getBarColor(skill.trend))}
                                    style={{ width: `${successRate * 100}%` }}
                                />
                            </div>
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}
