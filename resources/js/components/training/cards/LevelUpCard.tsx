import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type LevelUpCard as LevelUpCardType } from '@/types/training';
import { ChevronRight, Star, Lock } from 'lucide-react';

interface LevelUpCardProps {
    card: LevelUpCardType;
    onContinue: () => void;
}

function getLevelLabel(level: number): string {
    const labels: Record<number, string> = {
        1: 'Beginner',
        2: 'Novice',
        3: 'Intermediate',
        4: 'Advanced',
        5: 'Expert',
    };
    return labels[level] || `Level ${level}`;
}

export function LevelUpCard({ card, onContinue }: LevelUpCardProps) {
    const isLevelCap = card.type === 'level_cap';

    return (
        <Card className={`w-full max-w-2xl mx-auto ${isLevelCap ? 'border-amber-200 dark:border-amber-800' : 'border-emerald-200 dark:border-emerald-800'}`}>
            <CardContent className="pt-6">
                <div className="text-center space-y-4">
                    <div className={`inline-flex h-16 w-16 items-center justify-center rounded-full ${isLevelCap ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-emerald-100 dark:bg-emerald-900/30'}`}>
                        {isLevelCap ? (
                            <Lock className="h-8 w-8 text-amber-600 dark:text-amber-400" />
                        ) : (
                            <Star className="h-8 w-8 text-emerald-600 dark:text-emerald-400" />
                        )}
                    </div>
                    {!isLevelCap && card.new_level && (
                        <div>
                            <h2 className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                Level Up!
                            </h2>
                            <p className="text-lg font-medium text-neutral-700 dark:text-neutral-300 mt-1">
                                You've reached {getLevelLabel(card.new_level)}
                            </p>
                        </div>
                    )}
                    {isLevelCap && (
                        <h2 className="text-xl font-bold text-amber-600 dark:text-amber-400">
                            Level Cap Reached
                        </h2>
                    )}
                    <p className="text-neutral-600 dark:text-neutral-400">
                        {card.message}
                    </p>
                </div>
            </CardContent>
            <CardFooter className="justify-center">
                <Button onClick={onContinue}>
                    Continue Training
                    <ChevronRight className="ml-2 h-4 w-4" />
                </Button>
            </CardFooter>
        </Card>
    );
}
