# Authorization

All permission logic is centralized in Gates and Policies. No permission checks in views or scattered through controllers.

## Plan Configuration

Defined in `config/plans.php`:
```php
return [
    'free' => [
        'daily_exchanges' => 15,
        'max_level' => 2,
    ],
    'pro' => [
        'daily_exchanges' => 40,
        'max_level' => 4,
    ],
    'unlimited' => [
        'daily_exchanges' => 100,
        'max_level' => 5,
    ],
];
```

## User Model Helper
```php
// app/Models/User.php

public function planConfig(): array
{
    return config("plans.{$this->plan}", config('plans.free'));
}
```

---

## Gates

Register in `AuthServiceProvider` or a dedicated `GateServiceProvider`.

### can-train

User has exchanges remaining today.
```php
Gate::define('can-train', function (User $user) {
    $used = DailyUsage::forUserToday($user);
    $limit = $user->planConfig()['daily_exchanges'];
    return $used < $limit;
});
```

### can-train-mode

User can train AND their current level in the mode doesn't exceed plan's max level. Handles downgrade case where user is Level 4 but now on Free plan.
```php
Gate::define('can-train-mode', function (User $user, PracticeMode $mode) {
    if (!Gate::allows('can-train')) {
        return false;
    }
    
    $progress = UserModeProgress::where('user_id', $user->id)
        ->where('practice_mode_id', $mode->id)
        ->first();
    
    $currentLevel = $progress?->current_level ?? 1;
    $maxLevel = $user->planConfig()['max_level'];
    
    return $currentLevel <= $maxLevel;
});
```

### can-level-up

User's current level is below plan's max level.
```php
Gate::define('can-level-up', function (User $user, int $currentLevel) {
    return $currentLevel < $user->planConfig()['max_level'];
});
```

### access-level

Parameterized check: can user access level N?
```php
Gate::define('access-level', function (User $user, int $level) {
    return $user->planConfig()['max_level'] >= $level;
});
```

### admin

User has admin privileges.
```php
Gate::define('admin', function (User $user) {
    return $user->is_admin === true;
});
```

---

## Policies

### PracticeModePolicy
```php
// app/Policies/PracticeModePolicy.php

class PracticeModePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Anyone can browse modes
    }
    
    public function view(User $user, PracticeMode $mode): bool
    {
        return $mode->is_active || $user->is_admin;
    }
    
    public function start(User $user, PracticeMode $mode): bool
    {
        // Mode must be active
        if (!$mode->is_active) {
            return false;
        }
        
        // Check plan requirement
        if (!$this->meetsRequiredPlan($user, $mode)) {
            return false;
        }
        
        // Check can-train-mode gate
        return Gate::allows('can-train-mode', $mode);
    }
    
    public function create(User $user): bool
    {
        return $user->is_admin;
    }
    
    public function update(User $user, PracticeMode $mode): bool
    {
        return $user->is_admin;
    }
    
    public function delete(User $user, PracticeMode $mode): bool
    {
        return $user->is_admin;
    }
    
    private function meetsRequiredPlan(User $user, PracticeMode $mode): bool
    {
        if ($mode->required_plan === null) {
            return true; // Available to all
        }
        
        $planHierarchy = ['free' => 0, 'pro' => 1, 'unlimited' => 2];
        $userLevel = $planHierarchy[$user->plan] ?? 0;
        $requiredLevel = $planHierarchy[$mode->required_plan] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
}
```

### TrainingSessionPolicy
```php
// app/Policies/TrainingSessionPolicy.php

class TrainingSessionPolicy
{
    public function view(User $user, TrainingSession $session): bool
    {
        return $user->id === $session->user_id;
    }
    
    public function continue(User $user, TrainingSession $session): bool
    {
        if ($user->id !== $session->user_id) {
            return false;
        }
        
        if ($session->ended_at !== null) {
            return false;
        }
        
        return Gate::allows('can-train-mode', $session->practiceMode);
    }
    
    public function resume(User $user, TrainingSession $session): bool
    {
        return $this->continue($user, $session);
    }
    
    public function end(User $user, TrainingSession $session): bool
    {
        return $user->id === $session->user_id && $session->ended_at === null;
    }
}
```

