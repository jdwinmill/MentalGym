import { Card, CardContent } from '@/components/ui/card';
import { Loader2 } from 'lucide-react';

export function LoadingCard() {
    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardContent className="pt-6">
                <div className="flex flex-col items-center justify-center py-8 space-y-4">
                    <Loader2 className="h-8 w-8 animate-spin text-neutral-400" />
                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                        Thinking...
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}

export function LoadingCardSkeleton() {
    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardContent className="pt-6 space-y-4">
                <div className="space-y-2">
                    <div className="h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-3/4" />
                    <div className="h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-full" />
                    <div className="h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-5/6" />
                </div>
                <div className="pt-4">
                    <div className="h-10 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse w-32 ml-auto" />
                </div>
            </CardContent>
        </Card>
    );
}
