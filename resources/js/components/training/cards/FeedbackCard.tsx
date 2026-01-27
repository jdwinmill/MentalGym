import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type DrillFeedbackCard } from '@/types/training';
import { ChevronRight } from 'lucide-react';
import ReactMarkdown from 'react-markdown';

interface FeedbackCardProps {
    card: DrillFeedbackCard;
    onContinue: () => void;
    isLoading?: boolean;
}

function getScoreBadgeVariant(score: number): 'default' | 'secondary' | 'destructive' {
    if (score >= 8) return 'default';
    if (score >= 6) return 'secondary';
    return 'destructive';
}

function getScoreColor(score: number): string {
    if (score >= 8) return 'text-green-600 dark:text-green-400';
    if (score >= 6) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
}

export function FeedbackCard({ card, onContinue, isLoading }: FeedbackCardProps) {
    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <CardTitle className="text-lg">Feedback</CardTitle>
                    <Badge variant={getScoreBadgeVariant(card.score)}>
                        Score: <span className={`ml-1 font-bold ${getScoreColor(card.score)}`}>{card.score}</span>
                    </Badge>
                </div>
            </CardHeader>

            <CardContent>
                <div className="prose prose-neutral dark:prose-invert max-w-none">
                    <ReactMarkdown>
                        {card.content}
                    </ReactMarkdown>
                </div>
            </CardContent>

            <CardFooter className="justify-end">
                <Button onClick={onContinue} disabled={isLoading}>
                    {isLoading ? 'Loading...' : 'Continue'}
                    <ChevronRight className="ml-2 h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
