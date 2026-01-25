import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import SkillDimensionForm from '@/components/admin/skill-dimension-form';

interface DimensionData {
    key: string;
    label: string;
    description: string | null;
    category: string | null;
    anchor_low: string;
    anchor_mid: string;
    anchor_high: string;
    anchor_exemplary: string;
    active: boolean;
}

interface Props {
    dimension: DimensionData;
}

export default function SkillDimensionEdit({ dimension }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/users' },
        { title: 'Skill Dimensions', href: '/admin/skill-dimensions' },
        { title: dimension.label, href: `/admin/skill-dimensions/${dimension.key}/edit` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit - ${dimension.label}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-2xl">
                <div className="flex items-center gap-4">
                    <Link href="/admin/skill-dimensions">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Skill Dimensions
                        </Button>
                    </Link>
                </div>

                <div>
                    <h1 className="text-2xl font-bold">Edit Skill Dimension</h1>
                    <p className="text-neutral-500 mt-1">
                        Update "{dimension.label}"
                    </p>
                </div>

                <SkillDimensionForm dimension={dimension} isEdit />
            </div>
        </AppLayout>
    );
}
