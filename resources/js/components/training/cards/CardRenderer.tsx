import { type Card, type LevelUpCard as LevelUpCardType, type ModeConfig } from '@/types/training';
import { ScenarioCard } from './ScenarioCard';
import { PromptCard } from './PromptCard';
import { ChoiceCard } from './ChoiceCard';
import { InsightCard } from './InsightCard';
import { ReflectionCard } from './ReflectionCard';
import { LevelUpCard } from './LevelUpCard';

interface CardRendererProps {
    card: Card;
    config: ModeConfig;
    onSubmit: (input: string) => void;
    onContinue: () => void;
    isLoading?: boolean;
}

export function CardRenderer({ card, config, onSubmit, onContinue, isLoading }: CardRendererProps) {
    switch (card.type) {
        case 'scenario':
            return <ScenarioCard card={card} onContinue={onContinue} />;

        case 'prompt':
            return (
                <PromptCard
                    card={card}
                    onSubmit={onSubmit}
                    characterLimit={config.input_character_limit}
                    isLoading={isLoading}
                />
            );

        case 'multiple_choice':
            return (
                <ChoiceCard
                    card={card}
                    onSelect={onSubmit}
                    isLoading={isLoading}
                />
            );

        case 'insight':
            return <InsightCard card={card} onContinue={onContinue} />;

        case 'reflection':
            return (
                <ReflectionCard
                    card={card}
                    onSubmit={onSubmit}
                    characterLimit={config.reflection_character_limit}
                    isLoading={isLoading}
                />
            );

        default:
            return (
                <div className="text-center text-neutral-500 dark:text-neutral-400">
                    Unknown card type
                </div>
            );
    }
}

interface LevelUpRendererProps {
    card: LevelUpCardType;
    onContinue: () => void;
}

export function LevelUpRenderer({ card, onContinue }: LevelUpRendererProps) {
    return <LevelUpCard card={card} onContinue={onContinue} />;
}
