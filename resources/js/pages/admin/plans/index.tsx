import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

interface Plan {
    key: string;
    name: string;
    daily_exchanges: number;
    max_level: number;
    user_count: number;
}

interface Props {
    plans: Plan[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Plans', href: '/admin/plans' },
];

export default function PlansIndex({ plans }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Plans" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Plans</h1>
                        <p className="text-neutral-500 mt-1">
                            Plan tiers are defined in <code className="text-sm bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 rounded">config/plans.php</code>
                        </p>
                    </div>
                </div>

                <div className="rounded-lg border bg-white dark:bg-neutral-900 overflow-hidden">
                    {/* Header */}
                    <div className="grid grid-cols-5 gap-4 p-3 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-500 border-b">
                        <div>Plan</div>
                        <div>Daily Exchanges</div>
                        <div>Max Level</div>
                        <div>Users</div>
                        <div>Status</div>
                    </div>

                    {/* Body */}
                    {plans.map((plan) => (
                        <div key={plan.key} className="grid grid-cols-5 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                            <div className="font-medium">
                                {plan.name}
                                {plan.key === 'free' && (
                                    <span className="ml-2 text-xs text-neutral-400">(default)</span>
                                )}
                            </div>
                            <div className="text-neutral-600 dark:text-neutral-400">
                                {plan.daily_exchanges} / day
                            </div>
                            <div className="text-neutral-600 dark:text-neutral-400">
                                Level {plan.max_level}
                            </div>
                            <div>
                                {plan.user_count > 0 ? (
                                    <Badge variant="secondary">
                                        {plan.user_count} user{plan.user_count !== 1 ? 's' : ''}
                                    </Badge>
                                ) : (
                                    <span className="text-neutral-400">No users</span>
                                )}
                            </div>
                            <div>
                                <Badge variant="outline" className="text-green-600 border-green-300">
                                    Active
                                </Badge>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="text-sm text-neutral-500 bg-neutral-50 dark:bg-neutral-800 rounded-lg p-4">
                    <p className="font-medium mb-2">Plan Configuration</p>
                    <p>
                        To modify plan limits, edit the <code className="bg-neutral-200 dark:bg-neutral-700 px-1.5 py-0.5 rounded">config/plans.php</code> file.
                        Each plan defines:
                    </p>
                    <ul className="list-disc list-inside mt-2 space-y-1">
                        <li><strong>daily_exchanges</strong> - Maximum AI exchanges per day</li>
                        <li><strong>max_level</strong> - Highest training level accessible</li>
                    </ul>
                </div>
            </div>
        </AppLayout>
    );
}
