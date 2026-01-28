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

interface SkillDimension {
    key: string;
    label: string;
    category: string | null;
    active: boolean;
}

interface Props {
    dimensions: SkillDimension[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Skill Dimensions', href: '/admin/skill-dimensions' },
];

const categoryLabels: Record<string, string> = {
    communication: 'Communication',
    reasoning: 'Reasoning',
    resilience: 'Resilience',
    influence: 'Influence',
    self_awareness: 'Self-Awareness',
    manipulation_resistance: 'Manipulation Resistance',
};

export default function SkillDimensionsIndex({ dimensions }: Props) {
    const [deleteModal, setDeleteModal] = useState<{ open: boolean; dimension: SkillDimension | null }>({
        open: false,
        dimension: null,
    });

    const handleDelete = () => {
        if (deleteModal.dimension) {
            router.delete(`/admin/skill-dimensions/${deleteModal.dimension.key}`, {
                onSuccess: () => setDeleteModal({ open: false, dimension: null }),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Skill Dimensions" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Skill Dimensions</h1>
                    <Link href="/admin/skill-dimensions/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Create Dimension
                        </Button>
                    </Link>
                </div>

                <div className="rounded-lg border bg-white dark:bg-neutral-900 overflow-hidden">
                    {/* Header */}
                    <div className="grid grid-cols-5 gap-4 p-3 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-500 border-b">
                        <div>Key</div>
                        <div>Label</div>
                        <div>Category</div>
                        <div>Status</div>
                        <div>Actions</div>
                    </div>

                    {/* Body */}
                    {dimensions.length === 0 ? (
                        <div className="p-8 text-center text-neutral-500">
                            No skill dimensions found. Create one to get started.
                        </div>
                    ) : (
                        dimensions.map((dim) => (
                            <div key={dim.key} className="grid grid-cols-5 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                                <div className="font-mono text-neutral-600 dark:text-neutral-400">{dim.key}</div>
                                <div className="font-medium">{dim.label}</div>
                                <div>
                                    {dim.category ? (
                                        <Badge variant="secondary">
                                            {categoryLabels[dim.category] || dim.category}
                                        </Badge>
                                    ) : (
                                        <span className="text-neutral-400">-</span>
                                    )}
                                </div>
                                <div>
                                    {dim.active ? (
                                        <Badge variant="default">Active</Badge>
                                    ) : (
                                        <Badge variant="outline">Inactive</Badge>
                                    )}
                                </div>
                                <div className="flex gap-1">
                                    <Link href={`/admin/skill-dimensions/${dim.key}/edit`}>
                                        <Button variant="ghost" size="sm">
                                            <Pencil className="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                        onClick={() => setDeleteModal({ open: true, dimension: dim })}
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>

            {/* Delete Confirmation Dialog */}
            <Dialog open={deleteModal.open} onOpenChange={(open) => setDeleteModal({ open, dimension: open ? deleteModal.dimension : null })}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Skill Dimension</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{deleteModal.dimension?.label}"?
                            This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteModal({ open: false, dimension: null })}>
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
