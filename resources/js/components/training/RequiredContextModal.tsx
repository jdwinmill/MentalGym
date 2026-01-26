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
import { Loader2 } from 'lucide-react';

export interface FieldMeta {
    key: string;
    label: string;
    type: 'text' | 'number' | 'select' | 'checkbox' | 'multiselect';
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

    const handleChange = (key: string, value: unknown) => {
        setFormData((prev) => ({ ...prev, [key]: value }));
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
            await onSubmit(formData);
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
                    {fields.map((field) => (
                        <div key={field.key} className="space-y-2">
                            <Label htmlFor={field.key}>{field.label}</Label>
                            {renderField(field)}
                        </div>
                    ))}

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
