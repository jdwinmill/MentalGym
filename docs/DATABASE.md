# Database Schema

## Tables

### practice_modes

The core content table. Each record is a training methodology.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK, auto-increment |
| name | varchar(255) | Display name |
| slug | varchar(255) | URL-safe identifier, unique |
| tagline | varchar(255) | Short description for cards |
| description | text | Longer explanation |
| instruction_set | text | The AI system prompt (hidden from users) |
| config | json | Session settings (see below) |
| required_plan | varchar(20) nullable | null=all, 'pro', or 'unlimited' |
| is_active | boolean | Default false. Whether mode is visible/usable |
| display_order | int | Default 0. Sort order on listing page |
| created_at | timestamp | |
| updated_at | timestamp | |

**Config JSON structure:**
```json
{
  "input_character_limit": 500,
  "reflection_character_limit": 200,
  "max_response_tokens": 800,
  "max_history_exchanges": 10,
  "model": "claude-sonnet-4-20250514"
}
```

**Indexes:**
- `slug` - unique

---

### tags

Categorization for Practice Modes.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK, auto-increment |
| name | varchar(100) | Display name |
| slug | varchar(100) | Unique |
| category | enum | 'skill', 'context', 'duration', 'role' |
| display_order | int | Default 0. Sort within category |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- `slug` - unique
- `category` - for grouped queries

---

### practice_mode_tag (pivot)

Many-to-many relationship between modes and tags.

| Column | Type | Notes |
|--------|------|-------|
| practice_mode_id | bigint unsigned | FK → practice_modes.id, ON DELETE CASCADE |
| tag_id | bigint unsigned | FK → tags.id, ON DELETE CASCADE |

**Indexes:**
- Primary key on (practice_mode_id, tag_id)

---

### user_mode_progress

Tracks user's level and progress within each Practice Mode.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK, auto-increment |
| user_id | bigint unsigned | FK → users.id, ON DELETE CASCADE |
| practice_mode_id | bigint unsigned | FK → practice_modes.id, ON DELETE CASCADE |
| current_level | int | 1-5, default 1 |
| total_exchanges | int | Default 0. Cumulative all-time in this mode |
| exchanges_at_current_level | int | Default 0. Resets on level-up |
| last_trained_at | timestamp nullable | Last activity in this mode |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- Unique on (user_id, practice_mode_id)
- `user_id` - for user's progress list

**Level-up thresholds (exchanges_at_current_level needed):**
- Level 1 → 2: 10 exchanges
- Level 2 → 3: 15 exchanges
- Level 3 → 4: 20 exchanges
- Level 4 → 5: 30 exchanges

---

### training_sessions

Individual training sessions. Can be resumed if not ended.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK, auto-increment |
| user_id | bigint unsigned | FK → users.id, ON DELETE CASCADE |
| practice_mode_id | bigint unsigned | FK → practice_modes.id, ON DELETE CASCADE |
| level_at_start | int | Default 1. Snapshot of level when session started |
| exchange_count | int | Default 0. Exchanges in this session |
| started_at | timestamp | When session began |
| ended_at | timestamp nullable | null = active/resumable |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- (user_id, practice_mode_id, ended_at) - for finding active sessions
- `user_id` - for session history

---

### session_messages

Message history within a training session.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK, auto-increment |
| training_session_id | bigint unsigned | FK → training_sessions.id, ON DELETE CASCADE |
| role | enum | 'assistant', 'user' |
| content | text | Raw content. JSON for assistant, plain text or JSON for user |
| parsed_type | varchar(50) nullable | Card type for assistant messages (scenario, prompt, etc.) |
| created_at | timestamp | |

**Indexes:**
- `training_session_id` - for loading session history

**Note:** User responses for multiple choice stored as JSON: `{"selected": "b"}`

---

### daily_usage

Tracks exchanges per user per day for limit enforcement.

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK, auto-increment |
| user_id | bigint unsigned | FK → users.id, ON DELETE CASCADE |
| date | date | The calendar date |
| exchange_count | int | Default 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- Unique on (user_id, date)

