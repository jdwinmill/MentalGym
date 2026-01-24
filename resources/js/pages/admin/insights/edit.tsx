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

interface Insight {
    id: number;
    principle_id: number;
    name: string;
    slug: string;
    summary: string;
    content: string;
    position: number;
    is_active: boolean;
}

interface Props {
    insight: Insight;
    principles: Principle[];
}

export default function InsightEdit({ insight, principles }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/users' },
        { title: 'Insights', href: '/admin/insights' },
        { title: insight.name, href: `/admin/insights/${insight.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit - ${insight.name}`} />
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
                    <h1 className="text-2xl font-bold">Edit Insight</h1>
                    <p className="text-neutral-500 mt-1">
                        Update the insight "{insight.name}"
                    </p>
                </div>

                <InsightForm insight={insight} principles={principles} isEdit />
            </div>
        </AppLayout>
    );
}
