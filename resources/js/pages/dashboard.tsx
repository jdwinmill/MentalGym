import { TrackCard } from '@/components/track-card';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type TrackWithDetails } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardProps {
    tracks: TrackWithDetails[];
}

export default function Dashboard({ tracks }: DashboardProps) {
    const activeTracks = tracks.filter((track) => track.is_active);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {activeTracks.length > 0 ? (
                    <div className="space-y-4 max-w-3xl mx-auto w-full">
                        {activeTracks.map((track) => (
                            <TrackCard key={track.id} track={track} />
                        ))}
                    </div>
                ) : (
                    <div className="flex flex-1 items-center justify-center">
                        <p className="text-neutral-500 dark:text-neutral-400">
                            No tracks available
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