---

### user_streaks

Global streak tracking (not per-mode).

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK, auto-increment |
| user_id | bigint unsigned | FK → users.id, ON DELETE CASCADE, unique |
| current_streak | int | Default 0. Consecutive days |
| longest_streak | int | Default 0. All-time record |
| last_training_date | date nullable | Last day user trained |
| created_at | timestamp | |
| updated_at | timestamp | |

**Indexes:**
- `user_id` - unique

**Streak logic:**
- If last_training_date = yesterday → increment current_streak
- If last_training_date = today → no change
- Otherwise → reset current_streak to 1

---

### users (updates to existing table)

Add these columns to existing users table.

| Column | Type | Notes |
|--------|------|-------|
| plan | varchar(20) | Default 'free'. Values: 'free', 'pro', 'unlimited' |
| is_admin | boolean | Default false |

---

## Relationships
```
User
├── hasMany TrainingSessions
├── hasMany UserModeProgress
├── hasMany DailyUsage
└── hasOne UserStreak

PracticeMode
├── hasMany TrainingSessions
├── hasMany UserModeProgress
└── belongsToMany Tags (pivot: practice_mode_tag)

TrainingSession
├── belongsTo User
├── belongsTo PracticeMode
└── hasMany SessionMessages

SessionMessage
└── belongsTo TrainingSession

UserModeProgress
├── belongsTo User
└── belongsTo PracticeMode

Tag
└── belongsToMany PracticeModes (pivot: practice_mode_tag)

DailyUsage
└── belongsTo User

UserStreak
└── belongsTo User
```

---

## Migration Order

1. `create_practice_modes_table`
2. `create_tags_table`
3. `create_practice_mode_tag_table`
4. `create_user_mode_progress_table`
5. `create_training_sessions_table`
6. `create_session_messages_table`
7. `create_daily_usage_table`
8. `create_user_streaks_table`
9. `add_plan_and_admin_to_users_table`

---

## Seeders

### TagSeeder
```php
$tags = [
    // Skills
    ['name' => 'Decision-Making', 'slug' => 'decision-making', 'category' => 'skill'],
    ['name' => 'Communication', 'slug' => 'communication', 'category' => 'skill'],
    ['name' => 'Leadership', 'slug' => 'leadership', 'category' => 'skill'],
    ['name' => 'Negotiation', 'slug' => 'negotiation', 'category' => 'skill'],
    ['name' => 'Critical Thinking', 'slug' => 'critical-thinking', 'category' => 'skill'],
    ['name' => 'Emotional Intelligence', 'slug' => 'emotional-intelligence', 'category' => 'skill'],
    
    // Context
    ['name' => 'Meetings', 'slug' => 'meetings', 'category' => 'context'],
    ['name' => '1:1s', 'slug' => 'one-on-ones', 'category' => 'context'],
    ['name' => 'Presentations', 'slug' => 'presentations', 'category' => 'context'],
    ['name' => 'Written', 'slug' => 'written', 'category' => 'context'],
    ['name' => 'Conflict', 'slug' => 'conflict', 'category' => 'context'],
    ['name' => 'Hiring', 'slug' => 'hiring', 'category' => 'context'],
    
    // Duration
    ['name' => 'Quick (5 min)', 'slug' => 'quick', 'category' => 'duration'],
    ['name' => 'Standard (10-15 min)', 'slug' => 'standard', 'category' => 'duration'],
    ['name' => 'Deep (20+ min)', 'slug' => 'deep', 'category' => 'duration'],
    
    // Role
    ['name' => 'Individual Contributor', 'slug' => 'ic', 'category' => 'role'],
    ['name' => 'Manager', 'slug' => 'manager', 'category' => 'role'],
    ['name' => 'Executive', 'slug' => 'executive', 'category' => 'role'],
    ['name' => 'Founder', 'slug' => 'founder', 'category' => 'role'],
];
```