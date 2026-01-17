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
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface Track {
    id: number;
    title: string;
    description: string;
    active: boolean;
}

// Enhanced Track with nested relationships for dashboard
export interface TrackWithDetails {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    pitch: string | null;
    duration_weeks: number;
    sessions_per_week: number;
    session_duration_minutes: number;
    is_active: boolean;
    is_enrolled: boolean;
    is_activated: boolean;
    enrollment: TrackEnrollmentSummary | null;
    can_activate: boolean;
    activation_blocked_reason: string | null;
    skill_levels: SkillLevelWithLessons[];
}

export interface TrackEnrollmentSummary {
    id: number;
    status: 'active' | 'paused' | 'completed' | 'abandoned';
    enrolled_at: string | null;
    activated_at: string | null;
}

export interface CooldownInfo {
    has_cooldown: boolean;
    cooldown_days?: number;
    can_switch: boolean;
    days_remaining?: number;
    last_switch?: string;
    cooldown_ends_at?: string;
    current_track_id?: number;
}

export interface SkillLevelWithLessons {
    id: number;
    track_id: number;
    slug: string;
    name: string;
    description: string | null;
    level_number: number;
    pass_threshold: number;
    lessons: LessonSummary[];
}

export interface LessonSummary {
    id: number;
    lesson_number: number;
    title: string;
    is_completed: boolean;
    is_locked: boolean;
}

export interface UserTrackEnrollment {
    id: number;
    user_id: number;
    track_id: number;
    current_skill_level_id: number | null;
    enrolled_at: string;
    activated_at: string | null;
    completed_at: string | null;
    status: 'active' | 'paused' | 'completed' | 'abandoned';
}

// Lesson Flow Types
export interface LessonWithContent {
    id: number;
    track_id: number;
    skill_level_id: number;
    lesson_number: number;
    title: string;
    learning_objectives: string[] | null;
    estimated_duration_minutes: number | null;
    track: LessonTrack;
    skill_level: SkillLevel;
    content_blocks: LessonContentBlock[];
    questions: LessonQuestionWithOptions[];
}

export interface LessonTrack {
    id: number;
    slug: string;
    name: string;
    description: string | null;
}

export interface SkillLevel {
    id: number;
    track_id: number;
    slug: string;
    name: string;
    description: string | null;
    level_number: number;
    pass_threshold: number;
}

export interface LessonContentBlock {
    id: number;
    lesson_id: number;
    block_type: 'principle_text' | 'audio' | 'video' | 'image' | 'instruction_text';
    content: {
        text?: string;
        url?: string;
        title?: string;
        context?: string;
        transcript?: string;
        duration_seconds?: number;
        max_replays?: number;
        [key: string]: unknown;
    };
    sort_order: number;
}

export interface LessonQuestionWithOptions {
    id: number;
    lesson_id: number;
    skill_level_id: number;
    question_text: string;
    question_type: 'multiple_choice' | 'true_false' | 'open_ended';
    explanation: string | null;
    points: number;
    sort_order: number;
    answer_options: AnswerOption[];
}

export interface AnswerOption {
    id: number;
    question_id: number;
    option_text: string;
    is_correct: boolean;
    sort_order: number;
    feedback?: AnswerFeedback | null;
}

export interface AnswerFeedback {
    id: number;
    question_id: number;
    answer_option_id: number | null;
    feedback_text: string;
    pattern_tag: string | null;
    severity: 'critical_miss' | 'minor_miss' | 'correct' | null;
}

// Lesson Attempt Types
export interface LessonAttempt {
    id: number;
    user_id: number;
    lesson_id: number;
    started_at: string;
    completed_at: string | null;
    total_questions: number | null;
    correct_answers: number | null;
    accuracy_percentage: number | null;
    duration_seconds: number | null;
}

export interface LessonCompletionResult {
    attempt: LessonAttempt;
    score: number;
    total_questions: number;
    accuracy_percentage: number;
    passed: boolean;
    weakness_patterns: WeaknessPatternSummary[];
    transcript: string | null;
}

export interface WeaknessPatternSummary {
    pattern_tag: string;
    occurrence_count: number;
    severity_label: string;
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
    [key: string]: unknown; // This allows for additional properties...
}
