import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { type TrainingMode, type SessionProgress } from '@/types/training';
import { Square, Dumbbell } from 'lucide-react';

interface TrainingHeaderProps {
    mode: TrainingMode;
    progress: SessionProgress;
    onEndSession: () => void;
    isEnding?: boolean;
}

function getLevelLabel(level: number): string {
    const labels: Record<number, string> = {
        1: 'Beginner',
        2: 'Novice',
        3: 'Intermediate',
        4: 'Advanced',
        5: 'Expert',
    };
    return labels[level] || `Level ${level}`;
}

function getLevelColor(level: number): string {
    const colors: Record<number, string> = {
        1: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        2: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        3: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        4: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        5: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
    };
    return colors[level] || 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300';
}

export function TrainingHeader({ mode, progress, onEndSession, isEnding }: TrainingHeaderProps) {
    const [showEndDialog, setShowEndDialog] = useState(false);

    // Calculate progress percentage
    const progressPercentage = progress.exchanges_to_next_level !== null
        ? Math.min(100, (progress.exchanges_at_current_level / (progress.exchanges_at_current_level + progress.exchanges_to_next_level)) * 100)
        : 100;

    return (
        <div className="border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 px-4 py-3">
            <div className="max-w-4xl mx-auto flex items-center justify-between gap-4">
                <div className="flex items-center gap-3 min-w-0">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800 shrink-0">
                        <Dumbbell className="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                    </div>
                    <div className="min-w-0">
                        <h1 className="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                            {mode.name}
                        </h1>
                        <div className="flex items-center gap-2 mt-0.5">
                            <Badge className={getLevelColor(progress.current_level)}>
                                {getLevelLabel(progress.current_level)}
                            </Badge>
                            {progress.exchanges_to_next_level !== null && (
                                <div className="flex items-center gap-2">
                                    <Progress value={progressPercentage} className="w-20 h-2" />
                                    <span className="text-xs text-neutral-500 dark:text-neutral-400">
                                        {progress.exchanges_to_next_level} to level up
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <AlertDialog open={showEndDialog} onOpenChange={setShowEndDialog}>
                    <AlertDialogTrigger asChild>
                        <Button variant="outline" size="sm" disabled={isEnding}>
                            <Square className="h-4 w-4 mr-2" />
                            End Training
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>End Training Session?</AlertDialogTitle>
                            <AlertDialogDescription>
                                Your progress has been saved. You can return to continue training at any time.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Continue Training</AlertDialogCancel>
                            <AlertDialogAction onClick={onEndSession}>
                                End Session
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </div>
    );
}
