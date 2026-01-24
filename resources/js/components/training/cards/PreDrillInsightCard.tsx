import { useState } from 'react';
import { Lightbulb, ChevronDown, ChevronUp, ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type PrimaryInsight } from '@/types/training';

interface Props {
    insight: PrimaryInsight;
    onProceed: () => void;
}

export function PreDrillInsightCard({ insight, onProceed }: Props) {
    const [isExpanded, setIsExpanded] = useState(false);

    return (
        <div className="w-full max-w-2xl mx-auto">
            <div className="rounded-xl border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 dark:border-blue-800 dark:from-blue-950/30 dark:to-indigo-950/30 overflow-hidden">
                {/* Header */}
                <div className="px-6 pt-6 pb-4">
                    <div className="flex items-start gap-4">
                        <div className="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                            <Lightbulb className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <Badge variant="secondary" className="mb-2 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                {insight.principle.name}
                            </Badge>
                            <h3 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                                {insight.name}
                            </h3>
                        </div>
                    </div>
                </div>

                {/* Summary */}
                <div className="px-6 pb-4">
                    <p className="text-neutral-700 dark:text-neutral-300 leading-relaxed">
                        {insight.summary}
                    </p>
                </div>

                {/* Expandable Content */}
                {insight.content && (
                    <div className="px-6">
                        <button
                            onClick={() => setIsExpanded(!isExpanded)}
                            className="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors"
                        >
                            {isExpanded ? (
                                <>
                                    <ChevronUp className="w-4 h-4" />
                                    Hide details
                                </>
                            ) : (
                                <>
                                    <ChevronDown className="w-4 h-4" />
                                    Read more
                                </>
                            )}
                        </button>

                        {isExpanded && (
                            <div className="mt-4 pt-4 border-t border-blue-200 dark:border-blue-800">
                                <div className="prose prose-neutral dark:prose-invert prose-sm max-w-none">
                                    {insight.content.split('\n\n').map((paragraph, index) => (
                                        <p key={index} className="text-neutral-600 dark:text-neutral-400 leading-relaxed mb-3 last:mb-0">
                                            {paragraph}
                                        </p>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Action */}
                <div className="px-6 py-5 mt-4 bg-white/50 dark:bg-neutral-900/30 border-t border-blue-100 dark:border-blue-900">
                    <Button
                        onClick={onProceed}
                        className="w-full bg-blue-600 hover:bg-blue-700 text-white"
                        size="lg"
                    >
                        Got it, let's practice
                        <ArrowRight className="w-4 h-4 ml-2" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
