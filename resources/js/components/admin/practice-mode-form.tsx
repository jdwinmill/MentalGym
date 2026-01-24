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
import { Plus, Trash2, ChevronDown, ChevronUp } from 'lucide-react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';

interface PracticeModeConfig {
    input_character_limit: number;
    reflection_character_limit: number;
    max_response_tokens: number;
    max_history_exchanges: number;
    model: string;
}

interface DrillData {
    id?: number;
    name: string;
    position: number;
    timer_seconds: number | null;
    input_type: 'text' | 'multiple_choice';
    scenario_instruction_set: string;
    evaluation_instruction_set: string;
    primary_insight_id: number | null;
}

interface InsightOption {
    id: number;
    name: string;
}

interface PrincipleWithInsights {
    id: number;
    name: string;
    insights: InsightOption[];
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
    drills?: DrillData[];
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
    insightsByPrinciple?: PrincipleWithInsights[];
    contextFields?: Record<string, string>;
    selectedContext?: string[];
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

const defaultDrill: DrillData = {
    name: '',
    position: 0,
    timer_seconds: 60,
    input_type: 'text',
    scenario_instruction_set: '',
    evaluation_instruction_set: '',
    primary_insight_id: null,
};

export default function PracticeModeForm({ mode, isEdit = false, tagsByCategory = {}, selectedTags = [], insightsByPrinciple = [], contextFields = {}, selectedContext = [] }: Props) {
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
        required_context: selectedContext,
        drills: mode?.drills || [],
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

    const toggleContext = (field: string) => {
        const currentContext = form.data.required_context;
        if (currentContext.includes(field)) {
            form.setData('required_context', currentContext.filter((f) => f !== field));
        } else {
            form.setData('required_context', [...currentContext, field]);
        }
    };

    // Drill management functions
    const addDrill = () => {
        const newDrill: DrillData = {
            ...defaultDrill,
            position: form.data.drills.length,
        };
        form.setData('drills', [...form.data.drills, newDrill]);
    };

    const removeDrill = (index: number) => {
        const newDrills = form.data.drills.filter((_, i) => i !== index);
        // Update positions
        newDrills.forEach((drill, i) => {
            drill.position = i;
        });
        form.setData('drills', newDrills);
    };

    const updateDrill = (index: number, field: keyof DrillData, value: string | number | null) => {
        const newDrills = [...form.data.drills];
        newDrills[index] = { ...newDrills[index], [field]: value };
        form.setData('drills', newDrills);
    };

    const moveDrill = (index: number, direction: 'up' | 'down') => {
        const newDrills = [...form.data.drills];
        const newIndex = direction === 'up' ? index - 1 : index + 1;
        if (newIndex < 0 || newIndex >= newDrills.length) return;

        // Swap positions
        [newDrills[index], newDrills[newIndex]] = [newDrills[newIndex], newDrills[index]];
        // Update position values
        newDrills.forEach((drill, i) => {
            drill.position = i;
        });
        form.setData('drills', newDrills);
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

            {/* Required Context */}
            {Object.keys(contextFields).length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>Required User Context</CardTitle>
                        <CardDescription>
                            Select which user profile fields should be injected into prompts.
                            Use {"{{field_name}}"} placeholders in your instruction set (e.g., {"{{job_title}}"}, {"{{industry}}"}).
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-wrap gap-2">
                            {Object.entries(contextFields).map(([field, label]) => (
                                <button
                                    key={field}
                                    type="button"
                                    onClick={() => toggleContext(field)}
                                    className={`px-3 py-1.5 text-sm rounded-full border transition-colors ${
                                        form.data.required_context.includes(field)
                                            ? 'bg-primary text-primary-foreground border-primary'
                                            : 'bg-background hover:bg-accent border-border'
                                    }`}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                        {form.data.required_context.length > 0 && (
                            <p className="text-xs text-neutral-500 mt-3">
                                Available placeholders: {form.data.required_context.map(f => `{{${f}}}`).join(', ')}
                            </p>
                        )}
                        {form.errors.required_context && (
                            <p className="text-sm text-red-500 mt-2">{form.errors.required_context}</p>
                        )}
                    </CardContent>
                </Card>
            )}

            {/* Instruction Set */}
            <Card>
                <CardHeader>
                    <CardTitle>Mode Instruction Set *</CardTitle>
                    <CardDescription>
                        The base system prompt for this mode. This is combined with the global instruction set
                        and individual drill instructions.
                        Use {"{{level}}"} placeholder for the user's current level.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Textarea
                        id="instruction_set"
                        value={form.data.instruction_set}
                        onChange={(e) => form.setData('instruction_set', e.target.value)}
                        placeholder="You are a decision-making coach running a focused training session..."
                        rows={10}
                        className="font-mono text-sm"
                    />
                    {form.errors.instruction_set && (
                        <p className="text-sm text-red-500 mt-2">{form.errors.instruction_set}</p>
                    )}
                </CardContent>
            </Card>

            {/* Drills */}
            <Card>
                <CardHeader>
                    <CardTitle>Drills</CardTitle>
                    <CardDescription>
                        Define the sequence of drills for this practice mode. Each drill has its own
                        scenario generation and evaluation instructions.
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    {form.data.drills.length === 0 ? (
                        <div className="text-center py-8 text-neutral-500 border-2 border-dashed rounded-lg">
                            <p>No drills configured yet.</p>
                            <p className="text-sm">Add drills to define the training sequence.</p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {form.data.drills.map((drill, index) => (
                                <Collapsible key={drill.id || `new-${index}`} defaultOpen={!drill.id}>
                                    <div className="border rounded-lg">
                                        <div className="flex items-center gap-2 p-3 bg-neutral-50 dark:bg-neutral-900 rounded-t-lg">
                                            <div className="flex flex-col gap-1">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-5 w-5 p-0"
                                                    onClick={() => moveDrill(index, 'up')}
                                                    disabled={index === 0}
                                                >
                                                    <ChevronUp className="h-3 w-3" />
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-5 w-5 p-0"
                                                    onClick={() => moveDrill(index, 'down')}
                                                    disabled={index === form.data.drills.length - 1}
                                                >
                                                    <ChevronDown className="h-3 w-3" />
                                                </Button>
                                            </div>
                                            <span className="text-sm font-medium text-neutral-500 w-6">
                                                {index + 1}.
                                            </span>
                                            <CollapsibleTrigger asChild>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    className="flex-1 justify-start font-medium"
                                                >
                                                    {drill.name || 'Unnamed Drill'}
                                                </Button>
                                            </CollapsibleTrigger>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => removeDrill(index)}
                                                className="text-red-500 hover:text-red-700 hover:bg-red-50"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <CollapsibleContent>
                                            <div className="p-4 space-y-4 border-t">
                                                <div className="grid grid-cols-3 gap-4">
                                                    <div className="space-y-2">
                                                        <Label>Drill Name *</Label>
                                                        <Input
                                                            value={drill.name}
                                                            onChange={(e) => updateDrill(index, 'name', e.target.value)}
                                                            placeholder="e.g., Compression"
                                                        />
                                                    </div>
                                                    <div className="space-y-2">
                                                        <Label>Timer (seconds)</Label>
                                                        <Input
                                                            type="number"
                                                            min={0}
                                                            max={600}
                                                            value={drill.timer_seconds ?? ''}
                                                            onChange={(e) => updateDrill(index, 'timer_seconds', e.target.value ? parseInt(e.target.value) : null)}
                                                            placeholder="60"
                                                        />
                                                        <p className="text-xs text-neutral-500">Leave empty for no timer</p>
                                                    </div>
                                                    <div className="space-y-2">
                                                        <Label>Input Type *</Label>
                                                        <Select
                                                            value={drill.input_type}
                                                            onValueChange={(value) => updateDrill(index, 'input_type', value)}
                                                        >
                                                            <SelectTrigger>
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="text">Text Response</SelectItem>
                                                                <SelectItem value="multiple_choice">Multiple Choice</SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </div>
                                                </div>

                                                {insightsByPrinciple.length > 0 && (
                                                    <div className="space-y-2">
                                                        <Label>Primary Insight</Label>
                                                        <Select
                                                            value={drill.primary_insight_id?.toString() || 'none'}
                                                            onValueChange={(value) => updateDrill(index, 'primary_insight_id', value === 'none' ? null : parseInt(value))}
                                                        >
                                                            <SelectTrigger>
                                                                <SelectValue placeholder="Select an insight (optional)" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="none">No insight</SelectItem>
                                                                {insightsByPrinciple.map((principle) => (
                                                                    principle.insights.length > 0 && (
                                                                        <div key={principle.id}>
                                                                            <div className="px-2 py-1.5 text-xs font-semibold text-neutral-500 bg-neutral-50 dark:bg-neutral-800">
                                                                                {principle.name}
                                                                            </div>
                                                                            {principle.insights.map((insight) => (
                                                                                <SelectItem key={insight.id} value={insight.id.toString()}>
                                                                                    {insight.name}
                                                                                </SelectItem>
                                                                            ))}
                                                                        </div>
                                                                    )
                                                                ))}
                                                            </SelectContent>
                                                        </Select>
                                                        <p className="text-xs text-neutral-500">
                                                            This insight will be shown before the drill scenario
                                                        </p>
                                                    </div>
                                                )}

                                                <div className="space-y-2">
                                                    <Label>Scenario Instruction Set *</Label>
                                                    <Textarea
                                                        value={drill.scenario_instruction_set}
                                                        onChange={(e) => updateDrill(index, 'scenario_instruction_set', e.target.value)}
                                                        placeholder="Instructions for generating the scenario and task..."
                                                        rows={8}
                                                        className="font-mono text-sm"
                                                    />
                                                    <p className="text-xs text-neutral-500">
                                                        Instructions for generating the drill scenario. Should output JSON with "scenario" and "task" fields.
                                                    </p>
                                                </div>

                                                <div className="space-y-2">
                                                    <Label>Evaluation Instruction Set *</Label>
                                                    <Textarea
                                                        value={drill.evaluation_instruction_set}
                                                        onChange={(e) => updateDrill(index, 'evaluation_instruction_set', e.target.value)}
                                                        placeholder="Instructions for evaluating the user's response..."
                                                        rows={8}
                                                        className="font-mono text-sm"
                                                    />
                                                    <p className="text-xs text-neutral-500">
                                                        Instructions for evaluating the response. Should output JSON with "feedback" and "score" fields.
                                                    </p>
                                                </div>
                                            </div>
                                        </CollapsibleContent>
                                    </div>
                                </Collapsible>
                            ))}
                        </div>
                    )}

                    <Button
                        type="button"
                        variant="outline"
                        onClick={addDrill}
                        className="w-full"
                    >
                        <Plus className="h-4 w-4 mr-2" />
                        Add Drill
                    </Button>

                    {form.errors.drills && (
                        <p className="text-sm text-red-500">{form.errors.drills}</p>
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
