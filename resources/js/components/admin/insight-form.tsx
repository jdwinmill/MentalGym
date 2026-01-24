import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
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

interface Principle {
    id: number;
    name: string;
}

interface InsightData {
    id?: number;
    principle_id: number;
    name: string;
    slug: string;
    summary: string;
    content: string;
    position: number;
    is_active: boolean;
}

interface Props {
    insight?: InsightData;
    principles: Principle[];
    isEdit?: boolean;
}

export default function InsightForm({ insight, principles, isEdit = false }: Props) {
    const form = useForm({
        principle_id: insight?.principle_id?.toString() || '',
        name: insight?.name || '',
        slug: insight?.slug || '',
        summary: insight?.summary || '',
        content: insight?.content || '',
        position: insight?.position ?? 0,
        is_active: insight?.is_active ?? true,
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

        // Transform principle_id back to number
        form.transform((data) => ({
            ...data,
            principle_id: parseInt(data.principle_id),
        }));

        if (isEdit && insight?.id) {
            form.put(`/admin/insights/${insight.id}`);
        } else {
            form.post('/admin/insights');
        }
    };

    const summaryLength = form.data.summary.length;
    const summaryRemaining = 500 - summaryLength;

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                    <CardDescription>Define the insight name and principle</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="principle_id">Principle *</Label>
                        <Select
                            value={form.data.principle_id}
                            onValueChange={(value) => form.setData('principle_id', value)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select a principle" />
                            </SelectTrigger>
                            <SelectContent>
                                {principles.map((principle) => (
                                    <SelectItem key={principle.id} value={principle.id.toString()}>
                                        {principle.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {form.errors.principle_id && (
                            <p className="text-sm text-red-500">{form.errors.principle_id}</p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Name *</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(e) => handleNameChange(e.target.value)}
                                placeholder="e.g., Lead with the Conclusion"
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
                                Lower numbers appear first within the principle
                            </p>
                        </div>
                        <div className="flex items-end pb-2">
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="is_active"
                                    checked={form.data.is_active}
                                    onCheckedChange={(checked) => form.setData('is_active', checked === true)}
                                />
                                <Label htmlFor="is_active" className="cursor-pointer">
                                    Active (visible to users)
                                </Label>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Content</CardTitle>
                    <CardDescription>Write the insight summary and full content</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="space-y-2">
                        <div className="flex justify-between">
                            <Label htmlFor="summary">Summary *</Label>
                            <span className={`text-xs ${summaryRemaining < 0 ? 'text-red-500' : 'text-neutral-500'}`}>
                                {summaryRemaining} characters remaining
                            </span>
                        </div>
                        <Textarea
                            id="summary"
                            value={form.data.summary}
                            onChange={(e) => form.setData('summary', e.target.value)}
                            placeholder="A brief summary shown in lists and pre-drill cards..."
                            rows={3}
                            maxLength={500}
                        />
                        {form.errors.summary && (
                            <p className="text-sm text-red-500">{form.errors.summary}</p>
                        )}
                        <p className="text-xs text-neutral-500">
                            This is shown in the pre-drill insight card. Keep it concise.
                        </p>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="content">Full Content *</Label>
                        <Textarea
                            id="content"
                            value={form.data.content}
                            onChange={(e) => form.setData('content', e.target.value)}
                            placeholder="The full educational content of this insight..."
                            rows={12}
                            className="font-mono text-sm"
                        />
                        {form.errors.content && (
                            <p className="text-sm text-red-500">{form.errors.content}</p>
                        )}
                        <p className="text-xs text-neutral-500">
                            The full content shown when users click "Read more". Supports line breaks.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <div className="flex gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : isEdit ? 'Update Insight' : 'Create Insight'}
                </Button>
                <Link href="/admin/insights">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
            </div>
        </form>
    );
}
