# Subscription Plans

## Tier Definitions

| Plan | Price | Daily Exchanges | Max Level | Mode Access |
|------|-------|-----------------|-----------|-------------|
| Free | $0 | 15 | 2 | Unrestricted modes only |
| Pro | $24/mo | 40 | 4 | All modes except 'unlimited' tier |
| Unlimited | $49/mo | 100 | 5 | All modes |

## Annual Pricing

| Plan | Monthly | Annual | Monthly Equivalent | Savings |
|------|---------|--------|-------------------|---------|
| Pro | $24 | $199 | $16.58 | 31% |
| Unlimited | $49 | $399 | $33.25 | 32% |

---

## Level System

### Level Progression Thresholds

Exchanges required at current level to advance:

| Transition | Exchanges Needed | Cumulative Total |
|------------|------------------|------------------|
| Level 1 → 2 | 10 | 10 |
| Level 2 → 3 | 15 | 25 |
| Level 3 → 4 | 20 | 45 |
| Level 4 → 5 | 30 | 75 |

### Level Difficulty

| Level | Scenario Complexity | What's Tested |
|-------|---------------------|---------------|
| 1 | Clear-cut decisions | Basic pattern recognition, articulation |
| 2 | Competing priorities | Trade-offs, political awareness |
| 3 | Ambiguous situations | Framework application, multiple valid paths |
| 4 | High-stakes edge cases | Conviction, synthesis, incomplete information |
| 5 | Values conflicts | Deep trade-offs, personal philosophy |

### Level Constants (for code)
```php
// In a service or config

const LEVEL_THRESHOLDS = [
    1 => 10,   // Need 10 exchanges at Level 1 to reach Level 2
    2 => 15,   // Need 15 exchanges at Level 2 to reach Level 3
    3 => 20,   // Need 20 exchanges at Level 3 to reach Level 4
    4 => 30,   // Need 30 exchanges at Level 4 to reach Level 5
    5 => null, // Max level
];

const MAX_LEVEL = 5;
```

---

## Upgrade Triggers

### Free → Pro

User experiences one of:
- Hits daily exchange limit (15) and wants more
- Reaches Level 2 cap and sees "Unlock Level 3+"
- Tries to access a mode with `required_plan = 'pro'`

### Pro → Unlimited

User experiences one of:
- Hits daily exchange limit (40) regularly
- Reaches Level 4 cap and wants Level 5
- Tries to access a mode with `required_plan = 'unlimited'`

---

## Downgrade Handling

When a user downgrades (e.g., Pro → Free):

| What Happens | Behavior |
|--------------|----------|
| Progress preserved | Level stays at 4 even on Free plan |
| Can't train above cap | Can't train modes where current level > plan max |
| Can train within cap | Can train modes where current level ≤ plan max |
| Must upgrade to continue | Shows "Upgrade to continue training" message |

**Example:**
- User is Level 4 in MBA+ on Pro plan
- User downgrades to Free (max Level 2)
- User cannot train MBA+ until they upgrade
- User can still train a different mode where they're Level 1

---

## Cost Economics

### Per-Exchange Cost

| Component | Tokens | Rate | Cost |
|-----------|--------|------|------|
| Instruction set (cached) | 2,000 | $0.30/1M | $0.0006 |
| History (avg) | 2,500 | $3.00/1M | $0.0075 |
| User input | 150 | $3.00/1M | $0.0005 |
| Output | 350 | $15.00/1M | $0.0053 |
| **Total** | | | **~$0.015** |

### Cost Per User Type

| User Type | Exchanges/Day | Cost/Day | Days/Month | Monthly Cost |
|-----------|---------------|----------|------------|--------------|
| Free (casual) | 8 | $0.12 | 10 | $1.20 |
| Free (maxed) | 15 | $0.22 | 20 | $4.40 |
| Pro (average) | 25 | $0.38 | 20 | $7.60 |
| Pro (heavy) | 40 | $0.60 | 25 | $15.00 |
| Unlimited (power) | 100 | $1.50 | 20 | $30.00 |

### Margin Analysis

| Tier | Revenue | Max Cost (heavy use) | Min Margin |
|------|---------|----------------------|------------|
| Free | $0 | $6.75 | -$6.75 (acquisition cost) |
| Pro | $24 | $18.00 | +$6.00 |
| Unlimited | $49 | $45.00 | +$4.00 |

**All paid tiers are margin-positive even at maximum usage.**

---

## Mode Access Control

### required_plan Field

| Value | Who Can Access |
|-------|----------------|
| null | Everyone (Free, Pro, Unlimited) |
| 'pro' | Pro and Unlimited only |
| 'unlimited' | Unlimited only |

### Checking Access
```php
// In PracticeModePolicy

private function meetsRequiredPlan(User $user, PracticeMode $mode): bool
{
    if ($mode->required_plan === null) {
        return true;
    }
    
    $hierarchy = ['free' => 0, 'pro' => 1, 'unlimited' => 2];
    return $hierarchy[$user->plan] >= $hierarchy[$mode->required_plan];
}
```

---

## Config File
```php
// config/plans.php

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

---

## Stripe Integration Notes

Existing Stripe subscription system should map subscription status to `users.plan`:

| Stripe Product | users.plan |
|----------------|------------|
| No subscription | 'free' |
| Pro monthly/annual | 'pro' |
| Unlimited monthly/annual | 'unlimited' |

Update `users.plan` in webhook handler when subscription changes.