# AI Prompt: Generate SharpStack Track Content

Generate complete training content for a mental fitness track. The output must be valid JSON following the exact schema below.

## Track Specifications

- **Track Name:** [TRACK_NAME]
- **Track Slug:** [TRACK_SLUG]
- **Duration:** [WEEKS] weeks
- **Sessions per Week:** [SESSIONS_PER_WEEK]
- **Session Duration:** [MINUTES] minutes
- **Content Focus:** [DESCRIPTION]

### Skill Levels (Progressive Difficulty)

1. **[LEVEL_1_NAME]** (`[LEVEL_1_SLUG]`): [LEVEL_1_DESCRIPTION]
2. **[LEVEL_2_NAME]** (`[LEVEL_2_SLUG]`): [LEVEL_2_DESCRIPTION]
3. **[LEVEL_3_NAME]** (`[LEVEL_3_SLUG]`): [LEVEL_3_DESCRIPTION]
4. **[LEVEL_4_NAME]** (`[LEVEL_4_SLUG]`): [LEVEL_4_DESCRIPTION]

### Lessons per Level

Generate [LESSONS_PER_LEVEL] lessons per skill level, numbered sequentially across the entire track.

---

## JSON Schema

```json
{
  "track": {
    "slug": "string (lowercase, hyphens only)",
    "name": "string",
    "description": "string (2-3 sentences describing the track)",
    "pitch": "string (compelling 2-3 sentence hook that creates urgency)",
    "duration_weeks": "integer",
    "sessions_per_week": "integer",
    "session_duration_minutes": "integer",
    "is_active": true,
    "sort_order": "integer"
  },
  "skill_levels": [
    {
      "slug": "string (lowercase, hyphens only)",
      "name": "string",
      "description": "string (1-2 sentences)",
      "level_number": "integer (1-4)",
      "pass_threshold": "decimal (0.00-1.00, typically 0.80)"
    }
  ],
  "lessons": [
    {
      "lesson_number": "integer (sequential across entire track)",
      "skill_level_slug": "string (must match a skill_levels.slug)",
      "title": "string",
      "learning_objectives": ["string", "string", "string"],
      "estimated_duration_minutes": "integer",
      "is_active": true,
      "content_blocks": [
        {
          "block_type": "principle_text | instruction_text | audio | video | image",
          "sort_order": "integer (unique within lesson)",
          "content": "object (see Content Block Types below)"
        }
      ],
      "questions": [
        {
          "skill_level_slug": "string (must match a skill_levels.slug)",
          "related_block_sort_order": "integer | null (references content_blocks.sort_order)",
          "question_text": "string",
          "question_type": "multiple_choice | true_false | open_ended",
          "correct_answer": "string",
          "explanation": "string (why this is correct)",
          "points": "integer (typically 1-3)",
          "sort_order": "integer (unique within lesson)",
          "answer_options": [
            {
              "option_text": "string",
              "is_correct": "boolean",
              "sort_order": "integer",
              "feedback": {
                "feedback_text": "string (personalized response)",
                "pattern_tag": "string (identifies the mistake/success pattern)",
                "severity": "correct | minor_miss | critical_miss"
              }
            }
          ]
        }
      ]
    }
  ]
}
```

---

## Content Block Types

### principle_text
Educational content explaining a concept or principle.
```json
{
  "heading": "string",
  "body": "string (multiple paragraphs, use \\n for line breaks)",
  "key_takeaways": ["string", "string", "string"]
}
```

### instruction_text
Directions for the upcoming exercise.
```json
{
  "heading": "string",
  "body": "string",
  "focus_points": ["string", "string", "string"]
}
```

### audio
Audio content with transcript for listening exercises.
```json
{
  "title": "string",
  "context": "string (sets the scene)",
  "transcript": "string (full dialogue with speaker labels)",
  "duration_seconds": "integer",
  "audio_url": "string | null",
  "allow_replay": "boolean",
  "max_replays": "integer",
  "show_transcript_after_questions": "boolean"
}
```

### video
Video content.
```json
{
  "title": "string",
  "context": "string",
  "video_url": "string | null",
  "thumbnail_url": "string | null",
  "duration_seconds": "integer",
  "transcript": "string | null"
}
```

