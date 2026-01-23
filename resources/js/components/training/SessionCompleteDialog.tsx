import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { type SessionStats } from '@/types/training';
import { Trophy, RotateCcw, LogOut } from 'lucide-react';

interface SessionCompleteDialogProps {
    isOpen: boolean;
    stats: SessionStats;
    onRunItBack: () => void;
    onImOut: () => void;
}

export function SessionCompleteDialog({
    isOpen,
    stats,
    onRunItBack,
    onImOut,
}: SessionCompleteDialogProps) {
    const minutes = Math.round(stats.total_duration_seconds / 60);
    const avgScore = stats.scores.length > 0
        ? Math.round(stats.scores.reduce((acc, s) => acc + s.score, 0) / stats.scores.length)
        : 0;

    return (
        <Dialog open={isOpen}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader className="text-center">
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <Trophy className="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <DialogTitle className="text-xl">Nice work. You're done.</DialogTitle>
                </DialogHeader>

                <div className="space-y-4 py-4">
                    <div className="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p className="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">
                                {stats.drills_completed}
                            </p>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Drills
                            </p>
                        </div>
                        <div>
                            <p className="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">
                                {minutes}
                            </p>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Minutes
                            </p>
                        </div>
                        <div>
                            <p className="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">
                                {avgScore}
                            </p>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Avg Score
                            </p>
                        </div>
                    </div>

                    {stats.scores.length > 0 && (
                        <div className="border-t border-neutral-200 dark:border-neutral-800 pt-4">
                            <p className="text-sm font-medium text-neutral-600 dark:text-neutral-400 mb-2">
                                Drill Scores
                            </p>
                            <div className="space-y-2">
                                {stats.scores.map((score, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between text-sm"
                                    >
                                        <span className="text-neutral-700 dark:text-neutral-300">
                                            {score.drill_name}
                                        </span>
                                        <span
                                            className={`font-medium ${
                                                score.score >= 80
                                                    ? 'text-green-600 dark:text-green-400'
                                                    : score.score >= 60
                                                      ? 'text-amber-600 dark:text-amber-400'
                                                      : 'text-red-600 dark:text-red-400'
                                            }`}
                                        >
                                            {score.score}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                <div className="flex gap-3 pt-2">
                    <Button
                        variant="outline"
                        className="flex-1"
                        onClick={onRunItBack}
                    >
                        <RotateCcw className="mr-2 h-4 w-4" />
                        Run it back
                    </Button>
                    <Button className="flex-1" onClick={onImOut}>
                        <LogOut className="mr-2 h-4 w-4" />
                        I'm out
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
