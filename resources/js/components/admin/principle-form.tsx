import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Plus, Trash2 } from 'lucide-react';

interface BlogUrl {
    title: string;
    url: string;
}

interface PrincipleData {
    id?: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string | null;
    position: number;
    is_active: boolean;
    blog_urls: BlogUrl[];
}

interface Props {
    principle?: PrincipleData;
    isEdit?: boolean;
}

export default function PrincipleForm({ principle, isEdit = false }: Props) {
    const form = useForm({
        name: principle?.name || '',
        slug: principle?.slug || '',
        description: principle?.description || '',
        icon: principle?.icon || '',
        position: principle?.position ?? 0,
        is_active: principle?.is_active ?? true,
        blog_urls: principle?.blog_urls || [],
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

        if (isEdit && principle?.id) {
            form.put(`/admin/principles/${principle.id}`);
        } else {
            form.post('/admin/principles');
        }
    };

    const addBlogUrl = () => {
        form.setData('blog_urls', [...form.data.blog_urls, { title: '', url: '' }]);
    };

    const removeBlogUrl = (index: number) => {
        const newBlogUrls = form.data.blog_urls.filter((_, i) => i !== index);
        form.setData('blog_urls', newBlogUrls);
    };

    const updateBlogUrl = (index: number, field: keyof BlogUrl, value: string) => {
        const newBlogUrls = [...form.data.blog_urls];
        newBlogUrls[index] = { ...newBlogUrls[index], [field]: value };
        form.setData('blog_urls', newBlogUrls);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                    <CardDescription>Define the principle name and details</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Name *</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(e) => handleNameChange(e.target.value)}
                                placeholder="e.g., Clarity Under Pressure"
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

                    <div className="space-y-2">
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                            placeholder="Explain what this principle is about..."
                            rows={3}
                        />
                        {form.errors.description && (
                            <p className="text-sm text-red-500">{form.errors.description}</p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="icon">Icon</Label>
                            <Input
                                id="icon"
                                value={form.data.icon}
                                onChange={(e) => form.setData('icon', e.target.value)}
                                placeholder="e.g., target, crown, git-branch"
                            />
                            {form.errors.icon && (
                                <p className="text-sm text-red-500">{form.errors.icon}</p>
                            )}
                            <p className="text-xs text-neutral-500">
                                Lucide icon name
                            </p>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="position">Position</Label>
                            <Input
                                id="position"
                                type="number"
                                min={0}
                                value={form.data.position}
                                onChange={(e) => form.setData('position', parseInt(e.target.value) || 0)}
                            />
                            {form.errors.position && (
                                <p className="text-sm text-red-500">{form.errors.position}</p>
                            )}
                            <p className="text-xs text-neutral-500">
                                Lower numbers appear first
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center space-x-2 pt-2">
                        <Checkbox
                            id="is_active"
                            checked={form.data.is_active}
                            onCheckedChange={(checked) => form.setData('is_active', checked === true)}
                        />
                        <Label htmlFor="is_active" className="cursor-pointer">
                            Active (visible to users)
                        </Label>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Blog Links</CardTitle>
                    <CardDescription>Add related blog posts or resources</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    {form.data.blog_urls.length === 0 ? (
                        <div className="text-center py-4 text-neutral-500 border-2 border-dashed rounded-lg">
                            <p>No blog links added yet.</p>
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {form.data.blog_urls.map((blogUrl, index) => (
                                <div key={index} className="flex gap-2 items-start">
                                    <div className="flex-1 space-y-2">
                                        <Input
                                            value={blogUrl.title}
                                            onChange={(e) => updateBlogUrl(index, 'title', e.target.value)}
                                            placeholder="Link title"
                                        />
                                    </div>
                                    <div className="flex-1 space-y-2">
                                        <Input
                                            value={blogUrl.url}
                                            onChange={(e) => updateBlogUrl(index, 'url', e.target.value)}
                                            placeholder="https://..."
                                        />
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => removeBlogUrl(index)}
                                        className="text-red-500 hover:text-red-700 hover:bg-red-50"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </div>
                            ))}
                        </div>
                    )}

                    <Button
                        type="button"
                        variant="outline"
                        onClick={addBlogUrl}
                        className="w-full"
                    >
                        <Plus className="h-4 w-4 mr-2" />
                        Add Blog Link
                    </Button>
                </CardContent>
            </Card>

            <div className="flex gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : isEdit ? 'Update Principle' : 'Create Principle'}
                </Button>
                <Link href="/admin/principles">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
            </div>
        </form>
    );
}
