import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Check, X, Pencil } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    trial_ends_at: string | null;
    has_access: boolean;
    subscription_status: string;
    created_at: string;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    users: PaginatedUsers;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/users' },
    { title: 'Users', href: '/admin/users' },
];

export default function UsersIndex({ users }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Users" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Users</h1>
                </div>

                <div className="rounded-lg border bg-white dark:bg-neutral-900 overflow-hidden">
                    {/* Header */}
                    <div className="grid grid-cols-7 gap-4 p-3 bg-neutral-50 dark:bg-neutral-800 text-sm font-medium text-neutral-500 border-b">
                        <div>Name</div>
                        <div>Email</div>
                        <div>Role</div>
                        <div>Status</div>
                        <div>Access</div>
                        <div>Created</div>
                        <div>Actions</div>
                    </div>

                    {/* Body */}
                    {users.data.map((user) => (
                        <div key={user.id} className="grid grid-cols-7 gap-4 p-3 border-b last:border-b-0 items-center text-sm">
                            <div className="font-medium truncate">{user.name}</div>
                            <div className="truncate text-neutral-600 dark:text-neutral-400">{user.email}</div>
                            <div>
                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                                    user.role === 'admin'
                                        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200'
                                }`}>
                                    {user.role || 'user'}
                                </span>
                            </div>
                            <div>
                                <span className={`text-sm ${
                                    user.subscription_status.includes('Trial')
                                        ? 'text-blue-600 dark:text-blue-400'
                                        : user.subscription_status === 'Expired'
                                        ? 'text-red-600 dark:text-red-400'
                                        : 'text-green-600 dark:text-green-400'
                                }`}>
                                    {user.subscription_status}
                                </span>
                            </div>
                            <div>
                                {user.has_access ? (
                                    <Check className="h-5 w-5 text-green-500" />
                                ) : (
                                    <X className="h-5 w-5 text-red-500" />
                                )}
                            </div>
                            <div className="text-neutral-500">{user.created_at}</div>
                            <div>
                                <Link href={`/admin/users/${user.id}/edit`}>
                                    <Button variant="ghost" size="sm">
                                        <Pencil className="h-4 w-4" />
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Pagination */}
                {users.last_page > 1 && (
                    <div className="flex items-center justify-between px-2">
                        <p className="text-sm text-neutral-500">
                            Showing {users.data.length} of {users.total} users
                        </p>
                        <div className="flex gap-2">
                            {users.prev_page_url && (
                                <Link href={users.prev_page_url}>
                                    <Button variant="outline" size="sm">Previous</Button>
                                </Link>
                            )}
                            {users.next_page_url && (
                                <Link href={users.next_page_url}>
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
