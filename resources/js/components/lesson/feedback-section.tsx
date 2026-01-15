import { Link } from '@inertiajs/react';
import { CheckCircle, XCircle, AlertTriangle, RotateCcw, ArrowRight } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type LessonCompletionResult, type WeaknessPatternSummary } from '@/types';

interface FeedbackSectionProps {
    result: LessonCompletionResult | null;
    totalQuestions: number;
    onRetry: () => void;
    nextLessonId: number | null;
}

export function FeedbackSection({ result, totalQuestions, onRetry, nextLessonId }: FeedbackSectionProps) {
    const score = result?.score ?? 0;
    const total = result?.total_questions ?? totalQuestions;
    const accuracy = result?.accuracy_percentage ?? 0;
    const passed = result?.passed ?? false;
    const weaknessPatterns = result?.weakness_patterns ?? [];
    const transcript = result?.transcript;

    const formatPatternTag = (tag: string) => {
        return tag
            .split('_')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-2xl">Diagnostic Feedback</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                {/* Score Display */}
                <div
                    className={`text-center p-6 rounded-lg ${
                        passed
                            ? 'bg-green-50 dark:bg-green-900/20'
                            : 'bg-red-50 dark:bg-red-900/20'
                    }`}
                >
                    <div className="flex justify-center mb-4">
                        {passed ? (
                            <CheckCircle className="h-16 w-16 text-green-600 dark:text-green-400" />
                        ) : (
                            <XCircle className="h-16 w-16 text-red-600 dark:text-red-400" />
                        )}
                    </div>
                    <p
                        className={`text-5xl font-bold mb-2 ${
                            passed
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-red-600 dark:text-red-400'
                        }`}
                    >
                        {score}/{total}
                    </p>
                    <p
                        className={`text-xl font-semibold mb-2 ${
                            passed
                                ? 'text-green-700 dark:text-green-300'
                                : 'text-red-700 dark:text-red-300'
                        }`}
                    >
                        {accuracy}% Accuracy
                    </p>
                    <p
                        className={`text-lg ${
                            passed
                                ? 'text-green-700 dark:text-green-300'
                                : 'text-red-700 dark:text-red-300'
                        }`}
                    >
                        {passed
                            ? 'You passed! (80% required)'
                            : 'You need 80% to pass this lesson.'}
                    </p>
                </div>

                {/* Pattern Analysis */}
                {weaknessPatterns.length > 0 && (
                    <div>
                        <h3 className="text-xl font-semibold mb-4 text-neutral-900 dark:text-neutral-100">
                            Your Listening Gaps:
                        </h3>
                        <div className="space-y-4">
                            {weaknessPatterns.map((pattern: WeaknessPatternSummary) => (
                                <div
                                    key={pattern.pattern_tag}
                                    className="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4 rounded-r-lg"
                                >
                                    <div className="flex items-center gap-2 mb-2">
                                        <AlertTriangle className="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                        <p className="font-semibold text-yellow-800 dark:text-yellow-200">
                                            {formatPatternTag(pattern.pattern_tag)}
                                        </p>
                                        <span className="text-sm text-yellow-600 dark:text-yellow-400">
                                            ({pattern.occurrence_count}{' '}
                                            {pattern.occurrence_count === 1 ? 'time' : 'times'})
                                        </span>
                                    </div>
                                    <p className="text-sm text-yellow-700 dark:text-yellow-300">
                                        Severity: {pattern.severity_label}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Transcript Reveal */}
                {transcript && (
                    <div className="bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg p-6">
                        <h3 className="text-lg font-semibold mb-3 text-neutral-900 dark:text-neutral-100">
                            Audio Transcript
                        </h3>
                        <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-line">
                            {transcript}
                        </p>
                    </div>
                )}

                {/* Actions */}
                <div className="flex flex-wrap gap-4">
                    <Link href="/dashboard">
                        <Button variant="secondary" size="lg">
                            Back to Dashboard
                        </Button>
                    </Link>

                    {!passed && (
                        <Button onClick={onRetry} size="lg" variant="default">
                            <RotateCcw className="h-4 w-4 mr-2" />
                            Try Again
                        </Button>
                    )}

                    {passed && nextLessonId && (
                        <Link href={`/lessons/${nextLessonId}`}>
                            <Button size="lg" className="bg-green-600 hover:bg-green-700">
                                Next Lesson
                                <ArrowRight className="h-4 w-4 ml-2" />
                            </Button>
                        </Link>
                    )}

                    {passed && !nextLessonId && (
                        <Button size="lg" disabled className="bg-green-600">
                            Next Lesson
                            <ArrowRight className="h-4 w-4 ml-2" />
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
