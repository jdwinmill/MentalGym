import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type Track } from '@/types';
import { Head, Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardProps {
    tracks: Track[];
}

export default function Dashboard({ tracks }: DashboardProps) {
    const activeTracks = tracks.filter((track) => track.active);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {activeTracks.length > 0 ? (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {activeTracks.map((track) => (
                            <Link
                                key={track.id}
                                href={`/tracks/${track.id}`}
                                className="block rounded-xl bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-md dark:bg-neutral-800 dark:hover:bg-neutral-700"
                            >
                                <h3 className="font-bold text-neutral-900 dark:text-neutral-100">
                                    {track.title}
                                </h3>
                                <p className="mt-2 line-clamp-2 text-sm text-neutral-600 dark:text-neutral-400">
                                    {track.description}
                                </p>
                            </Link>
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
