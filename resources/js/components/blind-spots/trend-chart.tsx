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

// Colors for different skill categories
const categoryColors: Record<string, string> = {
    communication: '#3b82f6',  // blue
    reasoning: '#8b5cf6',      // purple
    resilience: '#10b981',     // green
    influence: '#f59e0b',      // amber
    self_awareness: '#ec4899', // pink
};

// Fallback colors for dimensions not matched by category
const dimensionColors: Record<string, string> = {
    assertiveness: '#3b82f6',
    perspective_taking: '#6366f1',
    active_listening: '#06b6d4',
    clarity: '#0ea5e9',
    diplomatic_framing: '#14b8a6',
    logical_structure: '#8b5cf6',
    cognitive_flexibility: '#a855f7',
    critical_analysis: '#7c3aed',
    assumption_identification: '#6d28d9',
    evidence_evaluation: '#5b21b6',
    emotional_regulation: '#10b981',
    pressure_composure: '#059669',
    recovery_speed: '#047857',
    defensiveness_management: '#0d9488',
    uncertainty_tolerance: '#0f766e',
    stress_management: '#15803d',
    self_confidence: '#16a34a',
    persuasion: '#f59e0b',
    negotiation_leverage: '#d97706',
    stakeholder_reading: '#b45309',
    objection_handling: '#92400e',
    timing_awareness: '#ea580c',
    blind_spot_recognition: '#ec4899',
    bias_detection: '#db2777',
    overconfidence_calibration: '#be185d',
    emotional_triggers: '#9d174d',
    feedback_receptivity: '#831843',
};

function getSkillColor(skill: string): string {
    return dimensionColors[skill] || categoryColors[skill] || '#888888';
}

function formatLabel(skill: string): string {
    return skill
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

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

    // Scale: scores are 1-10, map to chart height (10 at top, 1 at bottom)
    const getPoints = (skill: string) => {
        const points: { x: number; y: number; value: number }[] = [];
        chartData.forEach((week, index) => {
            if (week.data && week.data[skill] !== null) {
                const x = padding.left + (index / (chartData.length - 1 || 1)) * innerWidth;
                // Score 10 = top (y=padding.top), Score 1 = bottom (y=padding.top + innerHeight)
                const normalizedScore = (week.data[skill]! - 1) / 9; // 0 to 1
                const y = padding.top + (1 - normalizedScore) * innerHeight;
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
                                    ? { backgroundColor: getSkillColor(skill) }
                                    : {}
                            }
                        >
                            {formatLabel(skill)}
                        </button>
                    ))}
                </div>

                <div className="overflow-x-auto">
                    <svg
                        viewBox={`0 0 ${chartWidth} ${chartHeight}`}
                        className="w-full"
                        style={{ minWidth: 400 }}
                    >
                        {/* Grid lines - now 1-10 scale */}
                        {[1, 4, 7, 10].map((value) => {
                            const normalizedValue = (value - 1) / 9; // 0 to 1
                            const y = padding.top + (1 - normalizedValue) * innerHeight;
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
                                        {value}
                                    </text>
                                </g>
                            );
                        })}

                        {/* Threshold line at 4 (blind spot threshold) */}
                        <line
                            x1={padding.left}
                            y1={padding.top + (1 - (4 - 1) / 9) * innerHeight}
                            x2={chartWidth - padding.right}
                            y2={padding.top + (1 - (4 - 1) / 9) * innerHeight}
                            stroke="#ef4444"
                            strokeOpacity={0.3}
                            strokeDasharray="4 4"
                        />

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
                            const color = getSkillColor(skill);

                            return (
                                <g key={skill}>
                                    {/* Only draw line if we have 2+ points */}
                                    {path && (
                                        <path
                                            d={path}
                                            fill="none"
                                            stroke={color}
                                            strokeWidth={2}
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    )}
                                    {/* Always draw dots for data points */}
                                    {points.map((point, i) => (
                                        <g key={i}>
                                            <circle
                                                cx={point.x}
                                                cy={point.y}
                                                r={4}
                                                fill={color}
                                            />
                                            {/* Tooltip on hover would go here */}
                                        </g>
                                    ))}
                                </g>
                            );
                        })}
                    </svg>
                </div>

                <div className="flex items-center justify-center gap-4 text-xs text-neutral-400 mt-2">
                    <span>Higher is better (score 1-10)</span>
                    <span className="flex items-center gap-1">
                        <span className="w-4 h-0.5 bg-red-400 opacity-50" style={{ borderStyle: 'dashed', borderWidth: '1px 0 0 0' }} />
                        Blind spot threshold (4)
                    </span>
                </div>
            </CardContent>
        </Card>
    );
}
