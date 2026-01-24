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

interface Insight {
    id: number;
    name: string;
    slug: string;
    principle_name: string;
    position: number;
    is_active: boolean;
}

interface Props {
    insights: Insight[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Insights', href: '/admin/insights' },
];

export default function InsightsIndex({ insights }: Props) {
    const [deleteModal, setDeleteModal] = useState<{ open: boolean; insight: Insight | null }>({
        open: false,
        insight: null,
    });

    const handleDelete = () => {
        if (deleteModal.insight) {
            router.delete(`/admin/insights/${deleteModal.insight.id}`, {
                onSuccess: () => setDeleteModal({ open: false, insight: null }),
            });
        }
    };

    // Group insights by principle
    const insightsByPrinciple: Record<string, Insight[]> = {};
    insights.forEach((insight) => {
        if (!insightsByPrinciple[insight.principle_name]) {
            insightsByPrinciple[insight.principle_name] = [];
        }
        insightsByPrinciple[insight.principle_name].push(insight);
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Insights" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Insights</h1>
                    <Link href="/admin/insights/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Create New Insight
                        </Button>
                    </Link>
                </div>

                {Object.keys(insightsByPrinciple).length === 0 ? (
                    <div className="rounded-lg border bg-white dark:bg-neutral-900 p-8 text-center text-neutral-500">
                        No insights yet. Create your first insight to get started.
                    </div>
                ) : (
                    <div className="space-y-6">
                        {Object.entries(insightsByPrinciple).map(([principleName, principleInsights]) => (
                            <div key={principleName} className="rounded-lg border bg-white dark:bg-neutral-900 overflow-hidden">
                                <div className="p-3 bg-neutral-50 dark:bg-neutral-800 border-b">
                                    <h2 className="font-semibold text-lg">{principleName}</h2>
                                </div>

                                {/* Header */}
                                <div className="grid grid-cols-5 gap-4 p-3 bg-neutral-50/50 dark:bg-neutral-800/50 text-sm font-medium text-neutral-500 border-b">
                                    <div className="col-span-2">Name</div>
                                    <div>Position</div>
                                    <div>Status</div>
                                    <div>Actions</div>
                                </div>

                                {/* Body */}
                                {principleInsights.map((insight) => (
                                    <div key={insight.id} className="grid grid-cols-5 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                                        <div className="col-span-2 font-medium">{insight.name}</div>
                                        <div className="text-neutral-500">{insight.position}</div>
                                        <div>
                                            {insight.is_active ? (
                                                <Badge variant="secondary">Active</Badge>
                                            ) : (
                                                <Badge variant="outline">Inactive</Badge>
                                            )}
                                        </div>
                                        <div className="flex gap-1">
                                            <Link href={`/admin/insights/${insight.id}/edit`}>
                                                <Button variant="ghost" size="sm">
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="text-red-600 hover:text-red-700 hover:bg-red-50"
                                                onClick={() => setDeleteModal({ open: true, insight })}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Delete Confirmation Dialog */}
            <Dialog open={deleteModal.open} onOpenChange={(open) => setDeleteModal({ open, insight: open ? deleteModal.insight : null })}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Insight</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete "{deleteModal.insight?.name}"? This will also remove any drill associations.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteModal({ open: false, insight: null })}>
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
