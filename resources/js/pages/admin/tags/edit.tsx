import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import TagForm from '@/components/admin/tag-form';

interface Tag {
    id: number;
    name: string;
    slug: string;
    category: string;
    display_order: number;
}

interface Props {
    tag: Tag;
}

export default function TagEdit({ tag }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/users' },
        { title: 'Tags', href: '/admin/tags' },
        { title: tag.name, href: `/admin/tags/${tag.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit - ${tag.name}`} />
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
                    <h1 className="text-2xl font-bold">Edit Tag</h1>
                    <p className="text-neutral-500 mt-1">
                        Update the tag "{tag.name}"
                    </p>
                </div>

                <TagForm tag={tag} isEdit />
            </div>
        </AppLayout>
    );
}
