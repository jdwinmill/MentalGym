import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import InsightForm from '@/components/admin/insight-form';

interface Principle {
    id: number;
    name: string;
}

interface Props {
    principles: Principle[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Insights', href: '/admin/insights' },
    { title: 'Create', href: '/admin/insights/create' },
];

export default function InsightCreate({ principles }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Insight" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-2xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/insights">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Insights
                        </Button>
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold">Create Insight</h1>
                    <p className="text-neutral-500 mt-1">
                        Add a new educational insight to a principle
                    </p>
                </div>

                <InsightForm principles={principles} />
            </div>
        </AppLayout>
    );
}
