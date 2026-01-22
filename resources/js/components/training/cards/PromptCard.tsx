import { useState } from 'react';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { type PromptCard as PromptCardType } from '@/types/training';
import { Send } from 'lucide-react';
import { TimerDisplay } from '@/components/training/TimerDisplay';

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
        <Card className="w-full max-w-3xl mx-auto">
            <CardContent className="p-6 md:p-8 space-y-6">
                {/* Scenario context if consolidated */}
                {card.scenarioContext && (
                    <div className="bg-neutral-100 dark:bg-neutral-800 p-4 md:p-5 rounded-lg border-l-4 border-primary">
                        <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-2 uppercase tracking-wide">
                            Scenario
                        </p>
                        <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap leading-relaxed">
                            {card.scenarioContext}
                        </p>
                    </div>
                )}

                {/* Prompt question */}
                <div>
                    {card.scenarioContext && (
                        <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-3 uppercase tracking-wide">
                            Your Task
                        </p>
                    )}
                    <p className="text-lg text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap leading-relaxed max-w-prose">
                        {card.content}
                    </p>
                </div>

                {/* Timer display (above textarea, per design spec) */}
                {card.ui_hints?.timed && card.ui_hints.timer_seconds && (
                    <TimerDisplay seconds={card.ui_hints.timer_seconds} />
                )}

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
            <CardFooter className="justify-end px-6 md:px-8 pb-6 md:pb-8">
                <Button onClick={handleSubmit} disabled={!canSubmit}>
                    {isLoading ? 'Sending...' : 'Submit'}
                    <Send className="ml-2 h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
