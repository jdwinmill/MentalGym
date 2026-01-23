// Practice Mode Types
export interface Tag {
    id: number;
    name: string;
    color: string;
}

export interface ModeProgress {
    current_level: number;
    total_exchanges: number;
    total_sessions?: number;
    exchanges_at_current_level?: number;
}

export interface PracticeMode {
    id: number;
    slug: string;
    name: string;
    tagline: string;
    icon: string | null;
    required_plan: string | null;
    tags: Tag[];
    progress: ModeProgress | null;
    has_active_session: boolean;
    can_access: boolean;
}

export interface ModeConfig {
    input_character_limit: number;
    reflection_character_limit: number;
}

export interface TrainingMode {
    id: number;
    slug: string;
    name: string;
    tagline: string;
    icon: string | null;
    config: ModeConfig;
}

// =========================================================================
// New Drill-Based Types (v2 API)
// =========================================================================

export interface Drill {
    id: number;
    name: string;
    timer_seconds: number | null;
    input_type: 'text' | 'multiple_choice';
}

export interface DrillSession {
    id: number;
    drill_index: number;
    phase: 'scenario' | 'responding' | 'feedback' | 'complete';
}

export interface DrillScore {
    drill_id: number;
    drill_name: string;
    score: number;
}

export interface DrillProgress {
    current: number;
    total: number;
}

export interface DrillScenarioCard {
    type: 'scenario';
    content: string;
    task: string;
    options?: string[];
}

export interface DrillFeedbackCard {
    type: 'feedback';
    content: string;
    score: number;
}

export type DrillCard = DrillScenarioCard | DrillFeedbackCard;

export interface SessionStats {
    drills_completed: number;
    total_duration_seconds: number;
    scores: DrillScore[];
}

// v2 API Response Types
export interface StartDrillResponse {
    success: boolean;
    session: DrillSession;
    drill: Drill;
    card: DrillScenarioCard;
    progress: DrillProgress;
    resumed?: boolean;
    error?: string;
    message?: string;
}

export interface SubmitDrillResponse {
    success: boolean;
    session: DrillSession;
    card: DrillFeedbackCard;
    error?: string;
    message?: string;
}

export interface ContinueDrillResponse {
    success: boolean;
    session: DrillSession;
    drill?: Drill;
    card?: DrillScenarioCard;
    progress?: DrillProgress;
    complete?: boolean;
    stats?: SessionStats;
    error?: string;
    message?: string;
}

// =========================================================================
// Legacy Session Types (v1 API)
// @deprecated These types are for the legacy v1 API flow.
// New code should use the drill-based types above.
// =========================================================================

/** @deprecated Use DrillSession instead */
export interface Session {
    id: number;
    exchange_count: number;
    started_at: string;
}

export interface SessionProgress {
    current_level: number;
    exchanges_at_current_level: number;
    exchanges_to_next_level: number | null;
}

// Card Types
export type CardType = 'scenario' | 'prompt' | 'multiple_choice' | 'insight' | 'reflection';

export type PressureLevel = 'low' | 'medium' | 'high';

export interface UIHints {
    timed?: boolean;
    timer_seconds?: number;
    show_previous_response?: boolean;
    pressure_level?: PressureLevel;
}

export interface BaseCard {
    type: CardType;
    content: string;
    ui_hints?: UIHints;
}

export interface ScenarioCard extends BaseCard {
    type: 'scenario';
}

export interface PromptCard extends BaseCard {
    type: 'prompt';
    scenarioContext?: string;
    is_iteration?: boolean;
}

export interface MultipleChoiceCard extends BaseCard {
    type: 'multiple_choice';
    options: string[];
}

export interface InsightCard extends BaseCard {
    type: 'insight';
}

export interface ReflectionCard extends BaseCard {
    type: 'reflection';
    scenarioContext?: string;
}

export type Card = ScenarioCard | PromptCard | MultipleChoiceCard | InsightCard | ReflectionCard;

// Level Up Types
export interface LevelUpCard {
    type: 'level_up' | 'level_cap';
    new_level?: number;
    current_level?: number;
    message: string;
}

// Message History Types
export interface UserMessage {
    id: number;
    role: 'user';
    content: string;
    created_at: string;
}

export interface AssistantMessage {
    id: number;
    role: 'assistant';
    card: Card;
    type: CardType;
    created_at: string;
}

export type Message = UserMessage | AssistantMessage;

// API Response Types
export interface StartSessionResponse {
    success: boolean;
    session?: Session;
    messages?: Message[];
    card?: Card;
    resumed?: boolean;
    error?: string;
    message?: string;
}

export interface ContinueSessionResponse {
    success: boolean;
    card?: Card;
    session?: Session;
    progress?: SessionProgress;
    levelUp?: LevelUpCard;
    error?: string;
    message?: string;
}

export interface EndSessionResponse {
    success: boolean;
    message?: string;
    error?: string;
}

// Page Props Types
export interface PracticeModesIndexProps {
    modes: PracticeMode[];
}

export interface TrainPageProps {
    mode: TrainingMode;
    progress: ModeProgress;
    has_active_session: boolean;
}
