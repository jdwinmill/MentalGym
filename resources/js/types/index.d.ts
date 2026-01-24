import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
    isAdmin: boolean;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
    badge?: string;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface UserProfile {
    id: number;
    user_id: number;
    // Demographics
    birth_year: number | null;
    gender: string | null;
    zip_code: string | null;
    // Career Context
    job_title: string | null;
    industry: string | null;
    company_size: 'startup' | 'smb' | 'enterprise' | null;
    career_level: 'entry' | 'mid' | 'senior' | 'executive' | 'founder' | null;
    years_in_role: number | null;
    years_experience: number | null;
    // Team & Reporting
    manages_people: boolean;
    direct_reports: number | null;
    reports_to_role: string | null;
    team_composition: 'colocated' | 'remote' | 'hybrid' | 'international' | null;
    // Work Environment
    collaboration_style: 'async' | 'meeting-heavy' | 'mixed' | null;
    cross_functional_teams: string[] | null;
    communication_tools: string[] | null;
    // Professional Goals
    improvement_areas: string[] | null;
    upcoming_challenges: string[] | null;
}

export interface ProfileOptions {
    companySizes: Record<string, string>;
    careerLevels: Record<string, string>;
    teamCompositions: Record<string, string>;
    collaborationStyles: Record<string, string>;
    crossFunctionalOptions: Record<string, string>;
    improvementAreas: Record<string, string>;
    challenges: Record<string, string>;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    profile?: UserProfile;
    [key: string]: unknown;
}
