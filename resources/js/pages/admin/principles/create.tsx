import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import PrincipleForm from '@/components/admin/principle-form';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Principles', href: '/admin/principles' },
    { title: 'Create', href: '/admin/principles/create' },
];

export default function PrincipleCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Principle" />
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
                    <h1 className="text-2xl font-bold">Create Principle</h1>
                    <p className="text-neutral-500 mt-1">
                        Add a new educational principle
                    </p>
                </div>

                <PrincipleForm />
            </div>
        </AppLayout>
    );
}
