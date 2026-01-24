import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Clock, PlayCircle, ChevronRight, UserPlus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import ReactMarkdown from 'react-markdown';

interface Drill {
    id: number;
    name: string;
    is_primary: boolean;
    practice_mode: {
        id: number;
        name: string;
        slug: string;
    };
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
    principle: Principle;
    drills: Drill[];
}

interface Props {
    insight: Insight;
}

export default function InsightShow({ insight }: Props) {
    const { auth } = usePage<SharedData>().props;
    const isLoggedIn = !!auth.user;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Playbook', href: '/playbook' },
        { title: insight.name, href: `/playbook/${insight.slug}` },
    ];

    const primaryDrill = insight.drills.find(d => d.is_primary);
    const readTime = Math.max(1, Math.ceil(insight.content.split(/\s+/).length / 200));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={insight.name} />

            <article className="flex-1">
                {/* Hero Header */}
                <div className="border-b bg-gradient-to-br from-neutral-50 to-neutral-100 dark:from-neutral-900 dark:to-neutral-950">
                    <div className="max-w-3xl mx-auto px-4 py-12 md:py-16">
                        {/* Back link */}
                        <Link
                            href="/playbook"
                            className="inline-flex items-center gap-1 text-sm text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 mb-6"
                        >
                            <ArrowLeft className="w-4 h-4" />
                            Back to Playbook
                        </Link>

                        {/* Meta */}
                        <div className="flex items-center gap-3 mb-4">
                            <Badge className="bg-primary text-primary-foreground hover:bg-primary/90">
                                {insight.principle.name}
                            </Badge>
                            <span className="flex items-center gap-1 text-sm text-neutral-500">
                                <Clock className="w-4 h-4" />
                                {readTime} min read
                            </span>
                        </div>

                        {/* Title */}
                        <h1 className="text-3xl md:text-4xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                            {insight.name}
                        </h1>

                        {/* Summary/Lede */}
                        <p className="text-xl text-neutral-600 dark:text-neutral-400 leading-relaxed">
                            {insight.summary}
                        </p>
                    </div>
                </div>

                {/* Article Content */}
                <div className="bg-white dark:bg-neutral-950">
                    <div className="max-w-3xl mx-auto px-4 py-10 md:py-14">
                        <div className="article-content">
                            <ReactMarkdown>
                                {insight.content}
                            </ReactMarkdown>
                        </div>
                    </div>
                </div>

                {/* Practice CTA - show for guests always, or for logged-in users if there's a drill */}
                {(!isLoggedIn || primaryDrill) && (
                    <div className="border-t bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-950/30 dark:to-amber-950/30">
                        <div className="max-w-3xl mx-auto px-4 py-10 md:py-14">
                            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                                <div>
                                    <h2 className="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                                        Time to put this into practice
                                    </h2>
                                    <p className="text-neutral-600 dark:text-neutral-400">
                                        {isLoggedIn
                                            ? 'Apply what you\'ve learned in a realistic scenario. Get instant AI feedback on your response.'
                                            : 'Sign up for free to practice with AI-powered scenarios and get instant feedback.'}
                                    </p>
                                </div>
                                {isLoggedIn && primaryDrill ? (
                                    <Link href={`/practice-modes/${primaryDrill.practice_mode.slug}/train`}>
                                        <Button size="lg" className="bg-primary hover:bg-primary/90 text-primary-foreground whitespace-nowrap">
                                            <PlayCircle className="w-5 h-5 mr-2" />
                                            Practice Now
                                        </Button>
                                    </Link>
                                ) : !isLoggedIn ? (
                                    <Link href="/register">
                                        <Button size="lg" className="bg-primary hover:bg-primary/90 text-primary-foreground whitespace-nowrap">
                                            <UserPlus className="w-5 h-5 mr-2" />
                                            Sign Up Free
                                        </Button>
                                    </Link>
                                ) : null}
                            </div>
                        </div>
                    </div>
                )}

                {/* Related Drills - only show to logged in users */}
                {isLoggedIn && insight.drills.filter(d => !d.is_primary).length > 0 && (
                    <div className="border-t bg-neutral-50 dark:bg-neutral-900">
                        <div className="max-w-3xl mx-auto px-4 py-10">
                            <h3 className="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                                Related Practice
                            </h3>
                            <div className="space-y-3">
                                {insight.drills.filter(d => !d.is_primary).map((drill) => (
                                    <Link
                                        key={drill.id}
                                        href={`/practice-modes/${drill.practice_mode.slug}/train`}
                                        className="flex items-center justify-between p-4 rounded-lg bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 hover:border-primary/50 dark:hover:border-primary/50 transition-colors group"
                                    >
                                        <div>
                                            <p className="font-medium text-neutral-900 dark:text-neutral-100 group-hover:text-primary">
                                                {drill.name}
                                            </p>
                                            <p className="text-sm text-neutral-500">
                                                {drill.practice_mode.name}
                                            </p>
                                        </div>
                                        <ChevronRight className="w-5 h-5 text-neutral-400 group-hover:text-primary" />
                                    </Link>
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </article>
        </AppLayout>
    );
}
