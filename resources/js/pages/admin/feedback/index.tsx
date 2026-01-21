import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Bug, Lightbulb, MoreHorizontal, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useState } from 'react';

interface FeedbackUser {
    id: number;
    name: string;
    email: string;
}

interface FeedbackItem {
    id: number;
    type: 'bug' | 'idea' | 'other';
    title: string | null;
    body: string;
    url: string;
    user: FeedbackUser;
    created_at: string;
}

interface PaginatedFeedback {
    data: FeedbackItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    feedback: PaginatedFeedback;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/feedback' },
    { title: 'Feedback', href: '/admin/feedback' },
];

function TypeBadge({ type }: { type: FeedbackItem['type'] }) {
    if (type === 'bug') {
        return (
            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                <Bug className="h-3 w-3" />
                Bug
            </span>
        );
    }
    if (type === 'idea') {
        return (
            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                <Lightbulb className="h-3 w-3" />
                Idea
            </span>
        );
    }
    return (
        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
            <MoreHorizontal className="h-3 w-3" />
            Other
        </span>
    );
}

function FeedbackCard({ item, onDelete }: { item: FeedbackItem; onDelete: (id: number) => void }) {
    const [isDeleting, setIsDeleting] = useState(false);

    function handleDelete() {
        if (!confirm('Delete this feedback?')) return;
        setIsDeleting(true);
        onDelete(item.id);
    }

    return (
        <div className={`rounded-lg border bg-white dark:bg-neutral-900 p-4 ${item.type === 'bug' ? 'border-red-200 dark:border-red-900/50' : 'border-neutral-200 dark:border-neutral-800'}`}>
            <div className="flex items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-2">
                        <TypeBadge type={item.type} />
                        <span className="text-xs text-neutral-500">{item.created_at}</span>
                    </div>

                    {item.title && (
                        <h3 className="font-medium text-neutral-900 dark:text-neutral-100 mb-1">
                            {item.title}
                        </h3>
                    )}

                    <p className="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">
                        {item.body}
                    </p>

                    <div className="mt-3 flex items-center gap-4 text-xs text-neutral-500">
                        <span>
                            From: <span className="font-medium">{item.user.name}</span> ({item.user.email})
                        </span>
                        <span>
                            Page: <code className="bg-neutral-100 dark:bg-neutral-800 px-1 rounded">{item.url}</code>
                        </span>
                    </div>
                </div>

                <Button
                    variant="ghost"
                    size="sm"
                    onClick={handleDelete}
                    disabled={isDeleting}
                    className="text-neutral-400 hover:text-red-600 dark:hover:text-red-400"
                >
                    <Trash2 className="h-4 w-4" />
                </Button>
            </div>
        </div>
    );
}

export default function FeedbackIndex({ feedback }: Props) {
    function handleDelete(id: number) {
        router.delete(`/admin/feedback/${id}`, {
            preserveScroll: true,
        });
    }

    const bugCount = feedback.data.filter(f => f.type === 'bug').length;
    const ideaCount = feedback.data.filter(f => f.type === 'idea').length;
    const otherCount = feedback.data.filter(f => f.type === 'other').length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Feedback" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Feedback</h1>
                        <p className="text-sm text-neutral-500 mt-1">
                            {feedback.total} total items
                            {bugCount > 0 && <span className="text-red-600 dark:text-red-400 ml-2">({bugCount} bugs)</span>}
                        </p>
                    </div>
                </div>

                {feedback.data.length === 0 ? (
                    <div className="flex flex-1 items-center justify-center">
                        <div className="text-center">
                            <Lightbulb className="mx-auto h-12 w-12 text-neutral-300 dark:text-neutral-600" />
                            <h2 className="mt-4 text-lg font-semibold text-neutral-700 dark:text-neutral-300">
                                No Feedback Yet
                            </h2>
                            <p className="mt-2 text-neutral-500 dark:text-neutral-400">
                                User feedback will appear here.
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {feedback.data.map((item) => (
                            <FeedbackCard key={item.id} item={item} onDelete={handleDelete} />
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {feedback.last_page > 1 && (
                    <div className="flex items-center justify-between px-2">
                        <p className="text-sm text-neutral-500">
                            Showing {feedback.data.length} of {feedback.total} items
                        </p>
                        <div className="flex gap-2">
                            {feedback.prev_page_url && (
                                <Link href={feedback.prev_page_url}>
                                    <Button variant="outline" size="sm">Previous</Button>
                                </Link>
                            )}
                            {feedback.next_page_url && (
                                <Link href={feedback.next_page_url}>
                                    <Button variant="outline" size="sm">Next</Button>
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
