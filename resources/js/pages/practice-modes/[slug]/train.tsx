import { useEffect, useState, useCallback } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import {
    type TrainPageProps,
    type DrillSession,
    type Drill,
    type DrillCard,
    type DrillProgress,
    type SessionStats,
    type PrimaryInsight,
} from '@/types/training';
import { Head, router } from '@inertiajs/react';
import { LoadingCardSkeleton } from '@/components/training/LoadingCard';
import { SessionCompleteDialog } from '@/components/training/SessionCompleteDialog';
import { LimitReachedDialog } from '@/components/training/LimitReachedDialog';
import { DrillScenarioCardComponent } from '@/components/training/cards/DrillScenarioCard';
import { FeedbackCard } from '@/components/training/cards/FeedbackCard';
import { PreDrillInsightCard } from '@/components/training/cards/PreDrillInsightCard';
import { RequiredContextModal, type FieldMeta } from '@/components/training/RequiredContextModal';
import { AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';

export default function TrainPage({ mode }: TrainPageProps) {
    // Core state
    const [session, setSession] = useState<DrillSession | null>(null);
    const [currentDrill, setCurrentDrill] = useState<Drill | null>(null);
    const [currentCard, setCurrentCard] = useState<DrillCard | null>(null);
    const [progress, setProgress] = useState<DrillProgress | null>(null);
    const [pendingInsight, setPendingInsight] = useState<PrimaryInsight | null>(null);

    // UI state
    const [isLoading, setIsLoading] = useState(false);
    const [isStarting, setIsStarting] = useState(true);
    const [error, setError] = useState<string | null>(null);

    // Completion state
    const [showCompletionDialog, setShowCompletionDialog] = useState(false);
    const [sessionStats, setSessionStats] = useState<SessionStats | null>(null);

    // Limit reached state
    const [showLimitDialog, setShowLimitDialog] = useState(false);
    const [limitPlan, setLimitPlan] = useState<string>('free');

    // Required context modal state
    const [showContextModal, setShowContextModal] = useState(false);
    const [missingFields, setMissingFields] = useState<FieldMeta[]>([]);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Practice', href: '/practice-modes' },
        { title: mode.name, href: `/practice-modes/${mode.slug}/train` },
    ];

    // Check for required context before starting
    const checkRequiredContext = useCallback(async (): Promise<boolean> => {
        try {
            const response = await fetch(`/api/training/v2/check-context/${mode.slug}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
            });

            const data = await response.json();

            if (data.success && !data.has_required_context && data.missing_fields.length > 0) {
                setMissingFields(data.missing_fields);
                setShowContextModal(true);
                return false;
            }

            return true;
        } catch (err) {
            console.error('Check context error:', err);
            // On error, proceed anyway - the session start will handle validation
            return true;
        }
    }, [mode.slug]);

    // Start the actual session (after context is verified)
    const startActualSession = useCallback(async () => {
        try {
            console.log('[Training] Starting session for mode:', mode.slug);
            const response = await fetch(`/api/training/v2/start/${mode.slug}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
            });

            const data = await response.json();
            console.log('[Training] Start session response:', data);
            if (data.card) {
                console.log('[Training] Scenario card content:', data.card.content);
            }

            // Handle limit reached
            if (data.error === 'limit_reached') {
                setLimitPlan(data.plan || 'free');
                setShowLimitDialog(true);
                setIsStarting(false);
                return;
            }

            if (!data.success) {
                setError(data.message || 'Failed to start session');
                return;
            }

            setSession(data.session);
            setCurrentDrill(data.drill);
            setCurrentCard(data.card);
            setProgress(data.progress);

            // Check for primary insight
            if (data.primary_insight) {
                setPendingInsight(data.primary_insight);
            }
        } catch (err) {
            setError('Failed to connect. Please try again.');
            console.error('Start session error:', err);
        } finally {
            setIsStarting(false);
        }
    }, [mode.slug]);

    // Start or resume session on mount
    const startSession = useCallback(async () => {
        setIsStarting(true);
        setError(null);

        // First check if we have required context
        const hasContext = await checkRequiredContext();
        if (!hasContext) {
            setIsStarting(false);
            return;
        }

        await startActualSession();
    }, [checkRequiredContext, startActualSession]);

    // Handle context form submission
    const handleContextSubmit = async (data: Record<string, unknown>) => {
        console.log('[Profile] Submitting data:', data);

        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        console.log('[Profile] CSRF token present:', !!csrfToken);

        if (!csrfToken) {
            throw new Error('Session expired. Please refresh the page.');
        }

        let response: Response;
        try {
            response = await fetch('/api/training/v2/update-profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(data),
            });
            console.log('[Profile] Response status:', response.status);
        } catch (fetchError) {
            console.error('[Profile] Fetch failed:', fetchError);
            throw new Error('Network error. Please check your connection.');
        }

        if (!response.ok) {
            const text = await response.text();
            console.error('[Profile] Error response:', response.status, text);
            if (response.status === 419) {
                throw new Error('Session expired. Please refresh the page.');
            }
            throw new Error(`Server error (${response.status}). Please try again.`);
        }

        const result = await response.json();
        console.log('[Profile] Result:', result);

        if (!result.success) {
            throw new Error(result.error || 'Failed to update profile');
        }

        // Close modal and start session
        setShowContextModal(false);
        setMissingFields([]);
        setIsStarting(true);
        await startActualSession();
    };

    // Handle context modal close (go back to practice modes)
    const handleContextModalClose = () => {
        setShowContextModal(false);
        router.visit('/practice-modes');
    };

    useEffect(() => {
        startSession();
    }, [startSession]);

    // Submit response to current drill
    const submitResponse = async (response: string) => {
        if (!session || isLoading) return;

        setIsLoading(true);
        setError(null);

        console.log('[Training] Submitting response:', response);
        console.log('[Training] Current scenario card:', currentCard);

        try {
            const res = await fetch(`/api/training/v2/respond/${session.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ response }),
            });

            const data = await res.json();
            console.log('[Training] Submit response result:', data);
            if (data.card) {
                console.log('[Training] Feedback card content:', data.card.content);
                console.log('[Training] Feedback card feedback:', data.card.feedback);
                console.log('[Training] Dimension scores:', data.card.dimension_scores);
            }

            // Handle limit reached
            if (data.error === 'limit_reached') {
                setLimitPlan(data.plan || 'free');
                setShowLimitDialog(true);
                return;
            }

            if (!data.success) {
                setError(data.message || 'Failed to submit response');
                return;
            }

            setSession(data.session);
            setCurrentCard(data.card);
        } catch (err) {
            setError('Failed to submit response. Please try again.');
            console.error('Submit response error:', err);
        } finally {
            setIsLoading(false);
        }
    };

    // Continue to next drill
    const continueToNext = async () => {
        if (!session || isLoading) return;

        setIsLoading(true);
        setError(null);

        try {
            const res = await fetch(`/api/training/v2/continue/${session.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                },
            });

            const data = await res.json();

            // Handle limit reached
            if (data.error === 'limit_reached') {
                setLimitPlan(data.plan || 'free');
                setShowLimitDialog(true);
                return;
            }

            if (!data.success) {
                setError(data.message || 'Failed to continue');
                return;
            }

            if (data.complete && data.stats) {
                setSessionStats(data.stats);
                setShowCompletionDialog(true);
            } else {
                setSession(data.session);
                setCurrentDrill(data.drill!);
                setCurrentCard(data.card!);
                setProgress(data.progress!);

                // Check for primary insight
                if (data.primary_insight) {
                    setPendingInsight(data.primary_insight);
                }
            }
        } catch (err) {
            setError('Failed to continue. Please try again.');
            console.error('Continue error:', err);
        } finally {
            setIsLoading(false);
        }
    };

    // Handle proceeding from the insight card
    const handleInsightProceed = () => {
        setPendingInsight(null);
    };

    // Handle "Run it back" - start a new session
    const handleRunItBack = async () => {
        setShowCompletionDialog(false);
        setSession(null);
        setCurrentCard(null);
        setCurrentDrill(null);
        setProgress(null);
        setSessionStats(null);
        setPendingInsight(null);
        await startSession();
    };

    // Handle "I'm out" - go back to dashboard
    const handleImOut = () => {
        router.visit('/practice-modes');
    };

    // Handle closing the limit dialog
    const handleLimitDialogClose = () => {
        setShowLimitDialog(false);
        router.visit('/practice-modes');
    };

    // Handle retry on error
    const handleRetry = () => {
        setError(null);
        if (!session) {
            startSession();
        } else if (currentCard?.type === 'scenario') {
            // Retry starting (scenario generation)
            startSession();
        } else if (currentCard?.type === 'feedback') {
            // Retry continue
            continueToNext();
        }
    };

    // Render current card
    const renderCard = () => {
        // Show insight card first if there's a pending insight
        if (pendingInsight) {
            return (
                <PreDrillInsightCard
                    insight={pendingInsight}
                    onProceed={handleInsightProceed}
                />
            );
        }

        if (!currentCard || !currentDrill) return null;

        if (currentCard.type === 'scenario') {
            return (
                <DrillScenarioCardComponent
                    card={currentCard}
                    timerSeconds={currentDrill.timer_seconds}
                    inputType={currentDrill.input_type}
                    onSubmit={submitResponse}
                    isLoading={isLoading}
                />
            );
        }

        if (currentCard.type === 'feedback') {
            return (
                <FeedbackCard
                    card={currentCard}
                    onContinue={continueToNext}
                    isLoading={isLoading}
                />
            );
        }

        return null;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Training - ${mode.name}`} />

            <div className="flex flex-col h-full">
                {/* Header with progress */}
                {session && !isStarting && (
                    <div className="border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 px-4 py-3">
                        <div className="max-w-4xl mx-auto">
                            <div className="flex items-center justify-between gap-4">
                                <div className="flex items-center gap-4">
                                    <h1 className="font-semibold text-neutral-900 dark:text-neutral-100">
                                        {mode.name}
                                    </h1>
                                    {progress && (
                                        <div className="flex items-center gap-3">
                                            <span className="text-sm text-neutral-500 dark:text-neutral-400">
                                                Drill {progress.current} of {progress.total}
                                            </span>
                                            <Progress
                                                value={(progress.current / progress.total) * 100}
                                                className="w-24 h-2"
                                            />
                                        </div>
                                    )}
                                </div>
                                {currentDrill && (
                                    <span className="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                                        {currentDrill.name}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
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
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={handleRetry}
                                                className="mt-2"
                                            >
                                                Try Again
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Starting state */}
                        {isStarting && <LoadingCardSkeleton />}

                        {/* Current card */}
                        {!isStarting && !error && renderCard()}
                    </div>
                </div>
            </div>

            {/* Completion dialog */}
            {sessionStats && (
                <SessionCompleteDialog
                    isOpen={showCompletionDialog}
                    stats={sessionStats}
                    onRunItBack={handleRunItBack}
                    onImOut={handleImOut}
                />
            )}

            {/* Limit reached dialog */}
            <LimitReachedDialog
                isOpen={showLimitDialog}
                plan={limitPlan}
                onClose={handleLimitDialogClose}
            />

            {/* Required context modal */}
            <RequiredContextModal
                isOpen={showContextModal}
                onClose={handleContextModalClose}
                onSubmit={handleContextSubmit}
                fields={missingFields}
                modeName={mode.name}
            />
        </AppLayout>
    );
}
