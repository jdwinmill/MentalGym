import { useState } from 'react';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { type PromptCard as PromptCardType } from '@/types/training';
import { Send } from 'lucide-react';

interface PromptCardProps {
    card: PromptCardType;
    onSubmit: (input: string) => void;
    characterLimit: number;
    isLoading?: boolean;
}

export function PromptCard({ card, onSubmit, characterLimit, isLoading }: PromptCardProps) {
    const [input, setInput] = useState('');
    const remainingChars = characterLimit - input.length;
    const isOverLimit = remainingChars < 0;
    const canSubmit = input.trim().length > 0 && !isOverLimit && !isLoading;

    const handleSubmit = () => {
        if (canSubmit) {
            onSubmit(input.trim());
            setInput('');
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && (e.metaKey || e.ctrlKey) && canSubmit) {
            e.preventDefault();
            handleSubmit();
        }
    };

    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardContent className="pt-6 space-y-4">
                <div className="prose prose-neutral dark:prose-invert max-w-none">
                    <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">
                        {card.content}
                    </p>
                </div>
                <div className="space-y-2">
                    <Textarea
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyDown={handleKeyDown}
                        placeholder="Type your response..."
                        className="min-h-[120px] resize-none"
                        disabled={isLoading}
                    />
                    <div className="flex justify-end">
                        <span className={`text-xs ${isOverLimit ? 'text-red-500' : 'text-neutral-500'}`}>
                            {remainingChars} characters remaining
                        </span>
                    </div>
                </div>
            </CardContent>
            <CardFooter className="justify-end">
                <Button onClick={handleSubmit} disabled={!canSubmit}>
                    {isLoading ? 'Sending...' : 'Submit'}
                    <Send className="ml-2 h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
