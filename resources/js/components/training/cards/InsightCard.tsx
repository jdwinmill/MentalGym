import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type InsightCard as InsightCardType } from '@/types/training';
import { ChevronRight, Lightbulb } from 'lucide-react';

interface InsightCardProps {
    card: InsightCardType;
    onContinue: () => void;
}

export function InsightCard({ card, onContinue }: InsightCardProps) {
    return (
        <Card className="w-full max-w-2xl mx-auto border-blue-200 dark:border-blue-800">
            <CardContent className="pt-6">
                <div className="flex items-start gap-3">
                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30 shrink-0">
                        <Lightbulb className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div className="prose prose-neutral dark:prose-invert max-w-none">
                        <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">
                            {card.content}
                        </p>
                    </div>
                </div>
            </CardContent>
            <CardFooter className="justify-end">
                <Button onClick={onContinue}>
                    Continue
                    <ChevronRight className="ml-2 h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
