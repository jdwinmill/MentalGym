import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import { Search, Clock, ArrowRight, BookOpen } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface Drill {
    id: number;
    name: string;
    practice_mode_slug: string;
}

interface Principle {
    id: number;
    name: string;
    slug: string;
}

interface Insight {
    id: number;
    name: string;
    slug: string;
    summary: string;
    content: string;
    read_time: number;
    principle: Principle;
    drill: Drill | null;
    created_at: string;
}

interface Props {
    principles: Principle[];
    insights: Insight[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Playbook', href: '/playbook' },
];

export default function PlaybookIndex({ principles, insights }: Props) {
    const { auth } = usePage<SharedData>().props;
    const isLoggedIn = !!auth.user;
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedPrinciple, setSelectedPrinciple] = useState<string | null>(null);

    const filteredInsights = useMemo(() => {
        return insights.filter((insight) => {
            const matchesSearch = searchQuery === '' ||
                insight.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                insight.summary.toLowerCase().includes(searchQuery.toLowerCase());

            const matchesPrinciple = !selectedPrinciple ||
                insight.principle.slug === selectedPrinciple;

            return matchesSearch && matchesPrinciple;
        });
    }, [insights, searchQuery, selectedPrinciple]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Playbook" />
            <div className="flex h-full flex-1 flex-col">
                {/* Hero Header */}
                <div className="border-b bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-950">
                    <div className="max-w-4xl mx-auto px-4 py-12 md:py-16">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-12 h-12 rounded-xl bg-primary flex items-center justify-center">
                                <BookOpen className="w-6 h-6 text-primary-foreground" />
                            </div>
                            <h1 className="text-3xl md:text-4xl font-bold text-neutral-900 dark:text-neutral-100">
                                The Playbook
                            </h1>
                        </div>
                        <p className="text-lg text-neutral-600 dark:text-neutral-400 max-w-2xl">
                            Tactics and techniques for communicating with clarity, confidence, and impact.
                            Read, learn, then practice.
                        </p>

                        {/* Search */}
                        <div className="mt-8 relative max-w-xl">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" />
                            <Input
                                type="text"
                                placeholder="Search topics..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pl-10 h-12 text-base bg-white dark:bg-neutral-900 border-neutral-200 dark:border-neutral-700"
                            />
                        </div>

                        {/* Principle filters */}
                        <div className="mt-4 flex flex-wrap gap-2">
                            <button
                                onClick={() => setSelectedPrinciple(null)}
                                className={`px-3 py-1.5 text-sm rounded-full transition-colors ${
                                    !selectedPrinciple
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700'
                                }`}
                            >
                                All
                            </button>
                            {principles.map((principle) => (
                                <button
                                    key={principle.id}
                                    onClick={() => setSelectedPrinciple(principle.slug)}
                                    className={`px-3 py-1.5 text-sm rounded-full transition-colors ${
                                        selectedPrinciple === principle.slug
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-700'
                                    }`}
                                >
                                    {principle.name}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Blog Feed */}
                <div className="flex-1 bg-white dark:bg-neutral-950">
                    <div className="max-w-4xl mx-auto px-4 py-8">
                        {filteredInsights.length === 0 ? (
                            <div className="text-center py-16">
                                <BookOpen className="w-12 h-12 mx-auto mb-4 text-neutral-300 dark:text-neutral-700" />
                                <p className="text-neutral-500">
                                    {searchQuery || selectedPrinciple
                                        ? 'No articles match your search.'
                                        : 'No articles yet.'}
                                </p>
                            </div>
                        ) : (
                            <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                {filteredInsights.map((insight) => (
                                    <article key={insight.id} className="py-8 first:pt-0">
                                        <div className="flex items-start gap-6">
                                            <div className="flex-1 min-w-0">
                                                {/* Meta */}
                                                <div className="flex items-center gap-3 mb-3">
                                                    <Badge variant="secondary" className="text-xs font-medium">
                                                        {insight.principle.name}
                                                    </Badge>
                                                    <span className="flex items-center gap-1 text-xs text-neutral-500">
                                                        <Clock className="w-3 h-3" />
                                                        {insight.read_time} min read
                                                    </span>
                                                </div>

                                                {/* Title */}
                                                <Link
                                                    href={`/playbook/${insight.slug}`}
                                                    className="group"
                                                >
                                                    <h2 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100 group-hover:text-primary transition-colors mb-2">
                                                        {insight.name}
                                                    </h2>
                                                </Link>

                                                {/* Summary */}
                                                <p className="text-neutral-600 dark:text-neutral-400 leading-relaxed mb-4">
                                                    {insight.summary}
                                                </p>

                                                {/* Actions */}
                                                <div className="flex items-center gap-4">
                                                    <Link
                                                        href={`/playbook/${insight.slug}`}
                                                        className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary/80"
                                                    >
                                                        Read more
                                                        <ArrowRight className="w-4 h-4" />
                                                    </Link>
                                                    {insight.drill && (
                                                        isLoggedIn ? (
                                                            <Link href={`/practice-modes/${insight.drill.practice_mode_slug}/train`}>
                                                                <Button variant="outline" size="sm">
                                                                    Practice this
                                                                </Button>
                                                            </Link>
                                                        ) : (
                                                            <Link href="/register">
                                                                <Button variant="outline" size="sm">
                                                                    Sign up to practice
                                                                </Button>
                                                            </Link>
                                                        )
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
