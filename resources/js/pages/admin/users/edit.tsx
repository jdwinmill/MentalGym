import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Check, X } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    plan: string;
    trial_ends_at: string | null;
    has_access: boolean;
    current_status: string;
}

interface Props {
    user: User;
    plans: string[];
}

export default function UserEdit({ user, plans }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/users' },
        { title: 'Users', href: '/admin/users' },
        { title: user.name, href: `/admin/users/${user.id}/edit` },
    ];

    const form = useForm({
        plan: user.plan,
        trial_ends_at: user.trial_ends_at || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(`/admin/users/${user.id}`);
    };

    const handleExtendTrial = (days: number) => {
        router.post(`/admin/users/${user.id}/extend-trial`, { days }, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit User - ${user.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-2xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/users">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Users
                        </Button>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{user.name}</CardTitle>
                        <CardDescription>{user.email}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex items-center gap-4 p-4 rounded-lg bg-neutral-50 dark:bg-neutral-800">
                                <div className="flex-1">
                                    <p className="text-sm font-medium">Current Status</p>
                                    <p className="text-lg">{user.current_status}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    {user.has_access ? (
                                        <>
                                            <Check className="h-5 w-5 text-green-500" />
                                            <span className="text-green-600 dark:text-green-400 font-medium">Has Access</span>
                                        </>
                                    ) : (
                                        <>
                                            <X className="h-5 w-5 text-red-500" />
                                            <span className="text-red-600 dark:text-red-400 font-medium">No Access</span>
                                        </>
                                    )}
                                </div>
                            </div>

                            {/* Quick extend trial buttons */}
                            <div className="space-y-2">
                                <Label>Quick Extend Trial</Label>
                                <div className="flex gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleExtendTrial(7)}
                                    >
                                        +7 days
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleExtendTrial(14)}
                                    >
                                        +14 days
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleExtendTrial(30)}
                                    >
                                        +30 days
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Edit User Plan</CardTitle>
                        <CardDescription>Set the user's plan tier and trial period</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="plan">Plan Tier</Label>
                                <Select
                                    value={form.data.plan}
                                    onValueChange={(value) => form.setData('plan', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a plan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {plans.map((plan) => (
                                            <SelectItem key={plan} value={plan}>
                                                {plan.charAt(0).toUpperCase() + plan.slice(1)}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <p className="text-xs text-neutral-500">
                                    Determines daily exchange limits and max level access
                                </p>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="trial_ends_at">Trial Ends At</Label>
                                <Input
                                    id="trial_ends_at"
                                    type="datetime-local"
                                    value={form.data.trial_ends_at}
                                    onChange={(e) => form.setData('trial_ends_at', e.target.value)}
                                />
                                <p className="text-xs text-neutral-500">
                                    Set to a future date to grant trial access regardless of plan
                                </p>
                            </div>

                            <div className="flex gap-2 pt-4">
                                <Button type="submit" disabled={form.processing}>
                                    {form.processing ? 'Saving...' : 'Save Changes'}
                                </Button>
                                <Link href="/admin/users">
                                    <Button type="button" variant="outline">Cancel</Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
