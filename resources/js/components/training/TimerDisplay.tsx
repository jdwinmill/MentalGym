import { useState, useEffect, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Play, Pause, X, Timer } from 'lucide-react';
import {
    hasSeenTimerIntro,
    markTimerIntroSeen,
    isTimerDisabled,
    setTimerDisabled,
    recordTimerPause,
    recordTimerCompletion,
} from '@/lib/timer-preferences';

type TimerState = 'idle' | 'running' | 'expired';

interface TimerDisplayProps {
    seconds: number;
    onExpire?: () => void;
}

export function TimerDisplay({ seconds, onExpire }: TimerDisplayProps) {
    const [state, setState] = useState<TimerState>('idle');
    const [remaining, setRemaining] = useState(seconds);
    const [showIntro, setShowIntro] = useState(false);
    const [showDisablePrompt, setShowDisablePrompt] = useState(false);
    const [disabled, setDisabled] = useState(false);

    // Check if timer is globally disabled on mount
    useEffect(() => {
        setDisabled(isTimerDisabled());
        if (!hasSeenTimerIntro()) {
            setShowIntro(true);
        }
    }, []);

    // Timer countdown logic
    useEffect(() => {
        if (state !== 'running') return;

        const interval = setInterval(() => {
            setRemaining((prev) => {
                if (prev <= 1) {
                    setState('expired');
                    recordTimerCompletion();
                    onExpire?.();
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);

        return () => clearInterval(interval);
    }, [state, onExpire]);

    const handleToggle = useCallback(() => {
        if (state === 'idle') {
            setState('running');
            if (showIntro) {
                markTimerIntroSeen();
                setShowIntro(false);
            }
        } else if (state === 'running') {
            setState('idle');
            const shouldPrompt = recordTimerPause();
            if (shouldPrompt) {
                setShowDisablePrompt(true);
            }
        }
    }, [state, showIntro]);

    const handleDisableTimer = useCallback(() => {
        setTimerDisabled(true);
        setDisabled(true);
        setShowDisablePrompt(false);
    }, []);

    const handleDismissPrompt = useCallback(() => {
        setShowDisablePrompt(false);
    }, []);

    const handleDismissIntro = useCallback(() => {
        markTimerIntroSeen();
        setShowIntro(false);
    }, []);

    // If globally disabled, don't render anything
    if (disabled) return null;

    const percentage = (remaining / seconds) * 100;
    const isExpired = state === 'expired';
    const isRunning = state === 'running';

    const formatTime = (secs: number): string => {
        const mins = Math.floor(secs / 60);
        const s = secs % 60;
        if (mins > 0) {
            return `${mins}:${s.toString().padStart(2, '0')}`;
        }
        return `${s}s`;
    };

    return (
        <div className="space-y-3">
            {/* First-time intro tooltip */}
            {showIntro && (
                <div className="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm">
                    <div className="flex items-start justify-between gap-3">
                        <p className="text-blue-800 dark:text-blue-200">
                            Real pressure has a clock. This simulates it. You control when it starts. The goal is practice, not perfection.
                        </p>
                        <button
                            onClick={handleDismissIntro}
                            className="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 shrink-0"
                            aria-label="Dismiss"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            )}

            {/* Disable prompt after 3 consecutive pauses */}
            {showDisablePrompt && (
                <div className="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg p-4 text-sm">
                    <p className="text-amber-800 dark:text-amber-200 mb-3">
                        You've paused the timer a few times. Would you like to disable pressure timers for all drills?
                    </p>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleDisableTimer}
                            className="text-amber-700 border-amber-300 hover:bg-amber-100 dark:text-amber-300 dark:border-amber-700 dark:hover:bg-amber-900/30"
                        >
                            Disable timers
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={handleDismissPrompt}
                            className="text-amber-600 dark:text-amber-400"
                        >
                            Keep them
                        </Button>
                    </div>
                </div>
            )}

            {/* Timer display */}
            <div className="flex items-center gap-3">
                <div className="flex items-center gap-1.5 text-neutral-500 dark:text-neutral-400">
                    <Timer className="h-4 w-4" />
                    <span className="text-xs font-medium uppercase tracking-wide">Timer</span>
                </div>

                <Button
                    variant="ghost"
                    size="sm"
                    onClick={handleToggle}
                    disabled={isExpired}
                    className="h-8 w-8 p-0"
                    aria-label={isRunning ? 'Pause timer' : 'Start timer'}
                >
                    {isRunning ? (
                        <Pause className="h-4 w-4" />
                    ) : (
                        <Play className="h-4 w-4" />
                    )}
                </Button>

                <div className="flex-1 space-y-1">
                    <Progress
                        value={percentage}
                        className={`h-2 transition-colors ${
                            isExpired
                                ? '[&>div]:bg-amber-500 dark:[&>div]:bg-amber-600'
                                : ''
                        }`}
                    />
                </div>

                <span
                    className={`text-sm font-mono min-w-[3rem] text-right ${
                        isExpired
                            ? 'text-amber-600 dark:text-amber-400'
                            : 'text-neutral-600 dark:text-neutral-400'
                    }`}
                >
                    {formatTime(remaining)}
                </span>
            </div>

            {/* Expiry message */}
            {isExpired && (
                <p className="text-sm text-neutral-500 dark:text-neutral-400 animate-pulse">
                    Time's up. Submit what you have.
                </p>
            )}
        </div>
    );
}