### image
Image content for visual exercises.
```json
{
  "title": "string",
  "context": "string",
  "image_url": "string | null",
  "alt_text": "string",
  "caption": "string | null"
}
```

---

## Content Guidelines

### Lesson Structure
Each lesson should follow this pattern:
1. **Principle Block** - Teach the concept (what and why)
2. **Instruction Block** - Explain the exercise (what to focus on)
3. **Media Block** - The actual content to analyze (audio/video/image)
4. **Questions** - 3-5 questions testing the skill level

### Audio Transcript Guidelines
- Use realistic workplace scenarios
- Include speaker labels (e.g., "Sarah:", "Mike:")
- Keep conversations 60-120 seconds when read aloud
- Include natural speech patterns (but avoid excessive filler words)
- Embed specific facts, intentions, and emotional cues based on the skill level

### Question Design
- **Facts Level:** Test recall of explicit information (dates, names, numbers, commitments)
- **Intent Level:** Test understanding of underlying purposes and motivations
- **Emotion Level:** Test recognition of emotional states and concerns
- **Strategy Level:** Test ability to formulate appropriate responses

### Answer Option Guidelines
- Always include exactly one correct answer
- Include 3-4 total options
- Distractors should represent realistic mistakes:
  - **Surface-level interpretation** - Missing deeper meaning
  - **Confusion errors** - Mixing up details
  - **Assumption errors** - Adding information not present
  - **Negative attribution** - Assuming worst intentions

### Feedback Guidelines
- Feedback should be specific and educational
- Explain WHY the answer is right or wrong
- Identify the pattern of thinking that led to the answer
- Use severity levels:
  - `correct` - Got it right
  - `minor_miss` - Close but missed something
  - `critical_miss` - Fundamental misunderstanding

### Pattern Tags
Use consistent tags for tracking weakness patterns:
- `good_fact_recall`, `good_intent_recognition`, `good_emotion_detection`, `good_strategic_thinking`
- `date_confusion`, `name_confusion`, `number_distortion`
- `surface_level_interpretation`, `missed_subtext`
- `negative_attribution`, `assumed_hostility`
- `passive_interpretation`, `missed_agency`
- `fact_invention`, `assumption_injection`

---

## Quality Checklist

Before outputting:
- [ ] All slugs are lowercase with hyphens only
- [ ] All `skill_level_slug` references match defined skill levels
- [ ] All `related_block_sort_order` values match existing content block sort_orders
- [ ] Each question has exactly one `is_correct: true` answer option
- [ ] All sort_order values are unique within their scope
- [ ] Transcripts include realistic dialogue with speaker labels
- [ ] Feedback is specific and educational, not generic
- [ ] Pattern tags are consistent and meaningful
- [ ] JSON is valid with no trailing commas

---

## Example Prompt Usage

**Fill in the placeholders and send to AI:**

```
Generate complete training content for a SharpStack track.

Track Name: Active Listening
Track Slug: active-listening
Duration: 8 weeks
Sessions per Week: 5
Session Duration: 10 minutes
Content Focus: Professional listening skills in workplace conversations

Skill Levels:
1. Facts (facts): Capturing explicit information - names, dates, numbers, commitments
2. Intent (intent): Understanding why people say what they say
3. Emotion (emotion): Recognizing feelings and concerns beneath the words
4. Strategy (strategy): Formulating effective responses

Generate 10 lessons per skill level (40 total lessons).

Requirements:
- Each lesson needs 2-4 content blocks in logical teaching order
- Include principle_text, instruction_text, and audio blocks
- Each lesson needs 3-5 questions testing the associated skill level
- Each question needs 3-4 answer options with personalized feedback
- All content should be professionally relevant workplace scenarios
- Transcripts should be realistic conversations (meetings, 1:1s, calls)
- Feedback should identify specific listening patterns

Output only valid JSON with no additional commentary.
```

---

## Output Format

Output ONLY the JSON object, with no markdown code fences, no explanations, and no commentary. The output should start with `{` and end with `}`.
