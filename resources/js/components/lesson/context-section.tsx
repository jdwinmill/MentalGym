import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type LessonContentBlock } from '@/types';

interface ContextSectionProps {
    block: LessonContentBlock | undefined;
    onContinue: () => void;
}

export function ContextSection({ block, onContinue }: ContextSectionProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-2xl">Context</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                <div className="prose prose-neutral dark:prose-invert max-w-none">
                    {block?.content?.text ? (
                        <p className="text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">
                            {block.content.text}
                        </p>
                    ) : (
                        <p className="text-neutral-500 dark:text-neutral-400 italic">
                            No context provided for this lesson.
                        </p>
                    )}
                </div>

                <Button onClick={onContinue} size="lg">
                    Continue to Audio
                </Button>
            </CardContent>
        </Card>
    );
}
