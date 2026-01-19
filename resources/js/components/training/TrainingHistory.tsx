import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type Message, type Card as CardType } from '@/types/training';
import { ChevronDown, ChevronUp, User, Bot } from 'lucide-react';
import { cn } from '@/lib/utils';

interface TrainingHistoryProps {
    messages: Message[];
}

function getCardTypeLabel(type: CardType['type']): string {
    const labels: Record<CardType['type'], string> = {
        scenario: 'Scenario',
        prompt: 'Question',
        multiple_choice: 'Choice',
        insight: 'Insight',
        reflection: 'Reflection',
    };
    return labels[type] || type;
}

function MiniCard({ card }: { card: CardType }) {
    return (
        <div className="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-100 dark:border-blue-800">
            <span className="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide">
                {getCardTypeLabel(card.type)}
            </span>
            <p className="mt-1 text-sm text-neutral-700 dark:text-neutral-300 line-clamp-3">
                {card.content}
            </p>
            {card.type === 'multiple_choice' && (
                <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                    {card.options.length} options
                </p>
            )}
        </div>
    );
}

function UserMessageDisplay({ content }: { content: string }) {
    return (
        <div className="bg-neutral-100 dark:bg-neutral-800 rounded-lg p-3">
            <p className="text-sm text-neutral-700 dark:text-neutral-300">
                {content}
            </p>
        </div>
    );
}

export function TrainingHistory({ messages }: TrainingHistoryProps) {
    const [isExpanded, setIsExpanded] = useState(false);

    if (messages.length === 0) {
        return null;
    }

    return (
        <div className="w-full max-w-2xl mx-auto">
            <Button
                variant="ghost"
                size="sm"
                onClick={() => setIsExpanded(!isExpanded)}
                className="w-full justify-between text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300"
            >
                <span className="text-sm">
                    Session History ({messages.length} {messages.length === 1 ? 'message' : 'messages'})
                </span>
                {isExpanded ? (
                    <ChevronUp className="h-4 w-4" />
                ) : (
                    <ChevronDown className="h-4 w-4" />
                )}
            </Button>

            {isExpanded && (
                <Card className="mt-2">
                    <CardContent className="pt-4 space-y-4 max-h-96 overflow-y-auto">
                        {messages.map((message) => (
                            <div key={message.id} className="flex items-start gap-3">
                                <div className={cn(
                                    'flex h-8 w-8 items-center justify-center rounded-full shrink-0',
                                    message.role === 'user'
                                        ? 'bg-neutral-200 dark:bg-neutral-700'
                                        : 'bg-blue-100 dark:bg-blue-900/30'
                                )}>
                                    {message.role === 'user' ? (
                                        <User className="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                    ) : (
                                        <Bot className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    )}
                                </div>
                                <div className="flex-1 min-w-0">
                                    {message.role === 'user' ? (
                                        <UserMessageDisplay content={message.content} />
                                    ) : (
                                        <MiniCard card={message.card} />
                                    )}
                                </div>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
