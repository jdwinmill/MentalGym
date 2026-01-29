import { useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Loader2, Plus, X } from 'lucide-react';

export interface FieldMeta {
    key: string;
    label: string;
    type: 'text' | 'number' | 'select' | 'checkbox' | 'multiselect' | 'year_array';
    placeholder?: string;
    min?: number;
    max?: number;
    options?: Record<string, string>;
}

interface RequiredContextModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: (data: Record<string, unknown>) => Promise<void>;
    fields: FieldMeta[];
    modeName: string;
}

export function RequiredContextModal({
    isOpen,
    onClose,
    onSubmit,
    fields,
    modeName,
}: RequiredContextModalProps) {
    const [formData, setFormData] = useState<Record<string, unknown>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [yearInputs, setYearInputs] = useState<Record<string, string>>({});

    const handleChange = (key: string, value: unknown) => {
        setFormData((prev) => ({ ...prev, [key]: value }));
    };

    const handleYearInputChange = (key: string, value: string) => {
        // Only allow digits and max 4 characters
        const filtered = value.replace(/\D/g, '').slice(0, 4);
        setYearInputs((prev) => ({ ...prev, [key]: filtered }));
    };

    const handleAddYear = (key: string) => {
        const yearStr = yearInputs[key];
        if (!yearStr || yearStr.length !== 4) return;

        const year = parseInt(yearStr);
        const currentYear = new Date().getFullYear();
        if (year < 1900 || year > currentYear) return;

        const current = (formData[key] as number[]) || [];
        if (!current.includes(year)) {
            setFormData((prev) => ({ ...prev, [key]: [...current, year].sort((a, b) => b - a) }));
        }
        setYearInputs((prev) => ({ ...prev, [key]: '' }));
    };

    const handleRemoveYear = (key: string, year: number) => {
        const current = (formData[key] as number[]) || [];
        setFormData((prev) => ({ ...prev, [key]: current.filter((y) => y !== year) }));
    };

    const handleMultiselectToggle = (key: string, optionKey: string) => {
        const current = (formData[key] as string[]) || [];
        const updated = current.includes(optionKey)
            ? current.filter((k) => k !== optionKey)
            : [...current, optionKey];
        setFormData((prev) => ({ ...prev, [key]: updated }));
    };

    const handleSubmit = async () => {
        setIsSubmitting(true);
        setError(null);

        try {
            // Build final data with proper defaults for array fields
            const finalData: Record<string, unknown> = { ...formData };

            for (const field of fields) {
                if (field.type === 'year_array' || field.type === 'multiselect') {
                    // Ensure array fields have at least an empty array
                    if (finalData[field.key] === undefined) {
                        finalData[field.key] = [];
                    }
                }
            }

            // If has_kids is false, clear kid_birth_years
            if (finalData['has_kids'] === false) {
                finalData['kid_birth_years'] = [];
            }

            await onSubmit(finalData);
        } catch (err) {
            const message = err instanceof Error ? err.message : 'Failed to save. Please try again.';
            setError(message);
            console.error('Submit error:', err);
        } finally {
            setIsSubmitting(false);
        }
    };

    const renderField = (field: FieldMeta) => {
        switch (field.type) {
            case 'text':
                return (
                    <Input
                        id={field.key}
                        type="text"
                        placeholder={field.placeholder}
                        value={(formData[field.key] as string) || ''}
                        onChange={(e) => handleChange(field.key, e.target.value)}
                    />
                );

            case 'number':
                return (
                    <Input
                        id={field.key}
                        type="number"
                        placeholder={field.placeholder}
                        min={field.min}
                        max={field.max}
                        value={(formData[field.key] as number) || ''}
                        onChange={(e) =>
                            handleChange(field.key, e.target.value ? parseInt(e.target.value) : null)
                        }
                    />
                );

            case 'select':
                return (
                    <Select
                        value={(formData[field.key] as string) || ''}
                        onValueChange={(value) => handleChange(field.key, value)}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Select..." />
                        </SelectTrigger>
                        <SelectContent>
                            {field.options &&
                                Object.entries(field.options).map(([value, label]) => (
                                    <SelectItem key={value} value={value}>
                                        {label}
                                    </SelectItem>
                                ))}
                        </SelectContent>
                    </Select>
                );

            case 'checkbox':
                return (
                    <div className="flex items-center space-x-2">
                        <Checkbox
                            id={field.key}
                            checked={(formData[field.key] as boolean) || false}
                            onCheckedChange={(checked) => handleChange(field.key, checked)}
                        />
                        <label
                            htmlFor={field.key}
                            className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Yes
                        </label>
                    </div>
                );

            case 'multiselect': {
                const selected = (formData[field.key] as string[]) || [];
                return (
                    <div className="grid grid-cols-2 gap-2">
                        {field.options &&
                            Object.entries(field.options).map(([value, label]) => (
                                <div key={value} className="flex items-center space-x-2">
                                    <Checkbox
                                        id={`${field.key}-${value}`}
                                        checked={selected.includes(value)}
                                        onCheckedChange={() =>
                                            handleMultiselectToggle(field.key, value)
                                        }
                                    />
                                    <label
                                        htmlFor={`${field.key}-${value}`}
                                        className="text-sm leading-none"
                                    >
                                        {label}
                                    </label>
                                </div>
                            ))}
                    </div>
                );
            }

            case 'year_array': {
                const years = (formData[field.key] as number[]) || [];
                const currentYear = new Date().getFullYear();
                return (
                    <div className="space-y-2">
                        <div className="flex gap-2">
                            <Input
                                id={field.key}
                                type="text"
                                inputMode="numeric"
                                placeholder={field.placeholder || 'e.g., 2015'}
                                value={yearInputs[field.key] || ''}
                                onChange={(e) => handleYearInputChange(field.key, e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        handleAddYear(field.key);
                                    }
                                }}
                                className="flex-1"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                onClick={() => handleAddYear(field.key)}
                                disabled={!yearInputs[field.key] || yearInputs[field.key].length !== 4}
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        </div>
                        {years.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {years.map((year) => (
                                    <span
                                        key={year}
                                        className="inline-flex items-center gap-1 rounded-full bg-secondary px-3 py-1 text-sm"
                                    >
                                        {year} ({currentYear - year} yrs)
                                        <button
                                            type="button"
                                            onClick={() => handleRemoveYear(field.key, year)}
                                            className="ml-1 rounded-full hover:bg-muted p-0.5"
                                        >
                                            <X className="h-3 w-3" />
                                        </button>
                                    </span>
                                ))}
                            </div>
                        )}
                        <p className="text-xs text-muted-foreground">
                            Optional. Enter birth year and press Enter or click + to add.
                        </p>
                    </div>
                );
            }

            default:
                return null;
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Complete Your Profile</DialogTitle>
                    <DialogDescription>
                        {modeName} needs a bit more information about you to personalize your
                        training scenarios.
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4 py-4">
                    {fields.map((field) => {
                        // Skip kid_birth_years - it's rendered inline with has_kids
                        if (field.key === 'kid_birth_years') return null;

                        // For has_kids, also render kid_birth_years conditionally
                        if (field.key === 'has_kids') {
                            const birthYearsField = fields.find((f) => f.key === 'kid_birth_years');
                            return (
                                <div key={field.key} className="space-y-3">
                                    <div className="space-y-2">
                                        <Label htmlFor={field.key}>{field.label}</Label>
                                        {renderField(field)}
                                    </div>
                                    {formData['has_kids'] === true && birthYearsField && (
                                        <div className="space-y-2 pl-4 border-l-2 border-muted">
                                            <Label htmlFor={birthYearsField.key}>
                                                {birthYearsField.label}
                                            </Label>
                                            {renderField(birthYearsField)}
                                        </div>
                                    )}
                                </div>
                            );
                        }

                        return (
                            <div key={field.key} className="space-y-2">
                                <Label htmlFor={field.key}>{field.label}</Label>
                                {renderField(field)}
                            </div>
                        );
                    })}

                    {error && (
                        <p className="text-sm text-red-600 dark:text-red-400">{error}</p>
                    )}
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={onClose} disabled={isSubmitting}>
                        Cancel
                    </Button>
                    <Button onClick={handleSubmit} disabled={isSubmitting}>
                        {isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                        Continue to Training
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
