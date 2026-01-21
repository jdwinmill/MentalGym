import { Card, CardContent } from '@/components/ui/card';
import { Activity, MessageSquare, Calendar } from 'lucide-react';

interface SummaryStatsProps {
    totalSessions: number;
    totalResponses: number;
}

export function SummaryStats({ totalSessions, totalResponses }: SummaryStatsProps) {
    return (
        <Card>
            <CardContent className="pt-6">
                <h3 className="text-sm font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-4">
                    Your Training Summary
                </h3>
                <div className="grid grid-cols-3 gap-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <Activity className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <div className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                                {totalSessions}
                            </div>
                            <div className="text-sm text-neutral-500 dark:text-neutral-400">
                                Sessions
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                            <MessageSquare className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <div className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                                {totalResponses}
                            </div>
                            <div className="text-sm text-neutral-500 dark:text-neutral-400">
                                Responses
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                            <Calendar className="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <div className="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                                {Math.ceil(totalSessions / 7) || 1}
                            </div>
                            <div className="text-sm text-neutral-500 dark:text-neutral-400">
                                Weeks Active
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
