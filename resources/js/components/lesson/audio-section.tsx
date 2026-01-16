import { useState, useRef, useEffect } from 'react';
import { Play, Pause } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type LessonContentBlock } from '@/types';

interface AudioSectionProps {
    block: LessonContentBlock | undefined;
    onContinue: () => void;
    onInteraction?: (contentBlockId: number, interactionType: string, interactionData: Record<string, unknown>) => void;
}

export function AudioSection({ block, onContinue, onInteraction }: AudioSectionProps) {
    const audioRef = useRef<HTMLAudioElement>(null);
    const [isPlaying, setIsPlaying] = useState(false);
    const [progress, setProgress] = useState(0);
    const [currentTime, setCurrentTime] = useState(0);
    const [duration, setDuration] = useState(0);
    const [replayCount, setReplayCount] = useState(0);
    const [hasListened, setHasListened] = useState(false);

    const title = block?.content?.title as string | undefined;
    const context = block?.content?.context as string | undefined;
    const audioUrl = block?.content?.url as string | undefined;
    const durationSeconds = (block?.content?.duration_seconds as number) ?? 0;
    const maxReplays = (block?.content?.max_replays as number) ?? 3;

    // If no audio URL, allow continuing immediately
    useEffect(() => {
        if (!audioUrl) {
            setHasListened(true);
        }
    }, [audioUrl]);

    const formatTime = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    const togglePlay = () => {
        if (!audioRef.current) return;

        if (replayCount >= maxReplays && !isPlaying) {
            return;
        }

        if (isPlaying) {
            audioRef.current.pause();
        } else {
            audioRef.current.play();
        }
        setIsPlaying(!isPlaying);
    };

    const handleTimeUpdate = () => {
        if (!audioRef.current) return;
        setCurrentTime(audioRef.current.currentTime);
        setProgress((audioRef.current.currentTime / audioRef.current.duration) * 100);
    };

    const handleLoadedMetadata = () => {
        if (!audioRef.current) return;
        setDuration(audioRef.current.duration);
    };

    const handleEnded = () => {
        setIsPlaying(false);
        setReplayCount((prev) => prev + 1);
        setHasListened(true);

        // Record the interaction
        if (block && onInteraction) {
            onInteraction(block.id, 'audio_completed', {
                replay_number: replayCount + 1,
                duration_listened: currentTime,
            });
        }
    };

    const handleProgressClick = (e: React.MouseEvent<HTMLDivElement>) => {
        if (!audioRef.current) return;
        const rect = e.currentTarget.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const percentage = clickX / rect.width;
        audioRef.current.currentTime = percentage * audioRef.current.duration;
    };

    const canReplay = replayCount < maxReplays;

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-2xl">Listen Carefully</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                {title && (
                    <h3 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                        {title}
                    </h3>
                )}

                {context && (
                    <p className="text-neutral-600 dark:text-neutral-400">
                        {context}
                    </p>
                )}

                {/* Audio Player */}
                <div className="bg-neutral-100 dark:bg-neutral-800 rounded-lg p-6">
                    {audioUrl ? (
                        <>
                            <audio
                                ref={audioRef}
                                src={audioUrl}
                                onTimeUpdate={handleTimeUpdate}
                                onLoadedMetadata={handleLoadedMetadata}
                                onEnded={handleEnded}
                                className="hidden"
                            />

                            {/* Custom Controls */}
                            <div className="flex items-center gap-4 mb-4">
                                <Button
                                    onClick={togglePlay}
                                    disabled={!canReplay && !isPlaying}
                                    variant="default"
                                    size="icon"
                                    className="h-12 w-12"
                                >
                                    {isPlaying ? (
                                        <Pause className="h-6 w-6" />
                                    ) : (
                                        <Play className="h-6 w-6" />
                                    )}
                                </Button>

                                {/* Progress Bar */}
                                <div
                                    className="flex-1 h-2 bg-neutral-300 dark:bg-neutral-600 rounded-full cursor-pointer overflow-hidden"
                                    onClick={handleProgressClick}
                                >
                                    <div
                                        className="h-full bg-blue-600 transition-all"
                                        style={{ width: `${progress}%` }}
                                    />
                                </div>

                                <span className="text-sm text-neutral-600 dark:text-neutral-400 min-w-[80px] text-right">
                                    {formatTime(currentTime)} / {formatTime(duration || durationSeconds)}
                                </span>
                            </div>
                        </>
                    ) : (
                        <div className="text-center py-4">
                            <p className="text-neutral-500 dark:text-neutral-400">
                                Audio file not yet available
                            </p>
                        </div>
                    )}

                    <div className="flex justify-between text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                        <span>Duration: {durationSeconds}s</span>
                        <span className={replayCount >= maxReplays ? 'text-red-600 dark:text-red-400 font-semibold' : ''}>
                            Replays: {replayCount}/{maxReplays}
                        </span>
                    </div>
                </div>

                <Button
                    onClick={onContinue}
                    size="lg"
                    disabled={!hasListened}
                    className={!hasListened ? 'opacity-50 cursor-not-allowed' : ''}
                >
                    I'm Ready for Questions
                </Button>
            </CardContent>
        </Card>
    );
}
