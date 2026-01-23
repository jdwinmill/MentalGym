<?php

namespace Database\Seeders;

use App\Models\Drill;
use App\Models\PracticeMode;
use Illuminate\Database\Seeder;

class DrillSeeder extends Seeder
{
    public function run(): void
    {
        // MBA+ Decision Lab drills
        $mbaMode = PracticeMode::where('slug', 'mba-decision-lab')->first();
        if ($mbaMode) {
            $this->seedMbaDecisionLabDrills($mbaMode);
        }

        // On the Spot drills
        $onTheSpot = PracticeMode::where('slug', 'on-the-spot')->first();
        if ($onTheSpot) {
            $this->seedOnTheSpotDrills($onTheSpot);
        }
    }

    private function seedMbaDecisionLabDrills(PracticeMode $mode): void
    {
        $drills = [
            [
                'name' => 'Compression',
                'position' => 0,
                'timer_seconds' => 45,
                'input_type' => 'text',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a compression drill scenario.

Create a messy 60-100 word business statement that contains:
- Corporate jargon and buzzwords
- Hedging language ("might", "potentially", "it seems")
- A buried lead (the actual point is hidden in the middle)
- Unnecessary qualifiers and filler phrases

The statement should be from a realistic business context like:
- Project status update
- Strategy recommendation
- Resource request
- Risk assessment

Format your response as JSON:
{
    "scenario": "Here's a rambling statement. Read it. You'll extract the core point.\n\n[The messy statement here]",
    "task": "Compress to one clear sentence, 15 words max. What's the actual point?"
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's compression attempt.

Check for:
- Word count: Is it 15 words or under? Count exactly.
- Core message: Did they capture the buried lead?
- Clarity: Is it clear and direct?
- Filler: Did they remove all jargon and hedging?

Be specific about what worked or missed. If over 15 words, state the count.

Format your response as JSON:
{
    "feedback": "[Your specific critique of their compression attempt]",
    "score": 0-100
}

Score guide:
- 90-100: Under 15 words, captures core point, crystal clear
- 70-89: Slightly over OR misses a nuance, but mostly right
- 50-69: Gets the gist but too wordy or unclear
- 0-49: Misses the point or way too long
PROMPT,
            ],
            [
                'name' => 'Executive Communication',
                'position' => 1,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'scenario_instruction_set' => <<<'PROMPT'
Generate an executive communication drill scenario.

Create a situation where the user must respond to senior leadership. Include:
- A specific business context (board meeting, executive review, crisis)
- Stakes that matter (budget, headcount, timeline, reputation)
- An implicit challenge (defending a position, explaining a miss, proposing change)

Make the executive(s) feel real: give them names, titles, known preferences.

The user must explain or defend something under pressure.

Format your response as JSON:
{
    "scenario": "[Setup: You're facing senior leadership context]\n\n[The specific situation with named executives and stakes]",
    "task": "Respond to leadership. 3-5 declarative sentences. No hedging, no 'I think.'"
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's executive communication.

Check for:
- Authority: Do they sound like a peer or a supplicant?
- Clarity: Is the message clear in the first sentence?
- Hedging: Any "I think", "maybe", "potentially", "it seems"?
- Structure: 3-5 declarative sentences as requested?
- Signal: What does this response signal about their confidence?

Name the specific patterns you see in THEIR words. Quote them.

Format your response as JSON:
{
    "feedback": "[Your specific critique with quotes from their response]",
    "score": 0-100
}

Score guide:
- 90-100: Boardroom-ready, authoritative, clear, no hedging
- 70-89: Solid but one weakness (slight hedge, unclear opener, too long)
- 50-69: Message there but undermined by tone or structure
- 0-49: Hedging throughout, unclear, or doesn't address the situation
PROMPT,
            ],
            [
                'name' => 'Problem-Solving',
                'position' => 2,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a problem-solving drill scenario.

Create a business dilemma with:
- Incomplete information (they won't have all the data they want)
- Time pressure (decision needed soon)
- No obvious right answer (real tradeoffs)
- Real stakes (people, money, reputation affected)

Good dilemma types:
- Resource allocation with competing priorities
- Vendor/partner decision with imperfect options
- Crisis response with limited time
- Strategic pivot with uncertain outcomes

Make it specific with numbers, names, dates where relevant.

Format your response as JSON:
{
    "scenario": "Incomplete info, real stakes. You'll make a call.\n\n[The specific business dilemma]",
    "task": "Use this structure: Decision (1 sentence), Rationale (2-3 sentences), Risk (1 sentence), Mitigation (1 sentence). No 'it depends' without a choice."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's problem-solving response.

Check their structure:
- Decision: Did they make a clear choice? (Not "it depends" without resolution)
- Rationale: 2-3 sentences explaining why?
- Risk: Did they name the main risk of their choice?
- Mitigation: Did they address how to reduce that risk?

Evaluate the thinking:
- Is the rationale logical and tied to the scenario?
- Did they acknowledge tradeoffs?
- Is the mitigation realistic?

Be specific about what THEY wrote. Quote their words.

Format your response as JSON:
{
    "feedback": "[Your specific critique of their decision framework]",
    "score": 0-100
}

Score guide:
- 90-100: Clear decision, strong rationale, realistic risk/mitigation
- 70-89: Good decision but weak in one area (rationale thin, risk ignored)
- 50-69: Made a choice but reasoning is fuzzy or structure is off
- 0-49: No clear decision, or "it depends" without resolution
PROMPT,
            ],
            [
                'name' => 'Writing Precision',
                'position' => 3,
                'timer_seconds' => 75,
                'input_type' => 'text',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a writing precision drill scenario.

Create a 2-3 sentence passage that needs improvement. The passage should have:
- A clear improvement dimension (clarity, brevity, or impact)
- Obvious opportunities to cut or strengthen
- Real business content (not lorem ipsum)

Passage problems to include:
- For clarity: ambiguous pronouns, unclear antecedents, jargon
- For brevity: redundancy, filler phrases, unnecessary words
- For impact: passive voice, weak verbs, buried action

State which dimension they should optimize for.

Format your response as JSON:
{
    "scenario": "Here's flabby writing. You'll rewrite for [clarity/brevity/impact].\n\n[The 2-3 sentence passage needing improvement]",
    "task": "Rewrite for [clarity/brevity/impact]. Every word counts."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's rewrite.

Check for improvement on the target dimension:
- Clarity: Is it clearer? Ambiguity removed?
- Brevity: Is it shorter? How many words cut?
- Impact: Is it stronger? Better verbs? Active voice?

Also check:
- Did they preserve the meaning?
- Did they introduce new problems?
- Is their version actually better?

Compare their version to the original specifically.

Format your response as JSON:
{
    "feedback": "[Your specific comparison of their rewrite to the original]",
    "score": 0-100
}

Score guide:
- 90-100: Significantly improved on target dimension, no new problems
- 70-89: Improved but missed some opportunities or introduced minor issues
- 50-69: Marginal improvement or sideways move
- 0-49: No improvement, made it worse, or changed the meaning
PROMPT,
            ],
        ];

        foreach ($drills as $drill) {
            Drill::updateOrCreate(
                [
                    'practice_mode_id' => $mode->id,
                    'name' => $drill['name'],
                ],
                array_merge($drill, ['practice_mode_id' => $mode->id])
            );
        }
    }

    private function seedOnTheSpotDrills(PracticeMode $mode): void
    {
        $drills = [
            [
                'name' => 'Quick Response',
                'position' => 0,
                'timer_seconds' => 30,
                'input_type' => 'text',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a quick response drill scenario.

Create a sudden, unexpected question or challenge that might come up in a meeting:
- A direct question from a stakeholder
- A curveball objection
- A request for an opinion on the spot
- A challenge to justify a decision

Make it specific and realistic. Something that would catch someone off guard.

Format your response as JSON:
{
    "scenario": "You're in a meeting. Someone turns to you and says:\n\n[The unexpected question or challenge]",
    "task": "Respond in 2-3 sentences. Be direct. No stalling."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's quick response.

Check for:
- Directness: Did they answer or stall?
- Confidence: Do they sound sure or uncertain?
- Brevity: 2-3 sentences as requested?
- Substance: Is there actual content or just filler?

This is about thinking on your feet. Value directness over perfection.

Format your response as JSON:
{
    "feedback": "[Your critique of their quick response]",
    "score": 0-100
}

Score guide:
- 90-100: Direct, confident, substantive, right length
- 70-89: Answered but hedged or slightly too long
- 50-69: Got something out but stalled or unclear
- 0-49: Didn't really answer or way too long
PROMPT,
            ],
            [
                'name' => 'Defend Your Position',
                'position' => 1,
                'timer_seconds' => 60,
                'input_type' => 'text',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a position defense drill scenario.

Set up a situation where:
- The user has taken a position (stated or implied)
- Someone is pushing back or challenging it
- They need to defend without getting defensive

Good contexts:
- Budget defense
- Timeline justification
- Strategic choice explanation
- Resource request pushback

Make the challenger's objection specific and reasonable.

Format your response as JSON:
{
    "scenario": "[Context of the position they've taken]\n\nA colleague pushes back: '[Specific, reasonable objection]'",
    "task": "Defend your position in 3-4 sentences. Acknowledge their point, but hold your ground."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's position defense.

Check for:
- Acknowledgment: Did they address the objection?
- Firmness: Did they hold their ground or cave?
- Evidence: Did they back their position with something concrete?
- Tone: Professional disagreement or defensive/aggressive?

Good defense = acknowledge + reframe + hold.

Format your response as JSON:
{
    "feedback": "[Your critique of their defense]",
    "score": 0-100
}

Score guide:
- 90-100: Acknowledged, held ground with evidence, professional tone
- 70-89: Defended but missed acknowledgment or got slightly defensive
- 50-69: Held ground but poorly, or caved too easily
- 0-49: Got defensive, caved completely, or ignored the objection
PROMPT,
            ],
            [
                'name' => 'Summarize and Redirect',
                'position' => 2,
                'timer_seconds' => 45,
                'input_type' => 'text',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a summarize-and-redirect drill scenario.

Create a meeting situation where:
- Discussion has gone off track or into the weeds
- Multiple people have spoken with varying points
- Someone needs to summarize and get things back on track

Provide 3-4 distinct points that have been made (some relevant, some tangential).

Format your response as JSON:
{
    "scenario": "You're in a meeting that's drifted. Here's what's been said:\n\n[List of 3-4 points made by different people, with names]",
    "task": "Summarize the key point in 1 sentence, then redirect to what matters. 2-3 sentences total."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's summarize-and-redirect.

Check for:
- Accuracy: Did they capture the actual key point?
- Brevity: 1 sentence summary as requested?
- Redirect: Clear next step or focus?
- Diplomacy: Did they dismiss tangents without insulting contributors?

This is about leadership in meetings.

Format your response as JSON:
{
    "feedback": "[Your critique of their summary and redirect]",
    "score": 0-100
}

Score guide:
- 90-100: Accurate summary, clear redirect, diplomatic
- 70-89: Good but summary too long or redirect unclear
- 50-69: Got the gist but added confusion or was abrupt
- 0-49: Wrong summary, no redirect, or insulted contributors
PROMPT,
            ],
        ];

        foreach ($drills as $drill) {
            Drill::updateOrCreate(
                [
                    'practice_mode_id' => $mode->id,
                    'name' => $drill['name'],
                ],
                array_merge($drill, ['practice_mode_id' => $mode->id])
            );
        }
    }
}
