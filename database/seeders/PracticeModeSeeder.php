<?php

namespace Database\Seeders;

use App\Models\Drill;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use Illuminate\Database\Seeder;

class PracticeModeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDifficultConversationsMode();
    }

    private function seedDifficultConversationsMode(): void
    {
        // Create the practice mode
        $mode = PracticeMode::updateOrCreate(
            ['slug' => 'difficult-conversations'],
            [
                'name' => 'Difficult Conversations',
                'tagline' => 'Navigate high-stakes discussions with confidence',
                'description' => 'Practice handling uncomfortable workplace conversations—giving critical feedback, addressing conflict, setting boundaries, and delivering bad news. Build the muscle memory to stay composed when it matters most.',
                'instruction_set' => $this->getInstructionSet(),
                'config' => [
                    'input_character_limit' => 600,
                    'reflection_character_limit' => 250,
                    'max_response_tokens' => 900,
                    'max_history_exchanges' => 8,
                    'model' => 'claude-sonnet-4-20250514',
                ],
                'required_plan' => 'free',
                'icon' => 'MessageSquareWarning',
                'is_active' => true,
                'sort_order' => 10,
            ]
        );

        // Add required context fields
        $this->seedRequiredContext($mode);

        // Create drills with dimensions and insights
        $this->seedDrills($mode);
    }

    private function getInstructionSet(): string
    {
        return <<<'INSTRUCTION'
You are a communication coach specializing in difficult workplace conversations. Your role is to help users practice navigating high-stakes interpersonal situations with clarity, empathy, and confidence.

## User Context
The user is a {{career_level}} {{job_title}}{{manages_people}}. They work in a {{team_composition}} environment and frequently interact with {{cross_functional_teams}}.

## Difficulty Level
Current level: {{level}}

Level scaling:
- Levels 1-2: Straightforward situations, cooperative other party, low stakes
- Levels 3-4: Added complexity, mild resistance, moderate stakes
- Levels 5-6: Challenging dynamics, defensive reactions, higher stakes
- Levels 7-8: Multiple competing interests, strong emotions, significant consequences
- Levels 9-10: Crisis-level difficulty, hostile parties, career-affecting stakes

## Coaching Approach
1. Present realistic scenarios that match the user's professional context
2. Evaluate responses for clarity, empathy, directness, and professionalism
3. Provide specific, actionable feedback that quotes the user's actual words
4. Challenge hedging, passive language, and conflict avoidance
5. Reward responses that balance assertiveness with emotional intelligence

## Key Principles
- Direct doesn't mean harsh; clarity is kindness
- Acknowledge emotions without being derailed by them
- Own your message—avoid "they made me" or "I was told to"
- Separate the person from the behavior
- Prepare for defensiveness without provoking it
INSTRUCTION;
    }

    private function seedRequiredContext(PracticeMode $mode): void
    {
        // Clear existing and add fresh
        $mode->requiredContext()->delete();

        $contextFields = [
            'career_level',
            'job_title',
            'manages_people',
            'team_composition',
            'cross_functional_teams',
        ];

        foreach ($contextFields as $field) {
            PracticeModeRequiredContext::create([
                'practice_mode_id' => $mode->id,
                'profile_field' => $field,
            ]);
        }
    }

    private function seedDrills(PracticeMode $mode): void
    {
        $drills = [
            [
                'name' => 'Delivering Critical Feedback',
                'position' => 0,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['assertiveness', 'diplomatic_framing', 'clarity', 'perspective_taking'],
                'primary_insight_slug' => 'compression-find-the-buried-lead',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a critical feedback delivery scenario appropriate for the user's level and context.

Create a situation where the user must deliver constructive but critical feedback to someone. Include:
- A specific performance issue or behavior that needs addressing
- Context about the relationship (direct report, peer, cross-functional partner)
- Recent relevant history that makes this conversation necessary
- Stakes if the issue isn't addressed

At higher levels, add:
- Defensive personality traits in the recipient
- Political complications (the person is well-liked, connected, or has tenure)
- Time pressure or public visibility
- Previous failed attempts to address the issue

Format your response as JSON:
{
    "scenario": "[Setup: Who you're talking to and your relationship]\n\n[The specific situation requiring feedback]\n\n[Why this conversation needs to happen now]",
    "task": "Deliver the feedback directly. State the issue, its impact, and what needs to change. 4-6 sentences. No softening preambles like 'I wanted to chat' or 'I've been meaning to mention.'"
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's feedback delivery.

Check for:
- Directness: Did they state the issue clearly in the first 2 sentences?
- Specificity: Did they cite specific behaviors/instances, not vague generalities?
- Impact: Did they explain why this matters (to team, project, them)?
- Forward-looking: Did they state what needs to change?
- Tone: Professional and respectful, not harsh or timid?

Red flags to call out:
- Softening openers ("I just wanted to...", "I hope you don't mind...")
- Hedging language ("kind of", "maybe", "sort of", "a little bit")
- Sandwich feedback (praise-criticism-praise dilutes the message)
- Passive voice hiding ownership ("It's been noticed that...")
- Making it about feelings vs. facts ("I feel like you...")

Quote their actual words when critiquing.
PROMPT,
            ],
            [
                'name' => 'Navigating Pushback',
                'position' => 1,
                'timer_seconds' => 75,
                'input_type' => 'text',
                'dimensions' => ['assertiveness', 'active_listening', 'emotional_regulation', 'cognitive_flexibility'],
                'primary_insight_slug' => 'control-the-room',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a pushback navigation scenario.

Create a continuation where the person has responded defensively to feedback or a request. Include:
- Their specific defensive response (denial, deflection, counter-attack, or excuse)
- Emotional undertones (hurt, angry, dismissive, or overwhelmed)
- A grain of truth in their pushback that could derail you if you're not careful

At higher levels, add:
- More sophisticated deflection tactics
- Attempts to make YOU the problem
- Bringing up unrelated grievances
- Emotional escalation or shutting down

Format your response as JSON:
{
    "scenario": "[Context reminder of the conversation]\n\nThey respond: '[Their defensive response with emotional undertones]'",
    "task": "Navigate their pushback without backing down or escalating. Acknowledge what's valid, hold your ground on what matters. 3-5 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate how the user navigated the pushback.

Check for:
- Acknowledgment: Did they validate what's valid in the pushback?
- Holding ground: Did they maintain their core position?
- De-escalation: Did they lower the temperature, not raise it?
- Redirection: Did they bring focus back to the issue?
- Avoid getting hooked: Did they sidestep attempts to derail?

Red flags:
- Caving entirely when pushed
- Getting defensive in return ("Well, YOU...")
- Ignoring their emotional state
- Repeating the same point louder
- Apologizing for having the conversation

Quote specific phrases that worked or didn't.
PROMPT,
            ],
            [
                'name' => 'Setting Boundaries',
                'position' => 2,
                'timer_seconds' => 60,
                'input_type' => 'text',
                'dimensions' => ['assertiveness', 'clarity', 'stress_management', 'self_confidence'],
                'primary_insight_slug' => 'decisions-need-owners',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a boundary-setting scenario.

Create a situation where the user needs to set or enforce a professional boundary. Include:
- An unreasonable request or ongoing pattern of overreach
- The relationship context (manager, peer, stakeholder, client)
- Why saying yes would be problematic (bandwidth, scope, precedent, fairness)
- Social pressure making it hard to say no

At higher levels, add:
- Higher-status requesters
- "Urgent" framing that creates false pressure
- Guilt tactics or appeals to loyalty
- History of the user accommodating this person

Format your response as JSON:
{
    "scenario": "[Who is making the request and your relationship]\n\n[The unreasonable request or pattern]\n\n[The pressure to say yes]",
    "task": "Set the boundary clearly. State what you can't do, why briefly, and what alternative exists (if any). 3-4 sentences. No over-explaining or excessive apologizing."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's boundary-setting.

Check for:
- Clarity: Is the boundary unambiguous?
- Brevity: Did they over-explain or justify excessively?
- Ownership: Did they own the no, or blame others/policy?
- Alternative: Did they offer what they CAN do (if appropriate)?
- Apology management: One brief "sorry" max, not repeated groveling

Red flags:
- Leaving wiggle room ("I don't think I can...")
- Over-explaining reasons (invites negotiation)
- Excessive apologizing (signals guilt, invites pressure)
- Blaming ("My manager won't let me" vs. "I can't")
- Leaving the door open when it should be closed

Quote their language when providing feedback.
PROMPT,
            ],
            [
                'name' => 'Delivering Bad News',
                'position' => 3,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['clarity', 'diplomatic_framing', 'perspective_taking', 'stress_management'],
                'primary_insight_slug' => 'cut-first-then-edit',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a bad news delivery scenario.

Create a situation where the user must deliver unwelcome news. Include:
- The specific bad news (project delay, budget cut, denied request, negative decision)
- Who is receiving it and their likely emotional investment
- Any factors that contributed to this outcome
- What happens next

At higher levels, add:
- News that will significantly impact the recipient
- Situations where the user had some role in the outcome
- Time-sensitive contexts requiring immediate delivery
- Recipients who have been promised differently before

Format your response as JSON:
{
    "scenario": "[Who you're delivering news to and context]\n\n[The bad news you need to deliver]\n\n[Relevant background and what comes next]",
    "task": "Deliver the bad news directly. Lead with the news, not a buildup. Explain briefly, take appropriate ownership, and clarify next steps. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's bad news delivery.

Check for:
- Lead with the news: Did they state the bad news in the first 1-2 sentences?
- No false buildup: Did they avoid "So, I wanted to talk about..." preambles?
- Brief explanation: Context without excessive justification?
- Appropriate ownership: Took responsibility where warranted?
- Next steps: Clear about what happens now?

Red flags:
- Burying the lead (bad news in sentence 4+)
- Excessive preamble building anxiety
- Blaming others or circumstances entirely
- No path forward offered
- Sugar-coating that creates confusion about severity

The standard: A clear, compassionate, direct delivery that respects the recipient's time and intelligence.
PROMPT,
            ],
        ];

        foreach ($drills as $drillData) {
            $dimensions = $drillData['dimensions'] ?? [];
            $primaryInsightSlug = $drillData['primary_insight_slug'] ?? null;
            unset($drillData['dimensions'], $drillData['primary_insight_slug']);

            $drill = Drill::updateOrCreate(
                [
                    'practice_mode_id' => $mode->id,
                    'name' => $drillData['name'],
                ],
                array_merge($drillData, [
                    'practice_mode_id' => $mode->id,
                    'dimensions' => $dimensions,
                ])
            );

            // Attach primary insight if specified
            if ($primaryInsightSlug) {
                $insight = Insight::where('slug', $primaryInsightSlug)->first();
                if ($insight) {
                    // Sync to avoid duplicates, with is_primary flag
                    $drill->insights()->syncWithoutDetaching([
                        $insight->id => ['is_primary' => true],
                    ]);
                }
            }
        }
    }
}
