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

interface DimensionData {
    key: string;
    label: string;
    description: string | null;
    category: string | null;
    anchor_low: string;
    anchor_mid: string;
    anchor_high: string;
    anchor_exemplary: string;
    active: boolean;
}

interface Props {
    dimension?: DimensionData;
    isEdit?: boolean;
}

export default function SkillDimensionForm({ dimension, isEdit = false }: Props) {
    const form = useForm({
        key: dimension?.key || '',
        label: dimension?.label || '',
        description: dimension?.description || '',
        category: dimension?.category || '',
        anchor_low: dimension?.anchor_low || '',
        anchor_mid: dimension?.anchor_mid || '',
        anchor_high: dimension?.anchor_high || '',
        anchor_exemplary: dimension?.anchor_exemplary || '',
        active: dimension?.active ?? true,
    });

    const handleLabelChange = (value: string) => {
        form.setData('label', value);
        if (!isEdit) {
            const key = value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_|_$/g, '');
            form.setData('key', key);
        }
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        if (isEdit && dimension?.key) {
            form.put(`/admin/skill-dimensions/${dimension.key}`);
        } else {
            form.post('/admin/skill-dimensions');
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                    <CardDescription>Define the skill dimension identifier and name</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="label">Label *</Label>
                            <Input
                                id="label"
                                value={form.data.label}
                                onChange={(e) => handleLabelChange(e.target.value)}
                                placeholder="e.g., Active Listening"
                            />
                            {form.errors.label && (
                                <p className="text-sm text-red-500">{form.errors.label}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="key">Key</Label>
                            <Input
                                id="key"
                                value={form.data.key}
                                onChange={(e) => form.setData('key', e.target.value)}
                                placeholder="auto_generated_from_label"
                                disabled={isEdit}
                                className={isEdit ? 'bg-neutral-100 dark:bg-neutral-800' : ''}
                            />
                            {form.errors.key && (
                                <p className="text-sm text-red-500">{form.errors.key}</p>
                            )}
                            <p className="text-xs text-neutral-500">
                                Unique identifier (snake_case). Auto-generated if left empty.
                            </p>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                            placeholder="Describe what this skill dimension measures..."
                            rows={3}
                        />
                        {form.errors.description && (
                            <p className="text-sm text-red-500">{form.errors.description}</p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="category">Category</Label>
                            <Select
                                value={form.data.category}
                                onValueChange={(value) => form.setData('category', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="communication">Communication</SelectItem>
                                    <SelectItem value="reasoning">Reasoning</SelectItem>
                                    <SelectItem value="resilience">Resilience</SelectItem>
                                    <SelectItem value="influence">Influence</SelectItem>
                                    <SelectItem value="self_awareness">Self-Awareness</SelectItem>
                                </SelectContent>
                            </Select>
                            {form.errors.category && (
                                <p className="text-sm text-red-500">{form.errors.category}</p>
                            )}
                        </div>
                        <div className="space-y-2 flex items-end">
                            <div className="flex items-center space-x-2 pb-2">
                                <Checkbox
                                    id="active"
                                    checked={form.data.active}
                                    onCheckedChange={(checked) => form.setData('active', checked === true)}
                                />
                                <Label htmlFor="active" className="cursor-pointer">
                                    Active
                                </Label>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Score Anchors</CardTitle>
                    <CardDescription>
                        Define descriptions for each score range to guide evaluation
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="anchor_low">Low (1-3) *</Label>
                        <Textarea
                            id="anchor_low"
                            value={form.data.anchor_low}
                            onChange={(e) => form.setData('anchor_low', e.target.value)}
                            placeholder="Describe performance at the low end..."
                            rows={2}
                        />
                        {form.errors.anchor_low && (
                            <p className="text-sm text-red-500">{form.errors.anchor_low}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="anchor_mid">Mid (4-6) *</Label>
                        <Textarea
                            id="anchor_mid"
                            value={form.data.anchor_mid}
                            onChange={(e) => form.setData('anchor_mid', e.target.value)}
                            placeholder="Describe performance at the middle range..."
                            rows={2}
                        />
                        {form.errors.anchor_mid && (
                            <p className="text-sm text-red-500">{form.errors.anchor_mid}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="anchor_high">High (7-9) *</Label>
                        <Textarea
                            id="anchor_high"
                            value={form.data.anchor_high}
                            onChange={(e) => form.setData('anchor_high', e.target.value)}
                            placeholder="Describe performance at the high range..."
                            rows={2}
                        />
                        {form.errors.anchor_high && (
                            <p className="text-sm text-red-500">{form.errors.anchor_high}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="anchor_exemplary">Exemplary (10) *</Label>
                        <Textarea
                            id="anchor_exemplary"
                            value={form.data.anchor_exemplary}
                            onChange={(e) => form.setData('anchor_exemplary', e.target.value)}
                            placeholder="Describe exceptional, exemplary performance..."
                            rows={2}
                        />
                        {form.errors.anchor_exemplary && (
                            <p className="text-sm text-red-500">{form.errors.anchor_exemplary}</p>
                        )}
                    </div>
                </CardContent>
            </Card>

            <div className="flex gap-2">
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? 'Saving...' : isEdit ? 'Update Dimension' : 'Create Dimension'}
                </Button>
                <Link href="/admin/skill-dimensions">
                    <Button type="button" variant="outline">Cancel</Button>
                </Link>
            </div>
        </form>
    );
}
