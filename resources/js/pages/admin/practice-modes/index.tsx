import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Trash2, Plus, Check, X, Upload } from 'lucide-react';
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

interface PracticeMode {
    id: number;
    name: string;
    slug: string;
    tagline: string | null;
    is_active: boolean;
    required_plan: string | null;
    sort_order: number;
}

interface Props {
    modes: PracticeMode[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Practice Modes', href: '/admin/practice-modes' },
];

export default function PracticeModesIndex({ modes }: Props) {
    const [deleteModal, setDeleteModal] = useState<{ open: boolean; mode: PracticeMode | null }>({
        open: false,
        mode: null,
    });

    const handleDelete = () => {
        if (deleteModal.mode) {
            router.delete(`/admin/practice-modes/${deleteModal.mode.id}`, {
                onSuccess: () => setDeleteModal({ open: false, mode: null }),
            });
        }
    };

    const getPlanBadge = (plan: string | null) => {
        if (!plan) return <Badge variant="secondary">All Users</Badge>;
        if (plan === 'pro') return <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Pro+</Badge>;
        return <Badge className="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Unlimited</Badge>;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Practice Modes" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Practice Modes</h1>
                    <div className="flex gap-2">
                        <Link href="/admin/practice-modes-import">
                            <Button variant="outline">
                                <Upload className="h-4 w-4 mr-2" />
                                Bulk Import
                            </Button>
                        </Link>
                        <Link href="/admin/practice-modes/create">
                            <Button>
                                <Plus className="h-4 w-4 mr-2" />
                                Create New Mode
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="rounded-lg border bg-white dark:bg-neutral-900 overflow-hidden">
                    {/* Header */}
                    <div className="grid grid-cols-7 gap-4 p-3 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-500 border-b">
                        <div className="col-span-2">Name</div>
                        <div>Tagline</div>
                        <div>Status</div>
                        <div>Required Plan</div>
                        <div>Order</div>
                        <div>Actions</div>
                    </div>

                    {/* Body */}
                    {modes.length === 0 ? (
                        <div className="p-8 text-center text-neutral-500">
                            No practice modes yet. Create your first one!
                        </div>
                    ) : (
                        modes.map((mode) => (
                            <div key={mode.id} className="grid grid-cols-7 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                                <div className="col-span-2">
                                    <div className="font-medium">{mode.name}</div>
                                    <div className="text-xs text-neutral-500">{mode.slug}</div>
                                </div>
                                <div className="truncate text-neutral-600 dark:text-neutral-400">
                                    {mode.tagline || '-'}
                                </div>
                                <div>
                                    {mode.is_active ? (
                                        <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <Check className="h-3 w-3 mr-1" />
                                            Active
                                        </Badge>
                                    ) : (
                                        <Badge variant="secondary">
                                            <X className="h-3 w-3 mr-1" />
                                            Inactive
                                        </Badge>
                                    )}
                                </div>
                                <div>{getPlanBadge(mode.required_plan)}</div>
                                <div className="text-neutral-500">{mode.sort_order}</div>
                                <div className="flex gap-1">
                                    <Link href={`/admin/practice-modes/${mode.id}/edit`}>
                                        <Button variant="ghost" size="sm">
                                            <Pencil className="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                        onClick={() => setDeleteModal({ open: true, mode })}
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
            <Dialog open={deleteModal.open} onOpenChange={(open) => setDeleteModal({ open, mode: open ? deleteModal.mode : null })}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Practice Mode</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{deleteModal.mode?.name}"? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteModal({ open: false, mode: null })}>
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
