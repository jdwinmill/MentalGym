import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Trash2, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useState } from 'react';

interface Tag {
    id: number;
    name: string;
    slug: string;
    category: string;
    display_order: number;
    usage_count: number;
}

interface Props {
    tagsByCategory: Record<string, Tag[]>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Tags', href: '/admin/tags' },
];

const categoryLabels: Record<string, string> = {
    skill: 'Skills',
    context: 'Context',
    duration: 'Duration',
    role: 'Role',
};

const categoryOrder = ['skill', 'context', 'duration', 'role'];

export default function TagsIndex({ tagsByCategory }: Props) {
    const [deleteModal, setDeleteModal] = useState<{ open: boolean; tag: Tag | null }>({
        open: false,
        tag: null,
    });

    const handleDelete = () => {
        if (deleteModal.tag) {
            router.delete(`/admin/tags/${deleteModal.tag.id}`, {
                onSuccess: () => setDeleteModal({ open: false, tag: null }),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tags" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Tags</h1>
                    <Link href="/admin/tags/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Create New Tag
                        </Button>
                    </Link>
                </div>

                <div className="space-y-6">
                    {categoryOrder.map((category) => {
                        const tags = tagsByCategory[category] || [];
                        if (tags.length === 0) return null;

                        return (
                            <div key={category} className="rounded-lg border bg-white dark:bg-neutral-900 overflow-hidden">
                                <div className="p-3 bg-neutral-50 dark:bg-neutral-800 border-b">
                                    <h2 className="font-semibold text-lg">{categoryLabels[category]}</h2>
                                </div>

                                {/* Header */}
                                <div className="grid grid-cols-6 gap-4 p-3 bg-neutral-50/50 dark:bg-neutral-800/50 text-sm font-medium text-neutral-500 border-b">
                                    <div className="col-span-2">Name</div>
                                    <div>Slug</div>
                                    <div>Order</div>
                                    <div>Usage</div>
                                    <div>Actions</div>
                                </div>

                                {/* Body */}
                                {tags.map((tag) => (
                                    <div key={tag.id} className="grid grid-cols-6 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                                        <div className="col-span-2 font-medium">{tag.name}</div>
                                        <div className="text-neutral-500">{tag.slug}</div>
                                        <div className="text-neutral-500">{tag.display_order}</div>
                                        <div>
                                            {tag.usage_count > 0 ? (
                                                <Badge variant="secondary">
                                                    {tag.usage_count} mode{tag.usage_count !== 1 ? 's' : ''}
                                                </Badge>
                                            ) : (
                                                <span className="text-neutral-400">Unused</span>
                                            )}
                                        </div>
                                        <div className="flex gap-1">
                                            <Link href={`/admin/tags/${tag.id}/edit`}>
                                                <Button variant="ghost" size="sm">
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                                onClick={() => setDeleteModal({ open: true, tag })}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Delete Confirmation Dialog */}
            <Dialog open={deleteModal.open} onOpenChange={(open) => setDeleteModal({ open, tag: open ? deleteModal.tag : null })}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Tag</DialogTitle>
                        <DialogDescription>
                            {deleteModal.tag?.usage_count && deleteModal.tag.usage_count > 0 ? (
                                <>
                                    <span className="text-amber-600 font-medium">Warning:</span> This tag is used by {deleteModal.tag.usage_count} practice mode{deleteModal.tag.usage_count !== 1 ? 's' : ''}.
                                    Deleting it will remove the association.
                                </>
                            ) : (
                                <>Are you sure you want to delete "{deleteModal.tag?.name}"?</>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteModal({ open: false, tag: null })}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleDelete}>
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
