import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import PracticeModeForm from '@/components/admin/practice-mode-form';

interface Tag {
    id: number;
    name: string;
    slug: string;
    category: string;
    display_order: number;
}

interface Props {
    tagsByCategory: Record<string, Tag[]>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Practice Modes', href: '/admin/practice-modes' },
    { title: 'Create', href: '/admin/practice-modes/create' },
];

export default function PracticeModeCreate({ tagsByCategory }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Practice Mode" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-4xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/practice-modes">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Practice Modes
                        </Button>
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold">Create Practice Mode</h1>
                    <p className="text-neutral-500 mt-1">
                        Define a new training methodology for users
                    </p>
                </div>

                <PracticeModeForm tagsByCategory={tagsByCategory} />
            </div>
        </AppLayout>
    );
}
