import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { send } from '@/routes/verification';
import {
    type BreadcrumbItem,
    type ProfileOptions,
    type SharedData,
    type UserProfile,
} from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { useState } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit } from '@/routes/profile';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

interface SectionProps {
    title: string;
    description?: string;
    defaultOpen?: boolean;
    children: React.ReactNode;
}

function Section({
    title,
    description,
    defaultOpen = false,
    children,
}: SectionProps) {
    const [isOpen, setIsOpen] = useState(defaultOpen);

    return (
        <Collapsible open={isOpen} onOpenChange={setIsOpen}>
            <CollapsibleTrigger className="flex w-full items-center justify-between rounded-lg border bg-card p-4 text-left transition-colors hover:bg-accent/50">
                <div>
                    <h3 className="text-base font-medium">{title}</h3>
                    {description && (
                        <p className="text-sm text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
                <ChevronDown
                    className={`h-5 w-5 text-muted-foreground transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}
                />
            </CollapsibleTrigger>
            <CollapsibleContent className="px-1 pt-4">
                <div className="space-y-4">{children}</div>
            </CollapsibleContent>
        </Collapsible>
    );
}

interface MultiSelectProps {
    options: Record<string, string>;
    value: string[] | null;
    name: string;
    onChange: (values: string[]) => void;
}

function MultiSelect({ options, value, name, onChange }: MultiSelectProps) {
    const currentValues = value ?? [];

    const toggleValue = (key: string) => {
        if (currentValues.includes(key)) {
            onChange(currentValues.filter((v) => v !== key));
        } else {
            onChange([...currentValues, key]);
        }
    };

    return (
        <div className="flex flex-wrap gap-2">
            {Object.entries(options).map(([key, label]) => (
                <label
                    key={key}
                    className={`flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors ${
                        currentValues.includes(key)
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-border hover:bg-accent/50'
                    }`}
                >
                    <input
                        type="checkbox"
                        name={name}
                        value={key}
                        checked={currentValues.includes(key)}
                        onChange={() => toggleValue(key)}
                        className="sr-only"
                    />
                    {label}
                </label>
            ))}
        </div>
    );
}

export default function Profile({
    mustVerifyEmail,
    status,
    profile,
    profileOptions,
}: {
    mustVerifyEmail: boolean;
    status?: string;
    profile: UserProfile | null;
    profileOptions: ProfileOptions;
}) {
    const { auth } = usePage<SharedData>().props;

    // Local state for form values (for controlled components)
    const [formData, setFormData] = useState({
        // Demographics
        birth_year: profile?.birth_year?.toString() ?? '',
        gender: profile?.gender ?? '',
        zip_code: profile?.zip_code ?? '',
        // Career Context
        job_title: profile?.job_title ?? '',
        industry: profile?.industry ?? '',
        company_size: profile?.company_size ?? '',
        career_level: profile?.career_level ?? '',
        years_in_role: profile?.years_in_role?.toString() ?? '',
        years_experience: profile?.years_experience?.toString() ?? '',
        // Team Structure
        manages_people: profile?.manages_people ?? false,
        direct_reports: profile?.direct_reports?.toString() ?? '',
        reports_to_role: profile?.reports_to_role ?? '',
        team_composition: profile?.team_composition ?? '',
        // Work Environment
        collaboration_style: profile?.collaboration_style ?? '',
        cross_functional_teams: profile?.cross_functional_teams ?? [],
        communication_tools: profile?.communication_tools ?? [],
        // Professional Goals
        improvement_areas: profile?.improvement_areas ?? [],
        upcoming_challenges: profile?.upcoming_challenges ?? [],
    });

    const updateField = <K extends keyof typeof formData>(
        key: K,
        value: (typeof formData)[K],
    ) => {
        setFormData((prev) => ({ ...prev, [key]: value }));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile Settings</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Profile information"
                        description="Update your account and professional details to personalize your training experience"
                    />

                    <Form
                        {...ProfileController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors }) => (
                            <>
                                {/* Basic Information Section - Always visible */}
                                <div className="space-y-4 rounded-lg border bg-card p-4">
                                    <h3 className="text-base font-medium">
                                        Basic Information
                                    </h3>

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">Name</Label>
                                            <Input
                                                id="name"
                                                defaultValue={auth.user.name}
                                                name="name"
                                                required
                                                autoComplete="name"
                                                placeholder="Full name"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="email">
                                                Email address
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                defaultValue={auth.user.email}
                                                name="email"
                                                required
                                                autoComplete="username"
                                                placeholder="Email address"
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                    </div>

                                    {mustVerifyEmail &&
                                        auth.user.email_verified_at ===
                                            null && (
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    Your email address is
                                                    unverified.{' '}
                                                    <Link
                                                        href={send()}
                                                        as="button"
                                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                                    >
                                                        Click here to resend the
                                                        verification email.
                                                    </Link>
                                                </p>

                                                {status ===
                                                    'verification-link-sent' && (
                                                    <div className="mt-2 text-sm font-medium text-green-600">
                                                        A new verification link
                                                        has been sent to your
                                                        email address.
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                </div>

                                {/* Demographics Section */}
                                <Section
                                    title="Demographics"
                                    description="Help us tailor scenarios to your context"
                                >
                                    <div className="grid gap-4 sm:grid-cols-3">
                                        <div className="grid gap-2">
                                            <Label htmlFor="birth_year">
                                                Birth Year
                                            </Label>
                                            <Input
                                                id="birth_year"
                                                type="number"
                                                name="birth_year"
                                                min={1920}
                                                max={2015}
                                                value={formData.birth_year}
                                                onChange={(e) =>
                                                    updateField(
                                                        'birth_year',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., 1990"
                                            />
                                            <InputError
                                                message={errors.birth_year}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="gender">
                                                Gender
                                            </Label>
                                            <Input
                                                id="gender"
                                                name="gender"
                                                value={formData.gender}
                                                onChange={(e) =>
                                                    updateField(
                                                        'gender',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., Male, Female, Non-binary"
                                            />
                                            <InputError
                                                message={errors.gender}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="zip_code">
                                                Zip Code
                                            </Label>
                                            <Input
                                                id="zip_code"
                                                name="zip_code"
                                                value={formData.zip_code}
                                                onChange={(e) =>
                                                    updateField(
                                                        'zip_code',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., 94102"
                                            />
                                            <InputError
                                                message={errors.zip_code}
                                            />
                                        </div>
                                    </div>
                                </Section>

                                {/* Career Context Section */}
                                <Section
                                    title="Career Context"
                                    description="Your professional background for realistic scenarios"
                                >
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="job_title">
                                                Job Title
                                            </Label>
                                            <Input
                                                id="job_title"
                                                name="job_title"
                                                value={formData.job_title}
                                                onChange={(e) =>
                                                    updateField(
                                                        'job_title',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., Product Manager"
                                            />
                                            <InputError
                                                message={errors.job_title}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="industry">
                                                Industry
                                            </Label>
                                            <Input
                                                id="industry"
                                                name="industry"
                                                value={formData.industry}
                                                onChange={(e) =>
                                                    updateField(
                                                        'industry',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., Technology, Healthcare"
                                            />
                                            <InputError
                                                message={errors.industry}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="company_size">
                                                Company Size
                                            </Label>
                                            <Select
                                                name="company_size"
                                                value={formData.company_size}
                                                onValueChange={(v) =>
                                                    updateField(
                                                        'company_size',
                                                        v,
                                                    )
                                                }
                                            >
                                                <SelectTrigger id="company_size">
                                                    <SelectValue placeholder="Select company size" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {Object.entries(
                                                        profileOptions.companySizes,
                                                    ).map(([key, label]) => (
                                                        <SelectItem
                                                            key={key}
                                                            value={key}
                                                        >
                                                            {label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.company_size}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="career_level">
                                                Career Level
                                            </Label>
                                            <Select
                                                name="career_level"
                                                value={formData.career_level}
                                                onValueChange={(v) =>
                                                    updateField(
                                                        'career_level',
                                                        v,
                                                    )
                                                }
                                            >
                                                <SelectTrigger id="career_level">
                                                    <SelectValue placeholder="Select career level" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {Object.entries(
                                                        profileOptions.careerLevels,
                                                    ).map(([key, label]) => (
                                                        <SelectItem
                                                            key={key}
                                                            value={key}
                                                        >
                                                            {label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.career_level}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="years_in_role">
                                                Years in Current Role
                                            </Label>
                                            <Input
                                                id="years_in_role"
                                                type="number"
                                                name="years_in_role"
                                                min={0}
                                                max={50}
                                                value={formData.years_in_role}
                                                onChange={(e) =>
                                                    updateField(
                                                        'years_in_role',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., 2"
                                            />
                                            <InputError
                                                message={errors.years_in_role}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="years_experience">
                                                Total Years of Experience
                                            </Label>
                                            <Input
                                                id="years_experience"
                                                type="number"
                                                name="years_experience"
                                                min={0}
                                                max={60}
                                                value={formData.years_experience}
                                                onChange={(e) =>
                                                    updateField(
                                                        'years_experience',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="e.g., 8"
                                            />
                                            <InputError
                                                message={errors.years_experience}
                                            />
                                        </div>
                                    </div>
                                </Section>

                                {/* Team Structure Section */}
                                <Section
                                    title="Team Structure"
                                    description="Your reporting relationships and team setup"
                                >
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-3">
                                            <Checkbox
                                                id="manages_people"
                                                name="manages_people"
                                                checked={
                                                    formData.manages_people
                                                }
                                                onCheckedChange={(checked) =>
                                                    updateField(
                                                        'manages_people',
                                                        checked === true,
                                                    )
                                                }
                                            />
                                            <Label
                                                htmlFor="manages_people"
                                                className="cursor-pointer"
                                            >
                                                I manage people
                                            </Label>
                                        </div>

                                        <div className="grid gap-4 sm:grid-cols-2">
                                            {formData.manages_people && (
                                                <div className="grid gap-2">
                                                    <Label htmlFor="direct_reports">
                                                        Number of Direct Reports
                                                    </Label>
                                                    <Input
                                                        id="direct_reports"
                                                        type="number"
                                                        name="direct_reports"
                                                        min={0}
                                                        max={1000}
                                                        value={
                                                            formData.direct_reports
                                                        }
                                                        onChange={(e) =>
                                                            updateField(
                                                                'direct_reports',
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="e.g., 5"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.direct_reports
                                                        }
                                                    />
                                                </div>
                                            )}

                                            <div className="grid gap-2">
                                                <Label htmlFor="reports_to_role">
                                                    Reports To
                                                </Label>
                                                <Input
                                                    id="reports_to_role"
                                                    name="reports_to_role"
                                                    value={
                                                        formData.reports_to_role
                                                    }
                                                    onChange={(e) =>
                                                        updateField(
                                                            'reports_to_role',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="e.g., VP of Engineering"
                                                />
                                                <InputError
                                                    message={
                                                        errors.reports_to_role
                                                    }
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="team_composition">
                                                    Team Setup
                                                </Label>
                                                <Select
                                                    name="team_composition"
                                                    value={
                                                        formData.team_composition
                                                    }
                                                    onValueChange={(v) =>
                                                        updateField(
                                                            'team_composition',
                                                            v,
                                                        )
                                                    }
                                                >
                                                    <SelectTrigger id="team_composition">
                                                        <SelectValue placeholder="Select team setup" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {Object.entries(
                                                            profileOptions.teamCompositions,
                                                        ).map(([key, label]) => (
                                                            <SelectItem
                                                                key={key}
                                                                value={key}
                                                            >
                                                                {label}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <InputError
                                                    message={
                                                        errors.team_composition
                                                    }
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </Section>

                                {/* Work Environment Section */}
                                <Section
                                    title="Work Environment"
                                    description="How you work and collaborate with others"
                                >
                                    <div className="space-y-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="collaboration_style">
                                                Collaboration Style
                                            </Label>
                                            <Select
                                                name="collaboration_style"
                                                value={
                                                    formData.collaboration_style
                                                }
                                                onValueChange={(v) =>
                                                    updateField(
                                                        'collaboration_style',
                                                        v,
                                                    )
                                                }
                                            >
                                                <SelectTrigger id="collaboration_style">
                                                    <SelectValue placeholder="Select collaboration style" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {Object.entries(
                                                        profileOptions.collaborationStyles,
                                                    ).map(([key, label]) => (
                                                        <SelectItem
                                                            key={key}
                                                            value={key}
                                                        >
                                                            {label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={
                                                    errors.collaboration_style
                                                }
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label>
                                                Teams You Work With
                                                Cross-Functionally
                                            </Label>
                                            <MultiSelect
                                                options={
                                                    profileOptions.crossFunctionalOptions
                                                }
                                                value={
                                                    formData.cross_functional_teams
                                                }
                                                name="cross_functional_teams[]"
                                                onChange={(v) =>
                                                    updateField(
                                                        'cross_functional_teams',
                                                        v,
                                                    )
                                                }
                                            />
                                            <InputError
                                                message={
                                                    errors.cross_functional_teams
                                                }
                                            />
                                        </div>
                                    </div>
                                </Section>

                                {/* Professional Goals Section */}
                                <Section
                                    title="Professional Goals"
                                    description="What you want to improve and challenges you're facing"
                                >
                                    <div className="space-y-4">
                                        <div className="grid gap-2">
                                            <Label>
                                                Areas You Want to Improve
                                            </Label>
                                            <MultiSelect
                                                options={
                                                    profileOptions.improvementAreas
                                                }
                                                value={
                                                    formData.improvement_areas
                                                }
                                                name="improvement_areas[]"
                                                onChange={(v) =>
                                                    updateField(
                                                        'improvement_areas',
                                                        v,
                                                    )
                                                }
                                            />
                                            <InputError
                                                message={
                                                    errors.improvement_areas
                                                }
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label>Upcoming Challenges</Label>
                                            <MultiSelect
                                                options={
                                                    profileOptions.challenges
                                                }
                                                value={
                                                    formData.upcoming_challenges
                                                }
                                                name="upcoming_challenges[]"
                                                onChange={(v) =>
                                                    updateField(
                                                        'upcoming_challenges',
                                                        v,
                                                    )
                                                }
                                            />
                                            <InputError
                                                message={
                                                    errors.upcoming_challenges
                                                }
                                            />
                                        </div>
                                    </div>
                                </Section>

                                {/* Hidden inputs for array fields */}
                                {formData.cross_functional_teams.map(
                                    (team, i) => (
                                        <input
                                            key={`cft-${i}`}
                                            type="hidden"
                                            name="cross_functional_teams[]"
                                            value={team}
                                        />
                                    ),
                                )}
                                {formData.improvement_areas.map((area, i) => (
                                    <input
                                        key={`ia-${i}`}
                                        type="hidden"
                                        name="improvement_areas[]"
                                        value={area}
                                    />
                                ))}
                                {formData.upcoming_challenges.map(
                                    (challenge, i) => (
                                        <input
                                            key={`uc-${i}`}
                                            type="hidden"
                                            name="upcoming_challenges[]"
                                            value={challenge}
                                        />
                                    ),
                                )}

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                        data-test="update-profile-button"
                                    >
                                        Save
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">
                                            Saved
                                        </p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
