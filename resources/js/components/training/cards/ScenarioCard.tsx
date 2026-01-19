import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type ScenarioCard as ScenarioCardType } from '@/types/training';
import { ChevronRight } from 'lucide-react';

interface ScenarioCardProps {
    card: ScenarioCardType;
    onContinue: () => void;
}

export function ScenarioCard({ card, onContinue }: ScenarioCardProps) {
    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardContent className="pt-6">
                <div className="prose prose-neutral dark:prose-invert max-w-none">
                    <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">
                        {card.content}
                    </p>
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
