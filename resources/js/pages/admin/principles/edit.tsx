import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import PrincipleForm from '@/components/admin/principle-form';

interface BlogUrl {
    title: string;
    url: string;
}

interface Principle {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string | null;
    position: number;
    is_active: boolean;
    blog_urls: BlogUrl[];
}

interface Props {
    principle: Principle;
}

export default function PrincipleEdit({ principle }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/users' },
        { title: 'Principles', href: '/admin/principles' },
        { title: principle.name, href: `/admin/principles/${principle.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit - ${principle.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-2xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/principles">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Principles
                        </Button>
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold">Edit Principle</h1>
                    <p className="text-neutral-500 mt-1">
                        Update the principle "{principle.name}"
                    </p>
                </div>

                <PrincipleForm principle={principle} isEdit />
            </div>
        </AppLayout>
    );
}
