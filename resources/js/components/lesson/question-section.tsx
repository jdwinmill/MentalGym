import { useState, useRef } from 'react';
import { Check, X } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type LessonQuestionWithOptions, type AnswerFeedback } from '@/types';
import { cn } from '@/lib/utils';

interface AnswerResponse {
    answer: {
        id: number;
        is_correct: boolean;
    };
    is_correct: boolean;
    feedback: AnswerFeedback | null;
    correct_option_id: number;
}

interface QuestionSectionProps {
    questions: LessonQuestionWithOptions[];
    onSubmitAnswer: (questionId: number, answerOptionId: number, timeToAnswer?: number) => Promise<AnswerResponse | null>;
    onComplete: () => void;
}

export function QuestionSection({ questions, onSubmitAnswer, onComplete }: QuestionSectionProps) {
    const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
    const [selectedAnswerId, setSelectedAnswerId] = useState<number | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showFeedback, setShowFeedback] = useState(false);
    const [lastResponse, setLastResponse] = useState<AnswerResponse | null>(null);
    const questionStartTime = useRef<number>(Date.now());

    const currentQuestion = questions[currentQuestionIndex];
    const isLastQuestion = currentQuestionIndex === questions.length - 1;

    // Convert sort_order to letter (1=A, 2=B, etc.)
    const getOptionLetter = (sortOrder: number) => String.fromCharCode(64 + sortOrder);

    const handleSelectAnswer = (optionId: number) => {
        if (showFeedback) return; // Can't change after submitting
        setSelectedAnswerId(optionId);
    };

    const handleSubmit = async () => {
        if (!selectedAnswerId || isSubmitting) return;

        setIsSubmitting(true);
        const timeToAnswer = Math.floor((Date.now() - questionStartTime.current) / 1000);

        const response = await onSubmitAnswer(currentQuestion.id, selectedAnswerId, timeToAnswer);

        if (response) {
            setLastResponse(response);
            setShowFeedback(true);
        }
        setIsSubmitting(false);
    };

    const handleNextQuestion = () => {
        if (isLastQuestion) {
            onComplete();
        } else {
            setCurrentQuestionIndex((prev) => prev + 1);
            setSelectedAnswerId(null);
            setShowFeedback(false);
            setLastResponse(null);
            questionStartTime.current = Date.now();
        }
    };

    if (!currentQuestion) {
        return null;
    }

    const sortedOptions = [...currentQuestion.answer_options].sort((a, b) => a.sort_order - b.sort_order);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-2xl">Questions</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    Question {currentQuestionIndex + 1} of {questions.length}
                </p>

                <h3 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                    {currentQuestion.question_text}
                </h3>

                <div className="space-y-3">
                    {sortedOptions.map((option) => {
                        const isSelected = selectedAnswerId === option.id;
                        const isCorrect = option.is_correct;
                        const isCorrectAnswer = lastResponse?.correct_option_id === option.id;

                        return (
                            <label
                                key={option.id}
                                className={cn(
                                    'block p-4 border-2 rounded-lg transition-colors',
                                    showFeedback
                                        ? isCorrectAnswer
                                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-400'
                                            : isSelected && !isCorrect
                                                ? 'border-red-500 bg-red-50 dark:bg-red-900/20 dark:border-red-400'
                                                : 'border-neutral-200 dark:border-neutral-700 opacity-50'
                                        : isSelected
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-400 cursor-pointer'
                                            : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer'
                                )}
                            >
                                <div className="flex items-start gap-3">
                                    <input
                                        type="radio"
                                        name={`question_${currentQuestion.id}`}
                                        value={option.id}
                                        checked={isSelected}
                                        onChange={() => handleSelectAnswer(option.id)}
                                        disabled={showFeedback}
                                        className="mt-1"
                                    />
                                    <div className="flex-1">
                                        <span className="text-neutral-700 dark:text-neutral-300">
                                            <span className="font-medium">{getOptionLetter(option.sort_order)}.</span>{' '}
                                            {option.option_text}
                                        </span>

                                        {/* Show feedback indicator */}
                                        {showFeedback && isCorrectAnswer && (
                                            <span className="ml-2 inline-flex items-center text-green-600 dark:text-green-400">
                                                <Check className="h-4 w-4" />
                                            </span>
                                        )}
                                        {showFeedback && isSelected && !isCorrect && (
                                            <span className="ml-2 inline-flex items-center text-red-600 dark:text-red-400">
                                                <X className="h-4 w-4" />
                                            </span>
                                        )}
                                    </div>
                                </div>

                                {/* Show feedback text for selected wrong answer */}
                                {showFeedback && isSelected && lastResponse?.feedback && (
                                    <div className={cn(
                                        'mt-3 pt-3 border-t text-sm',
                                        isCorrect
                                            ? 'border-green-200 text-green-700 dark:border-green-700 dark:text-green-300'
                                            : 'border-red-200 text-red-700 dark:border-red-700 dark:text-red-300'
                                    )}>
                                        {lastResponse.feedback.feedback_text}
                                    </div>
                                )}
                            </label>
                        );
                    })}
                </div>

                {/* Buttons */}
                {!showFeedback ? (
                    <Button
                        onClick={handleSubmit}
                        size="lg"
                        disabled={!selectedAnswerId || isSubmitting}
                    >
                        {isSubmitting ? 'Submitting...' : 'Submit Answer'}
                    </Button>
                ) : (
                    <Button
                        onClick={handleNextQuestion}
                        size="lg"
                        className={isLastQuestion ? 'bg-green-600 hover:bg-green-700' : ''}
                    >
                        {isLastQuestion ? 'See Results' : 'Next Question'}
                    </Button>
                )}
            </CardContent>
        </Card>
    );
}
