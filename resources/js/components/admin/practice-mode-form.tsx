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

interface PracticeModeConfig {
    input_character_limit: number;
    reflection_character_limit: number;
    max_response_tokens: number;
    max_history_exchanges: number;
    model: string;
}

interface PracticeModeData {
    id?: number;
    name: string;
    slug: string;
    tagline: string;
    description: string;
    instruction_set: string;
    config: PracticeModeConfig;
    required_plan: string | null;
    is_active: boolean;
    sort_order: number;
}

interface Tag {
    id: number;
    name: string;
    slug: string;
    category: string;
    display_order: number;
}

interface Props {
    mode?: PracticeModeData;
    isEdit?: boolean;
    tagsByCategory?: Record<string, Tag[]>;
    selectedTags?: number[];
}

const categoryLabels: Record<string, string> = {
    skill: 'Skills',
    context: 'Context',
    duration: 'Duration',
    role: 'Role',
};

const categoryOrder = ['skill', 'context', 'duration', 'role'];

const defaultConfig: PracticeModeConfig = {
    input_character_limit: 500,
    reflection_character_limit: 200,
    max_response_tokens: 800,
    max_history_exchanges: 10,
    model: 'claude-sonnet-4-20250514',
};

export default function PracticeModeForm({ mode, isEdit = false, tagsByCategory = {}, selectedTags = [] }: Props) {
    const form = useForm({
        name: mode?.name || '',
        slug: mode?.slug || '',
        tagline: mode?.tagline || '',
        description: mode?.description || '',
        instruction_set: mode?.instruction_set || '',
        config: mode?.config || defaultConfig,
        required_plan: mode?.required_plan || 'all',
        is_active: mode?.is_active ?? false,
        sort_order: mode?.sort_order ?? 0,
        tags: selectedTags,
    });

    // Auto-generate slug from name (only on create, when user types in name field)
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

        // Use transform to convert 'all' required_plan to null for backend
        form.transform((data) => ({
            ...data,
            required_plan: data.required_plan === 'all' ? null : data.required_plan,
        }));

        if (isEdit && mode?.id) {
            form.put(`/admin/practice-modes/${mode.id}`);
        } else {
            form.post('/admin/practice-modes');
        }
    };

    const updateConfig = (key: keyof PracticeModeConfig, value: number | string) => {
        form.setData('config', {
            ...form.data.config,
            [key]: value,
        });
    };

    const toggleTag = (tagId: number) => {
        const currentTags = form.data.tags;
        if (currentTags.includes(tagId)) {
            form.setData('tags', currentTags.filter((id) => id !== tagId));
        } else {
            form.setData('tags', [...currentTags, tagId]);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {/* Basic Information */}
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                    <CardDescription>Core details about this practice mode</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Name *</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(e) => handleNameChange(e.target.value)}
                                placeholder="e.g., MBA+ Decision Lab"
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
                        <Label htmlFor="tagline">Tagline</Label>
                        <Input
                            id="tagline"
                            value={form.data.tagline}
                            onChange={(e) => form.setData('tagline', e.target.value)}
                            placeholder="Short description shown on mode cards"
                        />
                        {form.errors.tagline && (
                            <p className="text-sm text-red-500">{form.errors.tagline}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                            placeholder="Longer explanation of this practice mode"
                            rows={3}
                        />
                        {form.errors.description && (
                            <p className="text-sm text-red-500">{form.errors.description}</p>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Instruction Set */}
            <Card>
                <CardHeader>
                    <CardTitle>Instruction Set *</CardTitle>
                    <CardDescription>
                        The system prompt that defines how Claude behaves in this mode.
                        Use {"{{level}}"} placeholder for the user's current level.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Textarea
                        id="instruction_set"
                        value={form.data.instruction_set}
                        onChange={(e) => form.setData('instruction_set', e.target.value)}
                        placeholder="You are a decision-making coach running a focused training session..."
                        rows={15}
                        className="font-mono text-sm"
                    />
                    {form.errors.instruction_set && (
                        <p className="text-sm text-red-500 mt-2">{form.errors.instruction_set}</p>
                    )}
                </CardContent>
            </Card>

            {/* Configuration */}
            <Card>
                <CardHeader>
                    <CardTitle>Session Configuration</CardTitle>
                    <CardDescription>Settings that control the training session behavior</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="input_character_limit">Input Character Limit</Label>
                            <Input
                                id="input_character_limit"
                                type="number"
                                min={100}
                                max={2000}
                                value={form.data.config.input_character_limit}
                                onChange={(e) => updateConfig('input_character_limit', parseInt(e.target.value) || 500)}
                            />
                            <p className="text-xs text-neutral-500">100-2000 characters</p>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="reflection_character_limit">Reflection Character Limit</Label>
                            <Input
                                id="reflection_character_limit"
                                type="number"
                                min={50}
                                max={500}
                                value={form.data.config.reflection_character_limit}
                                onChange={(e) => updateConfig('reflection_character_limit', parseInt(e.target.value) || 200)}
                            />
                            <p className="text-xs text-neutral-500">50-500 characters</p>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="max_response_tokens">Max Response Tokens</Label>
                            <Input
                                id="max_response_tokens"
                                type="number"
                                min={200}
                                max={2000}
                                value={form.data.config.max_response_tokens}
                                onChange={(e) => updateConfig('max_response_tokens', parseInt(e.target.value) || 800)}
                            />
                            <p className="text-xs text-neutral-500">200-2000 tokens</p>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="max_history_exchanges">Max History Exchanges</Label>
                            <Input
                                id="max_history_exchanges"
                                type="number"
                                min={5}
                                max={24}
                                value={form.data.config.max_history_exchanges}
                                onChange={(e) => updateConfig('max_history_exchanges', parseInt(e.target.value) || 10)}
                            />
                            <p className="text-xs text-neutral-500">5-24 exchanges kept in context</p>
                        </div>
                        <div className="space-y-2 col-span-2">
                            <Label htmlFor="model">Model</Label>
                            <Select
                                value={form.data.config.model}
                                onValueChange={(value) => updateConfig('model', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a model" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="claude-sonnet-4-20250514">
                                        Claude Sonnet 4 (Default)
                                    </SelectItem>
                                    <SelectItem value="claude-haiku-4-20250414">
                                        Claude Haiku 4 (Faster, cheaper)
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Access & Display */}
            <Card>
                <CardHeader>
                    <CardTitle>Access & Display</CardTitle>
                    <CardDescription>Control who can access this mode and how it appears</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="required_plan">Required Plan</Label>
                            <Select
                                value={form.data.required_plan}
                                onValueChange={(value) => form.setData('required_plan', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="All Users" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Users</SelectItem>
                                    <SelectItem value="pro">Pro+ Only</SelectItem>
                                    <SelectItem value="unlimited">Unlimited Only</SelectItem>
                                </SelectContent>
                            </Select>
                            <p className="text-xs text-neutral-500">
                                Which subscription tier is required to access this mode
                            </p>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="sort_order">Display Order</Label>
                            <Input
                                id="sort_order"
                                type="number"
                                min={0}
                                value={form.data.sort_order}
                                onChange={(e) => form.setData('sort_order', parseInt(e.target.value) || 0)}
                            />
                            <p className="text-xs text-neutral-500">
                                Lower numbers appear first in the list
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
                    <p className="text-xs text-neutral-500">
                        Inactive modes are hidden from users but can still be edited by admins.
                    </p>
                </CardContent>
            </Card>

            {/* Tags */}
            {Object.keys(tagsByCategory).length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>Tags</CardTitle>
                        <CardDescription>
                            Categorize this practice mode for filtering and discovery
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {categoryOrder.map((category) => {
                            const tags = tagsByCategory[category] || [];
                            if (tags.length === 0) return null;

                            return (
                                <div key={category} className="space-y-2">
                                    <Label className="text-sm font-medium">
                                        {categoryLabels[category]}
                                    </Label>
                                    <div className="flex flex-wrap gap-2">
                                        {tags.map((tag) => (
                                            <div
                                                key={tag.id}
                                                className="flex items-center space-x-2"
                                            >
                                                <Checkbox
                                                    id={`tag-${tag.id}`}
                                                    checked={form.data.tags.includes(tag.id)}
                                                    onCheckedChange={() => toggleTag(tag.id)}
                                                />
                                                <Label
                                                    htmlFor={`tag-${tag.id}`}
                                                    className="text-sm cursor-pointer"
                                                >
                                                    {tag.name}
                                                </Label>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            );
                        })}
                        {form.errors.tags && (
                            <p className="text-sm text-red-500">{form.errors.tags}</p>
                        )}
                    </CardContent>
                </Card>
            )}

            {/* Actions */}
            <div className="flex gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : isEdit ? 'Update Mode' : 'Create Mode'}
                </Button>
                <Link href="/admin/practice-modes">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
            </div>
        </form>
    );
}
