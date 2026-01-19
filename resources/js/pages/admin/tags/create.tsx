import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import TagForm from '@/components/admin/tag-form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Tags', href: '/admin/tags' },
    { title: 'Create', href: '/admin/tags/create' },
];

export default function TagCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Tag" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-2xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/tags">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Tags
                        </Button>
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold">Create Tag</h1>
                    <p className="text-neutral-500 mt-1">
                        Add a new tag for categorizing practice modes
                    </p>
                </div>

                <TagForm />
            </div>
        </AppLayout>
    );
}
