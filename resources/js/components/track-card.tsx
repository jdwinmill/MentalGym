import { useState } from 'react';
import { ChevronDown, ChevronUp, Clock, Calendar, Repeat } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

    return (
        <Collapsible open={isOpen} onOpenChange={setIsOpen}>
            <Card className="transition-shadow duration-200 hover:shadow-md">
                <CollapsibleTrigger asChild>
                    <button
                        type="button"
                        className="w-full cursor-pointer text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-neutral-400 rounded-xl"
                    >
                        <CardHeader className="pb-2">
                            <div className="flex items-start justify-between">
                                <CardTitle className="text-xl font-bold">
                                    {track.name}
                                </CardTitle>
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
    );
}
