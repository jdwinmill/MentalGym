# Phase 4: Frontend

## Tasks

1. Add new types to `training.ts`
2. Simplify state in `train.tsx`
3. Create `SessionCompleteDialog` component
4. Update API calls to new endpoints
5. Simplify card rendering

---

## Types

```typescript
// resources/js/types/training.ts

export interface Drill {
    id: number;
    name: string;
    scenario_instruction_set: string;
    evaluation_instruction_set: string;
    position: number;
    timer_seconds: number | null;
    input_type: 'text' | 'multiple_choice';
    config?: Record<string, unknown>;
}

export interface Session {
    id: number;
    drill_index: number;
    phase: 'scenario' | 'responding' | 'feedback' | 'complete';
    current_scenario?: string;
    current_task?: string;
    current_options?: string[];
    drill_scores: DrillScore[];
}

export interface DrillScore {
    drill_id: number;
    drill_name: string;
    score: number;
}

export interface Progress {
    current: number;  // drill_index + 1
    total: number;    // total drills in mode
}

export interface ScenarioCard {
    type: 'scenario';
    content: string;
    task: string;
    options?: string[];
}

export interface FeedbackCard {
    type: 'feedback';
    content: string;
    score: number;
}

export type Card = ScenarioCard | FeedbackCard;

export interface SessionStats {
    drills_completed: number;
    total_duration_seconds: number;
}

export interface StartSessionResponse {
    session: Session;
    drill: Drill;
    card: ScenarioCard;
    progress: Progress;
}

export interface SubmitResponseResponse {
    session: Session;
    card: FeedbackCard;
}

export interface ContinueResponse {
    session: Session;
    drill?: Drill;
    card?: ScenarioCard;
    progress?: Progress;
    complete?: boolean;
    stats?: SessionStats;
}
```

---

## Simplified State

```typescript
// resources/js/pages/practice-modes/[slug]/train.tsx

export default function TrainPage() {
    const router = useRouter();
    const { slug } = router.query;

    // Core state
    const [session, setSession] = useState<Session | null>(null);
    const [currentDrill, setCurrentDrill] = useState<Drill | null>(null);
    const [currentCard, setCurrentCard] = useState<Card | null>(null);
    const [progress, setProgress] = useState<Progress | null>(null);

    // UI state
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Completion state
    const [showCompletionDialog, setShowCompletionDialog] = useState(false);
    const [sessionStats, setSessionStats] = useState<SessionStats | null>(null);

    // ... rest of component
}
```

### What's Removed

```typescript
// NO LONGER NEEDED:
// - lastScenarioContext (scenario is just currentCard)
// - isFetchingFollowUp (no auto-fetching)
// - is_iteration logic (removed entirely)
// - Complex useEffect chains (explicit user actions only)
// - messages array (not tracking history in frontend)
// - levelUpCard (simplified to completion dialog)
```

---

## API Calls

```typescript
// Start session
const startSession = async () => {
    setIsLoading(true);
    setError(null);

    try {
        const response = await fetch(`/api/training/start/${slug}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        });

        if (!response.ok) throw new Error('Failed to start session');

        const data: StartSessionResponse = await response.json();

        setSession(data.session);
        setCurrentDrill(data.drill);
        setCurrentCard(data.card);
        setProgress(data.progress);
    } catch (err) {
        setError('Failed to start session. Please try again.');
    } finally {
        setIsLoading(false);
    }
};

// Submit response
const submitResponse = async (response: string) => {
    if (!session) return;

    setIsLoading(true);
    setError(null);

    try {
        const res = await fetch(`/api/training/respond/${session.id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ response }),
        });

        if (!res.ok) throw new Error('Failed to submit response');

        const data: SubmitResponseResponse = await res.json();

        setSession(data.session);
        setCurrentCard(data.card);
    } catch (err) {
        setError('Failed to submit response. Please try again.');
    } finally {
        setIsLoading(false);
    }
};

// Continue to next drill
const continueToNext = async () => {
    if (!session) return;

    setIsLoading(true);
    setError(null);

    try {
        const res = await fetch(`/api/training/continue/${session.id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        });

        if (!res.ok) throw new Error('Failed to continue');

        const data: ContinueResponse = await res.json();

        if (data.complete) {
            setSessionStats(data.stats!);
            setShowCompletionDialog(true);
        } else {
            setSession(data.session);
            setCurrentDrill(data.drill!);
            setCurrentCard(data.card!);
            setProgress(data.progress!);
        }
    } catch (err) {
        setError('Failed to continue. Please try again.');
    } finally {
        setIsLoading(false);
    }
};
```

---

## Card Renderer

```typescript
function CardRenderer({
    card,
    drill,
    phase,
    onSubmit,
    onContinue,
    isLoading,
}: {
    card: Card;
    drill: Drill;
    phase: Session['phase'];
    onSubmit: (response: string) => void;
    onContinue: () => void;
    isLoading: boolean;
}) {
    if (card.type === 'scenario') {
        return (
            <ScenarioCard
                scenario={card.content}
                task={card.task}
                options={card.options}
                timerSeconds={drill.timer_seconds}
                inputType={drill.input_type}
                onSubmit={onSubmit}
                isLoading={isLoading}
            />
        );
    }

    if (card.type === 'feedback') {
        return (
            <FeedbackCard
                content={card.content}
                score={card.score}
                onContinue={onContinue}
                isLoading={isLoading}
            />
        );
    }

    return null;
}
```

---

## ScenarioCard Component

```typescript
// resources/js/components/training/cards/ScenarioCard.tsx

interface ScenarioCardProps {
    scenario: string;
    task: string;
    options?: string[];
    timerSeconds: number | null;
    inputType: 'text' | 'multiple_choice';
    onSubmit: (response: string) => void;
    isLoading: boolean;
}

