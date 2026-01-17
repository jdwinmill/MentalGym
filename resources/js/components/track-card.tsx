import { useState } from 'react';
import { ChevronDown, ChevronUp, Clock, Calendar, Repeat, Play, Pause, Loader2, Lock } from 'lucide-react';
import { Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { type TrackWithDetails } from '@/types';
import { cn } from '@/lib/utils';

interface TrackCardProps {
    track: TrackWithDetails;
}

export function TrackCard({ track }: TrackCardProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [showActivateDialog, setShowActivateDialog] = useState(false);
    const [showPauseDialog, setShowPauseDialog] = useState(false);

    const activateForm = useForm({});
    const pauseForm = useForm({});

    const handleActivate = () => {
        activateForm.post(`/tracks/${track.id}/activate`, {
            preserveScroll: true,
            onSuccess: () => setShowActivateDialog(false),
        });
    };

    const handlePause = () => {
        pauseForm.post(`/tracks/${track.id}/pause`, {
            preserveScroll: true,
            onSuccess: () => setShowPauseDialog(false),
        });
    };

    return (
        <>
            <Collapsible open={isOpen} onOpenChange={setIsOpen}>
                <Card
                    className={cn(
                        'transition-all duration-200 hover:shadow-md',
                        track.is_activated && 'ring-2 ring-green-500 dark:ring-green-400 bg-green-50/50 dark:bg-green-950/20'
                    )}
                >
                    <CollapsibleTrigger asChild>
                        <button
                            type="button"
                            className="w-full cursor-pointer text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-neutral-400 rounded-xl"
                        >
                            <CardHeader className="pb-2">
                                <div className="flex items-start justify-between">
                                    <div className="flex items-center gap-2">
                                        <CardTitle className="text-xl font-bold">
                                            {track.name}
                                        </CardTitle>
                                        {track.is_activated && (
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Active
                                            </span>
                                        )}
                                        {track.is_enrolled && !track.is_activated && (
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Paused
                                            </span>
                                        )}
                                    </div>
                                    <div className="ml-2 flex-shrink-0 p-1">
                                        {isOpen ? (
                                            <ChevronUp className="h-5 w-5 text-neutral-500" />
                                        ) : (
                                            <ChevronDown className="h-5 w-5 text-neutral-500" />
                                        )}
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="pt-0">
                                {track.pitch && (
                                    <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                        {track.pitch}
                                    </p>
                                )}
                                <div className="mt-3 flex flex-wrap gap-4 text-xs text-neutral-500 dark:text-neutral-500">
                                    <span className="inline-flex items-center gap-1">
                                        <Calendar className="h-3.5 w-3.5" />
                                        {track.duration_weeks} weeks
                                    </span>
                                    <span className="inline-flex items-center gap-1">
                                        <Repeat className="h-3.5 w-3.5" />
                                        {track.sessions_per_week} sessions/week
                                    </span>
                                    <span className="inline-flex items-center gap-1">
                                        <Clock className="h-3.5 w-3.5" />
                                        {track.session_duration_minutes} min each
                                    </span>
                                </div>
                            </CardContent>
                        </button>
                    </CollapsibleTrigger>

                    <CollapsibleContent>
                        <div className="border-t border-neutral-200 dark:border-neutral-700" />
                        <CardContent className="pt-4">
                            {/* Activation Button Section */}
                            <div className="mb-6 flex items-center gap-3">
                                {track.is_activated ? (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setShowPauseDialog(true);
                                        }}
                                    >
                                        <Pause className="h-4 w-4 mr-1" />
                                        Pause Track
                                    </Button>
                                ) : (
                                    <Button
                                        variant={track.can_activate ? 'default' : 'outline'}
                                        size="sm"
                                        disabled={!track.can_activate}
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setShowActivateDialog(true);
                                        }}
                                    >
                                        <Play className="h-4 w-4 mr-1" />
                                        {track.is_enrolled ? 'Resume Track' : 'Start Track'}
                                    </Button>
                                )}
                                {!track.can_activate && track.activation_blocked_reason && (
                                    <span className="text-xs text-amber-600 dark:text-amber-400">
                                        {track.activation_blocked_reason}
                                    </span>
                                )}
                            </div>

                            <div className="space-y-6">
                                {track.skill_levels.map((level) => (
                                    <div key={level.id}>
                                        <h4 className="font-semibold text-neutral-900 dark:text-neutral-100">
                                            Level {level.level_number}: {level.name}
                                        </h4>
                                        {level.description && (
                                            <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                {level.description}
                                            </p>
                                        )}
                                        <ul className="mt-3 space-y-1.5">
                                            {level.lessons.map((lesson) => (
                                                <li key={lesson.id}>
                                                    {lesson.is_locked ? (
                                                        <div
                                                            className="flex items-center gap-2 text-sm rounded px-2 py-1 -mx-2 text-neutral-400 dark:text-neutral-600 cursor-not-allowed"
                                                            title="Upgrade your plan to unlock"
                                                        >
                                                            <span className="flex-shrink-0 w-5">
                                                                {lesson.lesson_number}.
                                                            </span>
                                                            <span>{lesson.title}</span>
                                                            <Lock className="ml-auto h-3.5 w-3.5" />
                                                        </div>
                                                    ) : (
                                                        <Link
                                                            href={`/lessons/${lesson.id}`}
                                                            className={cn(
                                                                'flex items-center gap-2 text-sm rounded px-2 py-1 -mx-2 transition-colors',
                                                                lesson.is_completed
                                                                    ? 'text-neutral-400 dark:text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800'
                                                                    : 'text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-blue-600 dark:hover:text-blue-400'
                                                            )}
                                                        >
                                                            <span className="flex-shrink-0 w-5 text-neutral-400 dark:text-neutral-500">
                                                                {lesson.lesson_number}.
                                                            </span>
                                                            <span>{lesson.title}</span>
                                                            {lesson.is_completed && (
                                                                <span className="ml-auto text-xs text-green-600 dark:text-green-500">
                                                                    ✓
                                                                </span>
                                                            )}
                                                        </Link>
                                                    )}
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </CollapsibleContent>
                </Card>
            </Collapsible>

            {/* Activate Confirmation Dialog */}
            <Dialog open={showActivateDialog} onOpenChange={setShowActivateDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {track.is_enrolled ? 'Resume' : 'Start'} {track.name}?
                        </DialogTitle>
                        <DialogDescription>
                            {track.is_enrolled
                                ? `You'll resume your progress in ${track.name}. This will become your active track.`
                                : `You're about to start ${track.name}. This ${track.duration_weeks}-week program includes ${track.sessions_per_week} sessions per week.`}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setShowActivateDialog(false)}
                            disabled={activateForm.processing}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={handleActivate}
                            disabled={activateForm.processing}
                        >
                            {activateForm.processing && (
                                <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                            )}
                            {track.is_enrolled ? 'Resume Track' : 'Start Track'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Pause Confirmation Dialog */}
            <Dialog open={showPauseDialog} onOpenChange={setShowPauseDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Pause {track.name}?</DialogTitle>
                        <DialogDescription>
                            Your progress will be saved. You can resume this track anytime.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setShowPauseDialog(false)}
                            disabled={pauseForm.processing}
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="secondary"
                            onClick={handlePause}
                            disabled={pauseForm.processing}
                        >
                            {pauseForm.processing && (
                                <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                            )}
                            Pause Track
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
