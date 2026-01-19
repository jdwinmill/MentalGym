import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type MultipleChoiceCard } from '@/types/training';
import { cn } from '@/lib/utils';

interface ChoiceCardProps {
    card: MultipleChoiceCard;
    onSelect: (option: string) => void;
    isLoading?: boolean;
}

export function ChoiceCard({ card, onSelect, isLoading }: ChoiceCardProps) {
    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardContent className="pt-6 space-y-4">
                <div className="prose prose-neutral dark:prose-invert max-w-none">
                    <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">
                        {card.content}
                    </p>
                </div>
                <div className="space-y-2">
                    {card.options.map((option, index) => (
                        <Button
                            key={index}
                            variant="outline"
                            onClick={() => onSelect(option)}
                            disabled={isLoading}
                            className={cn(
                                'w-full justify-start text-left h-auto py-3 px-4',
                                'hover:bg-neutral-100 dark:hover:bg-neutral-800',
                                'whitespace-normal'
                            )}
                        >
                            <span className="inline-flex items-center justify-center w-6 h-6 rounded-full bg-neutral-200 dark:bg-neutral-700 text-sm font-medium mr-3 shrink-0">
                                {String.fromCharCode(65 + index)}
                            </span>
                            <span>{option}</span>
                        </Button>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
