import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { PlayIcon, ChevronDownIcon } from 'lucide-react';
import {
    UserType,
    SimulationConfig,
    USER_TYPE_LABELS,
    USER_TYPE_DESCRIPTIONS,
    INTERACTION_COUNT_OPTIONS,
} from '@/types/simulation';

interface SimulationButtonProps {
    onRunSimulation: (config: SimulationConfig) => void;
    disabled?: boolean;
}

export function SimulationButton({ onRunSimulation, disabled }: SimulationButtonProps) {
    const [interactionCount, setInteractionCount] = useState<number>(15);
    const [userType, setUserType] = useState<UserType>('cooperative');

    const handleRunSimulation = () => {
        onRunSimulation({
            interactionCount,
            userType,
        });
    };

    return (
        <div className="flex items-center gap-2">
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm" disabled={disabled}>
                        {interactionCount} exchanges
                        <ChevronDownIcon className="ml-1 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start">
                    <DropdownMenuLabel>Interaction Count</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuRadioGroup
                        value={interactionCount.toString()}
                        onValueChange={(v) => setInteractionCount(parseInt(v))}
                    >
                        {INTERACTION_COUNT_OPTIONS.map((count) => (
                            <DropdownMenuRadioItem key={count} value={count.toString()}>
                                {count} exchanges
                            </DropdownMenuRadioItem>
                        ))}
                    </DropdownMenuRadioGroup>
                </DropdownMenuContent>
            </DropdownMenu>

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm" disabled={disabled}>
                        {USER_TYPE_LABELS[userType]}
                        <ChevronDownIcon className="ml-1 h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" className="w-64">
                    <DropdownMenuLabel>User Type</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuRadioGroup
                        value={userType}
                        onValueChange={(v) => setUserType(v as UserType)}
                    >
                        {(Object.keys(USER_TYPE_LABELS) as UserType[]).map((type) => (
                            <DropdownMenuRadioItem key={type} value={type} className="flex flex-col items-start py-2">
                                <span className="font-medium">{USER_TYPE_LABELS[type]}</span>
                                <span className="text-xs text-muted-foreground">
                                    {USER_TYPE_DESCRIPTIONS[type]}
                                </span>
                            </DropdownMenuRadioItem>
                        ))}
                    </DropdownMenuRadioGroup>
                </DropdownMenuContent>
            </DropdownMenu>

            <Button onClick={handleRunSimulation} disabled={disabled} size="sm">
                <PlayIcon className="mr-1 h-4 w-4" />
                Run Simulation
            </Button>
        </div>
    );
}
