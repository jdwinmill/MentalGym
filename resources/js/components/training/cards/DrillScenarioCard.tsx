import { useState } from 'react';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { type DrillScenarioCard } from '@/types/training';
import { Send } from 'lucide-react';
import { TimerDisplay } from '@/components/training/TimerDisplay';

interface DrillScenarioCardProps {
    card: DrillScenarioCard;
    timerSeconds: number | null;
    inputType: 'text' | 'multiple_choice';
    onSubmit: (response: string) => void;
    isLoading?: boolean;
}

export function DrillScenarioCardComponent({
    card,
    timerSeconds,
    inputType,
    onSubmit,
    isLoading,
}: DrillScenarioCardProps) {
    const [response, setResponse] = useState('');
    const [selectedOption, setSelectedOption] = useState<number | null>(null);

    const canSubmit = inputType === 'multiple_choice'
        ? selectedOption !== null && !isLoading
        : response.trim().length > 0 && !isLoading;

    const handleSubmit = () => {
        if (!canSubmit) return;

        if (inputType === 'multiple_choice' && selectedOption !== null) {
            onSubmit(String(selectedOption));
        } else if (response.trim()) {
            onSubmit(response.trim());
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
                {/* Scenario content */}
                <div className="bg-neutral-100 dark:bg-neutral-800 p-4 md:p-5 rounded-lg border-l-4 border-primary">
                    <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-2 uppercase tracking-wide">
                        Scenario
                    </p>
                    <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap leading-relaxed">
                        {card.content}
                    </p>
                </div>

                {/* Task */}
                <div>
                    <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-3 uppercase tracking-wide">
                        Your Task
                    </p>
                    <p className="text-lg text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap leading-relaxed max-w-prose">
                        {card.task}
                    </p>
                </div>

                {/* Timer display */}
                {timerSeconds && <TimerDisplay seconds={timerSeconds} />}

                {/* Input area */}
                {inputType === 'multiple_choice' && card.options ? (
                    <div className="space-y-2">
                        {card.options.map((option, index) => (
                            <Button
                                key={index}
                                variant={selectedOption === index ? 'default' : 'outline'}
                                className="w-full justify-start text-left h-auto py-3 px-4"
                                onClick={() => setSelectedOption(index)}
                                disabled={isLoading}
                            >
                                <span className="mr-3 font-mono text-sm shrink-0">
                                    {String.fromCharCode(65 + index)}.
                                </span>
                                <span className="whitespace-pre-wrap">{option}</span>
                            </Button>
                        ))}
                    </div>
                ) : (
                    <div className="space-y-2">
                        <Textarea
                            value={response}
                            onChange={(e) => setResponse(e.target.value)}
                            onKeyDown={handleKeyDown}
                            placeholder="Type your response..."
                            className="min-h-[150px] resize-none"
                            disabled={isLoading}
                        />
                    </div>
                )}
            </CardContent>

            <CardFooter className="justify-end px-6 md:px-8 pb-6 md:pb-8">
                <Button onClick={handleSubmit} disabled={!canSubmit}>
                    {isLoading ? 'Submitting...' : 'Submit'}
                    <Send className="ml-2 h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
