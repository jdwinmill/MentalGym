import { useState, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface WeekData {
    week: string;
    data: Record<string, number | null> | null;
    sessions: number;
    responses: number;
}

interface TrendChartProps {
    history: WeekData[];
}

const skillColors: Record<string, string> = {
    clarity: '#3b82f6',      // blue
    brevity: '#6366f1',      // indigo
    authority: '#ef4444',    // red
    structure: '#10b981',    // green
    composure: '#f59e0b',    // amber
    directness: '#8b5cf6',   // purple
    ownership: '#ec4899',    // pink
    authenticity: '#14b8a6', // teal
    specificity: '#f97316',  // orange
    solution_focus: '#06b6d4', // cyan
    empathy: '#84cc16',      // lime
};

const skillLabels: Record<string, string> = {
    clarity: 'Clarity',
    brevity: 'Brevity',
    authority: 'Authority',
    structure: 'Structure',
    composure: 'Composure',
    directness: 'Directness',
    ownership: 'Ownership',
    authenticity: 'Authenticity',
    specificity: 'Specificity',
    solution_focus: 'Solution Focus',
    empathy: 'Empathy',
};

// Helper to extract available skills from history
function getAvailableSkillsFromHistory(history: WeekData[]): string[] {
    const skills = new Set<string>();
    history.forEach((week) => {
        if (week.data) {
            Object.keys(week.data).forEach((skill) => {
                if (week.data![skill] !== null) {
                    skills.add(skill);
                }
            });
        }
    });
    return Array.from(skills);
}

export function TrendChart({ history }: TrendChartProps) {
    const [timeRange, setTimeRange] = useState('4');

    // Initialize selected skills based on what's available in history
    const [selectedSkills, setSelectedSkills] = useState<string[]>(() => {
        const available = getAvailableSkillsFromHistory(history);
        return available.slice(0, 2);
    });

    const chartData = useMemo(() => {
        const weeks = parseInt(timeRange);
        return history.slice(-weeks);
    }, [history, timeRange]);

    const availableSkills = useMemo(() => {
        return getAvailableSkillsFromHistory(history);
    }, [history]);

    const chartWidth = 600;
    const chartHeight = 200;
    const padding = { top: 20, right: 20, bottom: 30, left: 40 };
    const innerWidth = chartWidth - padding.left - padding.right;
    const innerHeight = chartHeight - padding.top - padding.bottom;

    const getPoints = (skill: string) => {
        const points: { x: number; y: number; value: number }[] = [];
        chartData.forEach((week, index) => {
            if (week.data && week.data[skill] !== null) {
                const x = padding.left + (index / (chartData.length - 1 || 1)) * innerWidth;
                const y = padding.top + (1 - week.data[skill]!) * innerHeight;
                points.push({ x, y, value: week.data[skill]! });
            }
        });
        return points;
    };

    const getPath = (points: { x: number; y: number }[]) => {
        if (points.length < 2) return '';
        return points
            .map((point, i) => (i === 0 ? `M ${point.x} ${point.y}` : `L ${point.x} ${point.y}`))
            .join(' ');
    };

    const toggleSkill = (skill: string) => {
        if (selectedSkills.includes(skill)) {
            if (selectedSkills.length > 1) {
                setSelectedSkills(selectedSkills.filter((s) => s !== skill));
            }
        } else {
            setSelectedSkills([...selectedSkills, skill]);
        }
    };

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle className="text-lg">Trend Over Time</CardTitle>
                <Select value={timeRange} onValueChange={setTimeRange}>
                    <SelectTrigger className="w-32">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="4">4 weeks</SelectItem>
                        <SelectItem value="8">8 weeks</SelectItem>
                    </SelectContent>
                </Select>
            </CardHeader>
            <CardContent>
                <div className="flex flex-wrap gap-2 mb-4">
                    {availableSkills.map((skill) => (
                        <button
                            key={skill}
                            onClick={() => toggleSkill(skill)}
                            className={`px-3 py-1 rounded-full text-sm font-medium transition-colors ${
                                selectedSkills.includes(skill)
                                    ? 'text-white'
                                    : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'
                            }`}
                            style={
                                selectedSkills.includes(skill)
                                    ? { backgroundColor: skillColors[skill] }
                                    : {}
                            }
                        >
                            {skillLabels[skill] || skill}
                        </button>
                    ))}
                </div>

                <div className="overflow-x-auto">
                    <svg
                        viewBox={`0 0 ${chartWidth} ${chartHeight}`}
                        className="w-full"
                        style={{ minWidth: 400 }}
                    >
                        {/* Grid lines */}
                        {[0, 25, 50, 75, 100].map((value) => {
                            const y = padding.top + ((100 - value) / 100) * innerHeight;
                            return (
                                <g key={value}>
                                    <line
                                        x1={padding.left}
                                        y1={y}
                                        x2={chartWidth - padding.right}
                                        y2={y}
                                        stroke="currentColor"
                                        strokeOpacity={0.1}
                                    />
                                    <text
                                        x={padding.left - 8}
                                        y={y}
                                        textAnchor="end"
                                        dominantBaseline="middle"
                                        className="fill-neutral-400 text-xs"
                                    >
                                        {value}%
                                    </text>
                                </g>
                            );
                        })}

                        {/* X-axis labels */}
                        {chartData.map((week, index) => {
                            const x = padding.left + (index / (chartData.length - 1 || 1)) * innerWidth;
                            return (
                                <text
                                    key={week.week}
                                    x={x}
                                    y={chartHeight - 8}
                                    textAnchor="middle"
                                    className="fill-neutral-400 text-xs"
                                >
                                    {week.week}
                                </text>
                            );
                        })}

                        {/* Lines and dots */}
                        {selectedSkills.map((skill) => {
                            const points = getPoints(skill);
                            if (points.length === 0) return null;

                            const path = getPath(points);

                            return (
                                <g key={skill}>
                                    {/* Only draw line if we have 2+ points */}
                                    {path && (
                                        <path
                                            d={path}
                                            fill="none"
                                            stroke={skillColors[skill] || '#888'}
                                            strokeWidth={2}
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    )}
                                    {/* Always draw dots for data points */}
                                    {points.map((point, i) => (
                                        <circle
                                            key={i}
                                            cx={point.x}
                                            cy={point.y}
                                            r={4}
                                            fill={skillColors[skill] || '#888'}
                                        />
                                    ))}
                                </g>
                            );
                        })}
                    </svg>
                </div>

                <p className="text-xs text-neutral-400 mt-2 text-center">
                    Lower is better (failure rate %)
                </p>
            </CardContent>
        </Card>
    );
}
