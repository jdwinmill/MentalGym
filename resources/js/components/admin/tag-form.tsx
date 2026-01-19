import { useForm } from '@inertiajs/react';
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
import { Link } from '@inertiajs/react';
import { FormEvent } from 'react';

interface TagData {
    id?: number;
    name: string;
    slug: string;
    category: string;
    display_order: number;
}

interface Props {
    tag?: TagData;
    isEdit?: boolean;
}

export default function TagForm({ tag, isEdit = false }: Props) {
    const form = useForm({
        name: tag?.name || '',
        slug: tag?.slug || '',
        category: tag?.category || 'skill',
        display_order: tag?.display_order ?? 0,
    });

    const handleNameChange = (value: string) => {
        form.setData('name', value);
        if (!isEdit) {
            const slug = value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            form.setData('slug', slug);
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        if (isEdit && tag?.id) {
            form.put(`/admin/tags/${tag.id}`);
        } else {
            form.post('/admin/tags');
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Tag Details</CardTitle>
                    <CardDescription>Define the tag name, category, and display order</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Name *</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(e) => handleNameChange(e.target.value)}
                                placeholder="e.g., Decision-Making"
                            />
                            {form.errors.name && (
                                <p className="text-sm text-red-500">{form.errors.name}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="slug">Slug</Label>
                            <Input
                                id="slug"
                                value={form.data.slug}
                                onChange={(e) => form.setData('slug', e.target.value)}
                                placeholder="auto-generated-from-name"
                            />
                            {form.errors.slug && (
                                <p className="text-sm text-red-500">{form.errors.slug}</p>
                            )}
                            <p className="text-xs text-neutral-500">
                                URL-safe identifier. Auto-generated if left empty.
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="category">Category *</Label>
                            <Select
                                value={form.data.category}
                                onValueChange={(value) => form.setData('category', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="skill">Skill</SelectItem>
                                    <SelectItem value="context">Context</SelectItem>
                                    <SelectItem value="duration">Duration</SelectItem>
                                    <SelectItem value="role">Role</SelectItem>
                                </SelectContent>
                            </Select>
                            {form.errors.category && (
                                <p className="text-sm text-red-500">{form.errors.category}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="display_order">Display Order</Label>
                            <Input
                                id="display_order"
                                type="number"
                                min={0}
                                value={form.data.display_order}
                                onChange={(e) => form.setData('display_order', parseInt(e.target.value) || 0)}
                            />
                            {form.errors.display_order && (
                                <p className="text-sm text-red-500">{form.errors.display_order}</p>
                            )}
                            <p className="text-xs text-neutral-500">
                                Lower numbers appear first within the category
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div className="flex gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : isEdit ? 'Update Tag' : 'Create Tag'}
                </Button>
                <Link href="/admin/tags">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
            </div>
        </form>
    );
}
