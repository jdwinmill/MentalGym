import { useState, useCallback } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { ContextSection } from '@/components/lesson/context-section';
import { AudioSection } from '@/components/lesson/audio-section';
import { QuestionSection } from '@/components/lesson/question-section';
import { FeedbackSection } from '@/components/lesson/feedback-section';
import {
    type BreadcrumbItem,
    type LessonWithContent,
    type LessonCompletionResult,
    type AnswerFeedback,
} from '@/types';

type Section = 'context' | 'audio' | 'questions' | 'feedback';

interface LessonShowProps {
    lesson: LessonWithContent;
}

interface AnswerResponse {
    answer: {
        id: number;
        is_correct: boolean;
    };
    is_correct: boolean;
    feedback: AnswerFeedback | null;
    correct_option_id: number;
}

export default function LessonShow({ lesson }: LessonShowProps) {
    const [currentSection, setCurrentSection] = useState<Section>('context');
    const [attemptId, setAttemptId] = useState<number | null>(null);
    const [isStarting, setIsStarting] = useState(false);
    const [completionResult, setCompletionResult] = useState<LessonCompletionResult | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: lesson.track.name, href: '/dashboard' },
        { title: lesson.title, href: `/lessons/${lesson.id}` },
    ];

    // Find content blocks by type
    const contextBlock = lesson.content_blocks.find(
        (block) => block.block_type === 'instruction_text'
    );
    const audioBlock = lesson.content_blocks.find(
        (block) => block.block_type === 'audio'
    );

    // Start a lesson attempt when moving to audio section
    const startAttempt = useCallback(async () => {
        if (attemptId || isStarting) return;

        setIsStarting(true);
        try {
            const response = await fetch(`/lessons/${lesson.id}/attempts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setAttemptId(data.attempt.id);
            }
        } catch (error) {
            console.error('Failed to start attempt:', error);
        } finally {
            setIsStarting(false);
        }
    }, [lesson.id, attemptId, isStarting]);

    // Record audio interaction
    const recordAudioInteraction = useCallback(async (contentBlockId: number, interactionType: string, interactionData: Record<string, unknown>) => {
        if (!attemptId) return;

        try {
            await fetch(`/attempts/${attemptId}/interactions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    content_block_id: contentBlockId,
                    interaction_type: interactionType,
                    interaction_data: interactionData,
                }),
            });
        } catch (error) {
            console.error('Failed to record interaction:', error);
        }
    }, [attemptId]);

    // Submit answer and get feedback
    const submitAnswer = useCallback(async (questionId: number, answerOptionId: number, timeToAnswer?: number): Promise<AnswerResponse | null> => {
        if (!attemptId) return null;

        try {
            const response = await fetch(`/attempts/${attemptId}/answers`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer_option_id: answerOptionId,
                    time_to_answer_seconds: timeToAnswer,
                }),
            });

            if (response.ok) {
                return await response.json();
            }
        } catch (error) {
            console.error('Failed to submit answer:', error);
        }
        return null;
    }, [attemptId]);

    // Complete the lesson attempt
    const completeAttempt = useCallback(async (): Promise<LessonCompletionResult | null> => {
        if (!attemptId) return null;

        try {
            const response = await fetch(`/attempts/${attemptId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            if (response.ok) {
                const result = await response.json();
                setCompletionResult(result);
                return result;
            }
        } catch (error) {
            console.error('Failed to complete attempt:', error);
        }
        return null;
    }, [attemptId]);

    const goToAudio = () => {
        startAttempt();
        setCurrentSection('audio');
    };

    const goToQuestions = () => setCurrentSection('questions');

    const goToFeedback = async () => {
        await completeAttempt();
        setCurrentSection('feedback');
    };

    const retryLesson = () => {
        router.reload();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={lesson.title} />
            <div className="flex h-full flex-1 flex-col overflow-x-auto rounded-xl p-4">
                <div className="max-w-3xl mx-auto w-full">
                    {/* Header */}
                    <div className="mb-8">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back to Dashboard
                        </Link>
                        <h1 className="text-3xl font-bold mt-4 text-neutral-900 dark:text-neutral-100">
                            {lesson.title}
                        </h1>
                        <p className="text-neutral-600 dark:text-neutral-400">
                            {lesson.skill_level.name}
                        </p>
                    </div>

                    {/* Sections */}
                    {currentSection === 'context' && (
                        <ContextSection
                            block={contextBlock}
                            onContinue={goToAudio}
                        />
                    )}

                    {currentSection === 'audio' && (
                        <AudioSection
                            block={audioBlock}
                            onContinue={goToQuestions}
                            onInteraction={recordAudioInteraction}
                        />
                    )}

                    {currentSection === 'questions' && (
                        <QuestionSection
                            questions={lesson.questions}
                            onSubmitAnswer={submitAnswer}
                            onComplete={goToFeedback}
                        />
                    )}

                    {currentSection === 'feedback' && (
                        <FeedbackSection
                            result={completionResult}
                            totalQuestions={lesson.questions.length}
                            onRetry={retryLesson}
                            nextLessonId={null}
                        />
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