export function ScenarioCard({
    scenario,
    task,
    options,
    timerSeconds,
    inputType,
    onSubmit,
    isLoading,
}: ScenarioCardProps) {
    const [response, setResponse] = useState('');
    const [selectedOption, setSelectedOption] = useState<number | null>(null);

    const handleSubmit = () => {
        if (inputType === 'multiple_choice') {
            if (selectedOption !== null) {
                onSubmit(String(selectedOption));
            }
        } else {
            if (response.trim()) {
                onSubmit(response);
            }
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Scenario</CardTitle>
                {timerSeconds && <TimerDisplay seconds={timerSeconds} />}
            </CardHeader>

            <CardContent className="space-y-4">
                <div className="prose prose-sm">
                    <p>{scenario}</p>
                </div>

                <div className="bg-muted p-4 rounded-lg">
                    <p className="font-medium text-sm text-muted-foreground mb-1">
                        Your Task
                    </p>
                    <p>{task}</p>
                </div>

                {inputType === 'multiple_choice' && options ? (
                    <div className="space-y-2">
                        {options.map((option, index) => (
                            <Button
                                key={index}
                                variant={selectedOption === index ? 'default' : 'outline'}
                                className="w-full justify-start"
                                onClick={() => setSelectedOption(index)}
                                disabled={isLoading}
                            >
                                <span className="mr-2 font-mono">
                                    {String.fromCharCode(65 + index)}.
                                </span>
                                {option}
                            </Button>
                        ))}
                    </div>
                ) : (
                    <Textarea
                        value={response}
                        onChange={(e) => setResponse(e.target.value)}
                        placeholder="Type your response..."
                        rows={6}
                        disabled={isLoading}
                    />
                )}
            </CardContent>

            <CardFooter>
                <Button
                    onClick={handleSubmit}
                    disabled={isLoading || (inputType === 'text' ? !response.trim() : selectedOption === null)}
                    className="w-full"
                >
                    {isLoading ? 'Submitting...' : 'Submit'}
                </Button>
            </CardFooter>
        </Card>
    );
}
```

---

## FeedbackCard Component

```typescript
// resources/js/components/training/cards/FeedbackCard.tsx

interface FeedbackCardProps {
    content: string;
    score: number;
    onContinue: () => void;
    isLoading: boolean;
}

export function FeedbackCard({
    content,
    score,
    onContinue,
    isLoading,
}: FeedbackCardProps) {
    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <CardTitle>Feedback</CardTitle>
                    <Badge variant={score >= 80 ? 'default' : score >= 60 ? 'secondary' : 'destructive'}>
                        Score: {score}
                    </Badge>
                </div>
            </CardHeader>

            <CardContent>
                <div className="prose prose-sm">
                    <p>{content}</p>
                </div>
            </CardContent>

            <CardFooter>
                <Button onClick={onContinue} disabled={isLoading} className="w-full">
                    {isLoading ? 'Loading...' : 'Continue'}
                </Button>
            </CardFooter>
        </Card>
    );
}
```

---

## Session Complete Dialog

```typescript
// resources/js/components/training/SessionCompleteDialog.tsx

interface SessionCompleteDialogProps {
    isOpen: boolean;
    stats: SessionStats;
    onRunItBack: () => void;
    onImOut: () => void;
}

export function SessionCompleteDialog({
    isOpen,
    stats,
    onRunItBack,
    onImOut,
}: SessionCompleteDialogProps) {
    const minutes = Math.round(stats.total_duration_seconds / 60);

    return (
        <Dialog open={isOpen}>
            <DialogContent className="text-center">
                <DialogHeader>
                    <DialogTitle>Nice work. You're done.</DialogTitle>
                </DialogHeader>

                <p className="text-muted-foreground">
                    You completed {stats.drills_completed} drills in {minutes} minutes.
                </p>

                <div className="flex gap-4 justify-center mt-6">
                    <Button variant="outline" onClick={onRunItBack}>
                        Run it back
                    </Button>
                    <Button onClick={onImOut}>
                        I'm out
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
```

### Button Handlers

```typescript
const handleRunItBack = async () => {
    setShowCompletionDialog(false);
    setSession(null);
    setCurrentCard(null);
    setCurrentDrill(null);
    await startSession();
};

const handleImOut = () => {
    router.push('/dashboard');
};
```

---

## Progress Display

```typescript
// In TrainingHeader
<div className="flex items-center gap-4">
    <div className="text-sm text-muted-foreground">
        Drill {progress.current} of {progress.total}
    </div>
    <Progress value={(progress.current / progress.total) * 100} className="w-32" />
</div>
```

---

## Error Handling

```typescript
{error && (
    <Alert variant="destructive" className="mb-4">
        <AlertDescription className="flex items-center justify-between">
            {error}
            <Button variant="outline" size="sm" onClick={handleRetry}>
                Retry
            </Button>
        </AlertDescription>
    </Alert>
)}
```

```typescript
const handleRetry = () => {
    setError(null);
    if (session?.phase === 'scenario' || session?.phase === 'responding') {
        // Retry scenario generation
        startSession();
    } else if (session?.phase === 'feedback') {
        // Retry continue
        continueToNext();
    }
};
```

---

## Files to Create

- `resources/js/components/training/SessionCompleteDialog.tsx`
- `resources/js/components/training/cards/FeedbackCard.tsx`

## Files to Modify

- `resources/js/types/training.ts` - new types
- `resources/js/pages/practice-modes/[slug]/train.tsx` - simplified state, new flow
- `resources/js/components/training/cards/ScenarioCard.tsx` - add task, options
- `resources/js/components/training/TrainingHeader.tsx` - drill progress
