import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import PracticeModeForm from '@/components/admin/practice-mode-form';

interface PracticeModeConfig {
    input_character_limit: number;
    reflection_character_limit: number;
    max_response_tokens: number;
    max_history_exchanges: number;
    model: string;
}

interface PracticeMode {
    id: number;
    name: string;
    slug: string;
    tagline: string | null;
    description: string | null;
    instruction_set: string;
    config: PracticeModeConfig;
    required_plan: string | null;
    is_active: boolean;
    sort_order: number;
}

interface Tag {
    id: number;
    name: string;
    slug: string;
    category: string;
    display_order: number;
}

interface InsightOption {
    id: number;
    name: string;
}

interface PrincipleWithInsights {
    id: number;
    name: string;
    insights: InsightOption[];
}

interface Props {
    mode: PracticeMode;
    tagsByCategory: Record<string, Tag[]>;
    selectedTags: number[];
    insightsByPrinciple: PrincipleWithInsights[];
    contextFields: Record<string, string>;
    selectedContext: string[];
}

export default function PracticeModeEdit({ mode, tagsByCategory, selectedTags, insightsByPrinciple, contextFields, selectedContext }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/users' },
        { title: 'Practice Modes', href: '/admin/practice-modes' },
        { title: mode.name, href: `/admin/practice-modes/${mode.id}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit - ${mode.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-4xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/practice-modes">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Practice Modes
                        </Button>
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold">Edit Practice Mode</h1>
                    <p className="text-neutral-500 mt-1">
                        Update the configuration for "{mode.name}"
                    </p>
                </div>

                <PracticeModeForm
                    mode={{
                        ...mode,
                        tagline: mode.tagline || '',
                        description: mode.description || '',
                    }}
                    isEdit
                    tagsByCategory={tagsByCategory}
                    selectedTags={selectedTags}
                    insightsByPrinciple={insightsByPrinciple}
                    contextFields={contextFields}
                    selectedContext={selectedContext}
                />
            </div>
        </AppLayout>
    );
}
