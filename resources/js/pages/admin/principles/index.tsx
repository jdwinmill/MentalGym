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

interface Principle {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    position: number;
    is_active: boolean;
    insights_count: number;
}

interface Props {
    principles: Principle[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Principles', href: '/admin/principles' },
];

export default function PrinciplesIndex({ principles }: Props) {
    const [deleteModal, setDeleteModal] = useState<{ open: boolean; principle: Principle | null }>({
        open: false,
        principle: null,
    });

    const handleDelete = () => {
        if (deleteModal.principle) {
            router.delete(`/admin/principles/${deleteModal.principle.id}`, {
                onSuccess: () => setDeleteModal({ open: false, principle: null }),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Principles" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Principles</h1>
                    <Link href="/admin/principles/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Create New Principle
                        </Button>
                    </Link>
                </div>

                <div className="rounded-lg border bg-white dark:bg-neutral-900 overflow-hidden">
                    {/* Header */}
                    <div className="grid grid-cols-6 gap-4 p-3 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-500 border-b">
                        <div className="col-span-2">Name</div>
                        <div>Icon</div>
                        <div>Position</div>
                        <div>Insights</div>
                        <div>Actions</div>
                    </div>

                    {/* Body */}
                    {principles.length === 0 ? (
                        <div className="p-8 text-center text-neutral-500">
                            No principles yet. Create your first principle to get started.
                        </div>
                    ) : (
                        principles.map((principle) => (
                            <div key={principle.id} className="grid grid-cols-6 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                                <div className="col-span-2">
                                    <div className="font-medium">{principle.name}</div>
                                    {!principle.is_active && (
                                        <Badge variant="outline" className="text-xs mt-1">Inactive</Badge>
                                    )}
                                </div>
                                <div className="text-neutral-500">{principle.icon || '—'}</div>
                                <div className="text-neutral-500">{principle.position}</div>
                                <div>
                                    <Badge variant="secondary">
                                        {principle.insights_count} insight{principle.insights_count !== 1 ? 's' : ''}
                                    </Badge>
                                </div>
                                <div className="flex gap-1">
                                    <Link href={`/admin/principles/${principle.id}/edit`}>
                                        <Button variant="ghost" size="sm">
                                            <Pencil className="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                        onClick={() => setDeleteModal({ open: true, principle })}
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
            <Dialog open={deleteModal.open} onOpenChange={(open) => setDeleteModal({ open, principle: open ? deleteModal.principle : null })}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Principle</DialogTitle>
                        <DialogDescription>
                            {deleteModal.principle?.insights_count && deleteModal.principle.insights_count > 0 ? (
                                <>
                                    <span className="text-amber-600 font-medium">Warning:</span> This principle has {deleteModal.principle.insights_count} insight{deleteModal.principle.insights_count !== 1 ? 's' : ''}.
                                    Deleting it will also delete all associated insights.
                                </>
                            ) : (
                                <>Are you sure you want to delete "{deleteModal.principle?.name}"?</>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteModal({ open: false, principle: null })}>
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
