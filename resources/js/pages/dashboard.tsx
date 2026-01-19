import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardProps {
    hasAccess: boolean;
    subscriptionStatus: string;
}

export default function Dashboard({ hasAccess, subscriptionStatus }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {!hasAccess && (
                    <div className="max-w-3xl mx-auto w-full">
                        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/50">
                            <div className="flex items-start gap-3">
                                <AlertCircle className="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <h3 className="font-medium text-amber-800 dark:text-amber-200">
                                        Subscription Required
                                    </h3>
                                    <p className="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                        Your trial has expired. Subscribe to unlock all content and continue your training.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
                {hasAccess && (
                    <div className="max-w-3xl mx-auto w-full">
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Status: {subscriptionStatus}
                        </p>
                    </div>
                )}
                <div className="flex flex-1 items-center justify-center">
                    <div className="text-center">
                        <h2 className="text-xl font-semibold text-neutral-700 dark:text-neutral-300">
                            Coming Soon
                        </h2>
                        <p className="mt-2 text-neutral-500 dark:text-neutral-400">
                            New practice modes are being developed.
                        </p>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
