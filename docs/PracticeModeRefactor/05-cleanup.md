# Phase 5: Cleanup

## Tasks

1. Remove old AI methods
2. Remove deprecated session columns
3. Remove unused components
4. Remove old card types

---

## Backend Cleanup

### PracticeAIService

Remove old methods that are no longer used:

```php
// REMOVE these methods:
- getFirstResponse()
- getResponse()
- buildMessageHistory()
- reconstructToolMessages()
- normalizeCard()
- Any tool_use/tool_result handling
```

### TrainingSessionService

Remove old flow methods:

```php
// REMOVE these methods:
- continueSession() (old version)
- recordExchange()
- checkLevelUp() (moved to listener)
- Any iteration/reiteration logic
```

### Old Routes

```php
// REMOVE these routes:
Route::post('/training/continue/{session}', ...);  // old continue endpoint
// (if different from new one)
```

---

## Database Cleanup

### Migration: Remove Deprecated Columns

```php
Schema::table('training_sessions', function (Blueprint $table) {
    // Remove old columns no longer needed
    $table->dropColumn([
        'exchange_count',
        // any other deprecated columns
    ]);
});
```

### Session Messages Table

Consider whether `session_messages` table is still needed:

- If keeping for audit/history: leave it
- If not needed: drop table

```php
// If removing:
Schema::dropIfExists('session_messages');
```

---

## Frontend Cleanup

### Remove Unused State Variables

```typescript
// REMOVE from train.tsx:
const [lastScenarioContext, setLastScenarioContext] = useState<string | null>(null);
const [isFetchingFollowUp, setIsFetchingFollowUp] = useState(false);
const [messages, setMessages] = useState<Message[]>([]);
const [levelUpCard, setLevelUpCard] = useState<LevelUpCard | null>(null);
```

### Remove Auto-Follow-Up useEffect

```typescript
// DELETE this entire useEffect:
useEffect(() => {
    if (currentCard?.type === 'scenario' && !isFetchingFollowUp && session && !isStarting) {
        fetchFollowUpCard(currentCard.content, currentCard);
    }
}, [currentCard, session, isStarting]);
```

### Remove Consolidation Logic

```typescript
// DELETE all scenario context consolidation code:
const consolidatableTypes = ['prompt', 'reflection'];
if (data.card && consolidatableTypes.includes(data.card.type)) {
    const consolidatedCard = {
        ...data.card,
        scenarioContext: scenarioContent,
    };
    // ...
}
```

### Remove Old Card Types

From `training.ts`:

```typescript
// REMOVE these types:
interface PromptCard { ... }
interface ReflectionCard { ... }
interface ChoiceCard { ... }  // if replaced by MC in ScenarioCard
interface InsightCard { ... }
interface LevelUpCard { ... }

// REMOVE from Card union:
type Card = ScenarioCard | PromptCard | ReflectionCard | ChoiceCard | InsightCard;
// REPLACE with:
type Card = ScenarioCard | FeedbackCard;
```

### Remove Unused Components

```
// DELETE these files:
resources/js/components/training/cards/PromptCard.tsx
resources/js/components/training/cards/ReflectionCard.tsx
resources/js/components/training/cards/ChoiceCard.tsx
resources/js/components/training/cards/InsightCard.tsx
resources/js/components/training/LevelUpRenderer.tsx
resources/js/components/training/TrainingHistory.tsx  // if not needed
```

### Remove Iteration Logic

```typescript
// REMOVE from PromptCard (before deleting):
interface PromptCard extends BaseCard {
    type: 'prompt';
    scenarioContext?: string;
    is_iteration?: boolean;  // DELETE
}

// REMOVE iteration UI:
{is_iteration && (
    <div className="...">
        Second attempt. Tighten and strengthen...
    </div>
)}
```

---

## Old API Response Handling

### Remove Tool-Based Response Parsing

```typescript
// DELETE any code handling:
- tool_use responses
- tool_result formatting
- Card type normalization from AI responses
```

### Remove Complex Card Rendering

```typescript
// SIMPLIFY CardRenderer from:
switch (card.type) {
    case 'scenario': return <ScenarioCard />;
    case 'prompt': return <PromptCard />;
    case 'reflection': return <ReflectionCard />;
    case 'multiple_choice': return <ChoiceCard />;
    case 'insight': return <InsightCard />;
}

// TO:
if (card.type === 'scenario') return <ScenarioCard />;
if (card.type === 'feedback') return <FeedbackCard />;
```

---

## Verification Checklist

Before completing cleanup:

- [ ] All tests pass with new flow
- [ ] Old endpoints return 404 or are removed
- [ ] No console errors from missing components
- [ ] Session start → complete flow works end-to-end
- [ ] Level up logic works via event listener
- [ ] Analytics events are being recorded
- [ ] Resume on refresh works correctly

---

## Files to Delete

### Frontend
- `resources/js/components/training/cards/PromptCard.tsx`
- `resources/js/components/training/cards/ReflectionCard.tsx`
- `resources/js/components/training/cards/ChoiceCard.tsx`
- `resources/js/components/training/cards/InsightCard.tsx`
- `resources/js/components/training/LevelUpRenderer.tsx`

### Backend
- Any old service methods (inline cleanup)
- Old job classes for scoring if not used

---

## Migration Notes

### Backwards Compatibility Period

During migration, you may need to support both flows:

```php
// In controller
public function continue(TrainingSession $session)
{
    if ($session->drill_index !== null) {
        // New flow
        return $this->newContinue($session);
    } else {
        // Legacy flow (temporary)
        return $this->legacyContinue($session);
    }
}
```

Once all active sessions are complete, remove legacy code.

### Data Migration

If needed, migrate existing sessions:

```php
// One-time migration script
TrainingSession::whereNull('drill_index')
    ->where('status', 'active')
    ->update(['status' => 'abandoned']);
```

Or let users start fresh with new architecture.
