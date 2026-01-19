import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Stats {
    request_count: number;
    total_input_tokens: number;
    total_output_tokens: number;
    total_cache_read_tokens: number;
    avg_response_time: number;
    error_count: number;
    estimated_cost: number;
}

interface ModeStats {
    practice_mode_id: number;
    mode_name: string;
    mode_slug: string;
    request_count: number;
    total_input_tokens: number;
    total_output_tokens: number;
    total_cache_read_tokens: number;
    avg_response_time: number;
    error_count: number;
}

interface ErrorLog {
    id: number;
    created_at: string;
    mode_name: string;
    user_name: string;
    user_email: string;
    error_message: string;
    model: string;
}

interface CacheStats {
    cached_tokens: number;
    cache_write_tokens: number;
    total_input_tokens: number;
    cache_hit_rate: number;
}

interface Props {
    stats: {
        today: Stats;
        week: Stats;
        month: Stats;
    };
    byMode: ModeStats[];
    recentErrors: ErrorLog[];
    cacheStats: CacheStats;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'API Metrics', href: '/admin/api-metrics' },
];

function formatNumber(num: number): string {
    if (num >= 1_000_000) {
        return (num / 1_000_000).toFixed(2) + 'M';
    }
    if (num >= 1_000) {
        return (num / 1_000).toFixed(1) + 'K';
    }
    return num.toString();
}

function StatCard({ title, stats }: { title: string; stats: Stats }) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
                <div className="flex justify-between text-sm">
                    <span className="text-neutral-500">Requests</span>
                    <span className="font-medium">{formatNumber(stats.request_count)}</span>
                </div>
                <div className="flex justify-between text-sm">
                    <span className="text-neutral-500">Input Tokens</span>
                    <span className="font-medium">{formatNumber(stats.total_input_tokens)}</span>
                </div>
                <div className="flex justify-between text-sm">
                    <span className="text-neutral-500">Output Tokens</span>
                    <span className="font-medium">{formatNumber(stats.total_output_tokens)}</span>
                </div>
                <div className="flex justify-between text-sm">
                    <span className="text-neutral-500">Cache Hits</span>
                    <span className="font-medium">{formatNumber(stats.total_cache_read_tokens)}</span>
                </div>
                <div className="flex justify-between text-sm">
                    <span className="text-neutral-500">Avg Response</span>
                    <span className="font-medium">{stats.avg_response_time}ms</span>
                </div>
                <div className="flex justify-between text-sm">
                    <span className="text-neutral-500">Errors</span>
                    <span className={`font-medium ${stats.error_count > 0 ? 'text-red-600' : ''}`}>
                        {stats.error_count}
                    </span>
                </div>
                <div className="flex justify-between text-sm pt-2 border-t">
                    <span className="text-neutral-500">Est. Cost</span>
                    <span className="font-semibold">${stats.estimated_cost.toFixed(4)}</span>
                </div>
            </CardContent>
        </Card>
    );
}

export default function ApiMetricsIndex({ stats, byMode, recentErrors, cacheStats }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="API Metrics" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">API Metrics</h1>
                    <p className="text-neutral-500 mt-1">Monitor Claude API usage and costs</p>
                </div>

                {/* Summary Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <StatCard title="Today" stats={stats.today} />
                    <StatCard title="Last 7 Days" stats={stats.week} />
                    <StatCard title="Last 30 Days" stats={stats.month} />
                </div>

                {/* Cache Efficiency */}
                <Card>
                    <CardHeader>
                        <CardTitle>Cache Efficiency (7 Days)</CardTitle>
                        <CardDescription>Prompt caching reduces costs by ~90% on repeated content</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p className="text-sm text-neutral-500">Cache Hit Rate</p>
                                <p className="text-2xl font-bold">{cacheStats.cache_hit_rate}%</p>
                            </div>
                            <div>
                                <p className="text-sm text-neutral-500">Cached Tokens</p>
                                <p className="text-2xl font-bold">{formatNumber(cacheStats.cached_tokens)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-neutral-500">Cache Writes</p>
                                <p className="text-2xl font-bold">{formatNumber(cacheStats.cache_write_tokens)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-neutral-500">Uncached Input</p>
                                <p className="text-2xl font-bold">{formatNumber(cacheStats.total_input_tokens)}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Usage by Mode */}
                {byMode.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Usage by Mode (30 Days)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-lg border overflow-hidden">
                                <div className="grid grid-cols-7 gap-4 p-3 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-500 border-b">
                                    <div className="col-span-2">Mode</div>
                                    <div>Requests</div>
                                    <div>Input</div>
                                    <div>Output</div>
                                    <div>Avg Time</div>
                                    <div>Errors</div>
                                </div>
                                {byMode.map((mode) => (
                                    <div key={mode.practice_mode_id} className="grid grid-cols-7 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                                        <div className="col-span-2 font-medium">{mode.mode_name}</div>
                                        <div>{formatNumber(mode.request_count)}</div>
                                        <div className="text-neutral-600 dark:text-neutral-400">{formatNumber(mode.total_input_tokens)}</div>
                                        <div className="text-neutral-600 dark:text-neutral-400">{formatNumber(mode.total_output_tokens)}</div>
                                        <div className="text-neutral-600 dark:text-neutral-400">{mode.avg_response_time}ms</div>
                                        <div>
                                            {mode.error_count > 0 ? (
                                                <Badge variant="destructive">{mode.error_count}</Badge>
                                            ) : (
                                                <span className="text-neutral-400">0</span>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Recent Errors */}
                {recentErrors.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Errors</CardTitle>
                            <CardDescription>Last 20 failed API calls</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-lg border overflow-hidden">
                                <div className="grid grid-cols-5 gap-4 p-3 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-500 border-b">
                                    <div>Time</div>
                                    <div>Mode</div>
                                    <div>User</div>
                                    <div className="col-span-2">Error</div>
                                </div>
                                {recentErrors.map((error) => (
                                    <div key={error.id} className="grid grid-cols-5 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                                        <div className="text-neutral-500 text-xs">{error.created_at}</div>
                                        <div>{error.mode_name}</div>
                                        <div className="truncate" title={error.user_email}>{error.user_name}</div>
                                        <div className="col-span-2 text-red-600 dark:text-red-400 truncate" title={error.error_message}>
                                            {error.error_message}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Empty State */}
                {stats.month.request_count === 0 && (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <p className="text-neutral-500">No API calls recorded yet.</p>
                            <p className="text-sm text-neutral-400 mt-1">
                                Metrics will appear here once users start training sessions.
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