### TagPolicy
```php
// app/Policies/TagPolicy.php

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
    
    public function create(User $user): bool
    {
        return $user->is_admin;
    }
    
    public function update(User $user, Tag $tag): bool
    {
        return $user->is_admin;
    }
    
    public function delete(User $user, Tag $tag): bool
    {
        return $user->is_admin;
    }
}
```

---

## Registering Policies
```php
// app/Providers/AuthServiceProvider.php

protected $policies = [
    PracticeMode::class => PracticeModePolicy::class,
    TrainingSession::class => TrainingSessionPolicy::class,
    Tag::class => TagPolicy::class,
];
```

---

## Usage in Controllers
```php
// Check gate
Gate::authorize('can-train');

// Or with response
if (Gate::denies('can-train')) {
    return response()->json(['error' => 'Daily limit reached'], 403);
}

// Check policy
$this->authorize('start', $practiceMode);
$this->authorize('continue', $trainingSession);

// Check with response
if ($user->cannot('start', $practiceMode)) {
    return redirect()->route('upgrade');
}
```

---

## Frontend Permission Sharing

Update `HandleInertiaRequests` middleware to share permissions with every response:
```php
// app/Http/Middleware/HandleInertiaRequests.php

public function share(Request $request): array
{
    $user = $request->user();
    
    if (!$user) {
        return parent::share($request);
    }
    
    $planConfig = $user->planConfig();
    $usedToday = DailyUsage::forUserToday($user);
    
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $user,
            'can' => [
                'train' => Gate::allows('can-train'),
                'admin' => Gate::allows('admin'),
            ],
            'limits' => [
                'daily_exchanges' => $planConfig['daily_exchanges'],
                'exchanges_used' => $usedToday,
                'exchanges_remaining' => max(0, $planConfig['daily_exchanges'] - $usedToday),
                'max_level' => $planConfig['max_level'],
            ],
            'plan' => $user->plan,
        ],
    ]);
}
```

---

## Vue Usage
```vue
<template>
  <!-- Check if user can train -->
  <UpgradePrompt v-if="!$page.props.auth.can.train" />
  <TrainingCard v-else />
  
  <!-- Show remaining exchanges -->
  <span>{{ $page.props.auth.limits.exchanges_remaining }} exchanges left today</span>
  
  <!-- Admin-only content -->
  <AdminPanel v-if="$page.props.auth.can.admin" />
  
  <!-- Check level access -->
  <span v-if="$page.props.auth.limits.max_level < 3">
    Upgrade to unlock Level 3+
  </span>
</template>
```

---

## Mode-Specific Permissions

For permissions that depend on a specific mode (like `can-train-mode`), pass with the mode data rather than globally:
```php
// In controller
$mode = PracticeMode::find($id);

return Inertia::render('PracticeModes/Show', [
    'mode' => $mode,
    'canStart' => $request->user()->can('start', $mode),
    'userProgress' => UserModeProgress::where('user_id', $request->user()->id)
        ->where('practice_mode_id', $mode->id)
        ->first(),
]);
```

---

## Edge Cases Handled

| Scenario | Gate/Policy | Result |
|----------|-------------|--------|
| Free user, hit 15 exchanges | can-train | Denied |
| Pro user, Level 4, tries to level up | can-level-up | Denied |
| User downgrades to Free while at Level 4 | can-train-mode | Denied until upgrade |
| Free user tries premium mode | PracticeModePolicy@start | Denied |
| User tries to view others' session | TrainingSessionPolicy@view | Denied |
| Admin views inactive mode | PracticeModePolicy@view | Allowed |