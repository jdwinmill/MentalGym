import { useEffect, useState, useCallback } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import {
    type TrainPageProps,
    type Card,
    type Session,
    type SessionProgress,
    type Message,
    type LevelUpCard as LevelUpCardType,
    type StartSessionResponse,
    type ContinueSessionResponse,
} from '@/types/training';
import { Head, router } from '@inertiajs/react';
import { TrainingHeader } from '@/components/training/TrainingHeader';
import { TrainingHistory } from '@/components/training/TrainingHistory';
import { LoadingCard, LoadingCardSkeleton } from '@/components/training/LoadingCard';
import { CardRenderer, LevelUpRenderer } from '@/components/training/cards/CardRenderer';
import { AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function TrainPage({ mode, progress: initialProgress, has_active_session }: TrainPageProps) {
    const [session, setSession] = useState<Session | null>(null);
    const [currentCard, setCurrentCard] = useState<Card | null>(null);
    const [messages, setMessages] = useState<Message[]>([]);
    const [progress, setProgress] = useState<SessionProgress>({
        current_level: initialProgress.current_level,
        exchanges_at_current_level: initialProgress.exchanges_at_current_level ?? 0,
        exchanges_to_next_level: null,
    });
    const [isLoading, setIsLoading] = useState(false);
    const [isStarting, setIsStarting] = useState(true);
    const [isEnding, setIsEnding] = useState(false);
    const [levelUpCard, setLevelUpCard] = useState<LevelUpCardType | null>(null);
    const [error, setError] = useState<string | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Practice', href: '/practice-modes' },
        { title: mode.name, href: `/practice-modes/${mode.slug}/train` },
    ];

    // Start or resume session on mount
    const startSession = useCallback(async () => {
        setIsStarting(true);
        setError(null);

        try {
            const response = await fetch(`/api/training/start/${mode.slug}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
            });

            const data: StartSessionResponse = await response.json();

            if (!data.success) {
                setError(data.message || 'Failed to start session');
                return;
            }

            if (data.session) {
                setSession(data.session);
            }

            if (data.resumed && data.messages) {
                // Resumed session - restore messages and get last card
                setMessages(data.messages);
                const lastAssistantMessage = [...data.messages]
                    .reverse()
                    .find((m): m is Extract<Message, { role: 'assistant' }> => m.role === 'assistant');
                if (lastAssistantMessage) {
                    setCurrentCard(lastAssistantMessage.card);
                }
            } else if (data.card) {
                // New session - set first card
                setCurrentCard(data.card);
            }
        } catch (err) {
            setError('Failed to connect. Please try again.');
            console.error('Start session error:', err);
        } finally {
            setIsStarting(false);
        }
    }, [mode.slug]);

    useEffect(() => {
        startSession();
    }, [startSession]);

    // Submit user input (for prompt, reflection, or choice cards)
    const handleSubmit = async (input: string) => {
        if (!session || isLoading) return;

        setIsLoading(true);
        setError(null);

        // Add user message to history
        const userMessage: Message = {
            id: Date.now(),
            role: 'user',
            content: input,
            created_at: new Date().toISOString(),
        };
        setMessages((prev) => [...prev, userMessage]);

        try {
            const response = await fetch(`/api/training/continue/${session.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ input }),
            });

            const data: ContinueSessionResponse = await response.json();

            if (!data.success) {
                if (data.error === 'limit_reached') {
                    setError(data.message || 'Daily exchange limit reached.');
                } else {
                    setError(data.message || 'Failed to continue session');
                }
                // Remove the user message we added
                setMessages((prev) => prev.slice(0, -1));
                return;
            }

            if (data.card) {
                // Add assistant message to history
                const assistantMessage: Message = {
                    id: Date.now() + 1,
                    role: 'assistant',
                    card: data.card,
                    type: data.card.type,
                    created_at: new Date().toISOString(),
                };
                setMessages((prev) => [...prev, assistantMessage]);
                setCurrentCard(data.card);
            }

            if (data.session) {
                setSession(data.session);
            }

            if (data.progress) {
                setProgress(data.progress);
            }

            // Handle level up
            if (data.levelUp) {
                setLevelUpCard(data.levelUp);
            }
        } catch (err) {
            setError('Failed to send response. Please try again.');
            // Remove the user message we added
            setMessages((prev) => prev.slice(0, -1));
            console.error('Continue session error:', err);
        } finally {
            setIsLoading(false);
        }
    };

    // Handle continue button (for scenario, insight cards)
    const handleContinue = () => {
        // For cards that just need acknowledgment, send an empty or default response
        handleSubmit('continue');
    };

    // Handle level up card dismissal
    const handleLevelUpContinue = () => {
        setLevelUpCard(null);
        // Update progress with new level if it was a level up (not level cap)
        if (levelUpCard?.type === 'level_up' && levelUpCard.new_level) {
            setProgress((prev) => ({
                ...prev,
                current_level: levelUpCard.new_level!,
                exchanges_at_current_level: 0,
            }));
        }
    };

    // End session
    const handleEndSession = async () => {
        if (!session || isEnding) return;

        setIsEnding(true);

        try {
            await fetch(`/api/training/end/${session.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
            });

            // Navigate back to practice modes
            router.visit('/practice-modes');
        } catch (err) {
            console.error('End session error:', err);
            // Navigate anyway
            router.visit('/practice-modes');
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Training - ${mode.name}`} />

            <div className="flex flex-col h-full">
                {/* Header */}
                {session && !isStarting && (
                    <TrainingHeader
                        mode={mode}
                        progress={progress}
                        onEndSession={handleEndSession}
                        isEnding={isEnding}
                    />
                )}

                {/* Main content */}
                <div className="flex-1 overflow-y-auto">
                    <div className="p-4 md:p-6 space-y-6">
                        {/* Error state */}
                        {error && (
                            <div className="w-full max-w-2xl mx-auto">
                                <div className="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/50">
                                    <div className="flex items-start gap-3">
                                        <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                                        <div className="flex-1">
                                            <p className="text-sm text-red-700 dark:text-red-300">
                                                {error}
                                            </p>
                                            {!session && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={startSession}
                                                    className="mt-2"
                                                >
                                                    Try Again
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Starting state */}
                        {isStarting && <LoadingCardSkeleton />}

                        {/* Level up card */}
                        {levelUpCard && !isStarting && (
                            <LevelUpRenderer card={levelUpCard} onContinue={handleLevelUpContinue} />
                        )}

                        {/* Current card */}
                        {!levelUpCard && currentCard && !isStarting && (
                            <>
                                {isLoading ? (
                                    <LoadingCard />
                                ) : (
                                    <CardRenderer
                                        card={currentCard}
                                        config={mode.config}
                                        onSubmit={handleSubmit}
                                        onContinue={handleContinue}
                                        isLoading={isLoading}
                                    />
                                )}
                            </>
                        )}

                        {/* Session history */}
                        {!isStarting && messages.length > 0 && (
                            <TrainingHistory messages={messages.slice(0, -1)} />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
