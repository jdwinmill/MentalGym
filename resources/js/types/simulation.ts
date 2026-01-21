export type UserType = 'cooperative' | 'terse' | 'verbose' | 'confused' | 'adversarial';

export type IssueSeverity = 'error' | 'warning';

export type IssueType =
    | 'repeated_card'
    | 'wrong_sequence'
    | 'missing_input_config'
    | 'missing_options'
    | 'character_break';

export interface SimulationCard {
    type: 'scenario' | 'prompt' | 'insight' | 'reflection' | 'multiple_choice';
    content: string;
    input?: {
        type: string;
        max_length: number;
        placeholder: string;
    };
    options?: Array<{
        id: string;
        label: string;
    }>;
    drill_phase?: string;
    is_iteration?: boolean;
}

export interface TranscriptEntry {
    role: 'assistant' | 'user';
    card?: SimulationCard;
    content?: string;
}

export interface SimulationIssue {
    type: IssueType;
    severity: IssueSeverity;
    description: string;
    exchange: number;
}

export interface SimulationResult {
    transcript: TranscriptEntry[];
    issues: SimulationIssue[];
    improved_instruction_set: string;
    exchange_count: number;
}

export interface SimulationResponse {
    success: boolean;
    data?: SimulationResult;
    error?: string;
}

export interface SimulationConfig {
    interactionCount: number;
    userType: UserType;
}

export const USER_TYPE_LABELS: Record<UserType, string> = {
    cooperative: 'Cooperative',
    terse: 'Terse',
    verbose: 'Verbose',
    confused: 'Confused',
    adversarial: 'Adversarial',
};

export const USER_TYPE_DESCRIPTIONS: Record<UserType, string> = {
    cooperative: 'Engaged, follows instructions, thoughtful responses',
    terse: 'Minimal responses, few words',
    verbose: 'Long, detailed, tangential responses',
    confused: 'Misinterprets prompts, asks clarifications',
    adversarial: 'Tests boundaries, tries to break character',
};

export const INTERACTION_COUNT_OPTIONS = [10, 15, 20, 25] as const;
