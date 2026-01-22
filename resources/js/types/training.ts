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

// Session Types
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
