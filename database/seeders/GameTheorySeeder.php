<?php

namespace Database\Seeders;

use App\Models\Drill;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\Principle;
use App\Models\SkillDimension;
use Illuminate\Database\Seeder;

class GameTheorySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSkillDimensions();
        $this->seedPrinciple();
        $this->seedInsights();
        $this->seedPracticeMode();
    }

    private function seedSkillDimensions(): void
    {
        $dimensions = [
            [
                'key' => 'game_structure_recognition',
                'label' => 'Game Structure Recognition',
                'description' => 'Ability to identify the type of strategic situation—prisoner\'s dilemma, coordination game, chicken, battle of the sexes—and understand what that implies for optimal play.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Treats all conflicts the same; misses structural differences between situations',
                    'mid' => 'Recognizes classic structures when obvious; struggles with mixed or ambiguous games',
                    'high' => 'Quickly identifies game type and adjusts strategy accordingly',
                    'exemplary' => 'Sees multiple valid framings; understands how structure shifts optimal play',
                ],
                'improvement_tips' => [
                    'low' => 'Learn the basic game types: prisoner\'s dilemma (mutual defection hurts everyone), coordination (we need to match), chicken (who swerves first?). Ask: "Which one is this?"',
                    'mid' => 'When a situation feels unclear, map the payoffs explicitly. Draw the 2x2 matrix. The structure often becomes obvious.',
                    'high' => 'Consider how the game changes if you add or remove players, change timing, or alter information. Games transform more than you think.',
                ],
            ],
            [
                'key' => 'incentive_mapping',
                'label' => 'Incentive Mapping',
                'description' => 'Understanding what each party actually wants—their real preferences and constraints—not just what they claim or what seems obvious.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Takes stated preferences at face value; surprised when people act against their words',
                    'mid' => 'Considers hidden incentives but may miss subtle or structural pressures',
                    'high' => 'Accurately maps what each party is optimizing for; distinguishes stated from revealed preferences',
                    'exemplary' => 'Understands incentive layers—personal, role-based, organizational—and how they interact',
                ],
                'improvement_tips' => [
                    'low' => 'Ignore what people say they want. Watch what they do. Revealed preference beats stated preference.',
                    'mid' => 'Ask: "What does this person get rewarded for? What gets them in trouble?" Incentives often come from above.',
                    'high' => 'Map the full incentive stack: personal goals, role requirements, organizational pressures, career incentives. They often conflict.',
                ],
            ],
            [
                'key' => 'second_order_thinking',
                'label' => 'Second-Order Thinking',
                'description' => 'Anticipating how others will respond to your move before you make it, rather than treating your action as the end of the sequence.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Focuses only on immediate effects; surprised by others\' responses',
                    'mid' => 'Considers likely responses but may miss less obvious reactions',
                    'high' => 'Systematically thinks through "and then they will..." before acting',
                    'exemplary' => 'Anticipates multiple response pathways; plans for different scenarios',
                ],
                'improvement_tips' => [
                    'low' => 'Before any move, pause and ask: "What will they do after I do this?" Don\'t stop at your action.',
                    'mid' => 'Consider multiple possible responses, not just the most likely. What if they surprise you?',
                    'high' => 'Think about what your move signals. Others respond to what they think you\'re doing, not just what you actually did.',
                ],
            ],
            [
                'key' => 'third_order_thinking',
                'label' => 'Third-Order Thinking',
                'description' => 'Considering responses to responses—thinking three or more moves ahead in a strategic sequence.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Rarely thinks past immediate response; blindsided by cascading effects',
                    'mid' => 'Can think ahead when prompted but doesn\'t naturally extend the chain',
                    'high' => 'Routinely considers second and third-order effects before acting',
                    'exemplary' => 'Traces long causal chains; sees how early moves constrain or enable later ones',
                ],
                'improvement_tips' => [
                    'low' => 'Practice the "three whats": What will I do? What will they do? What will I do then? Never stop at step one.',
                    'mid' => 'For important decisions, write out the move sequence. It\'s hard to hold in your head but obvious on paper.',
                    'high' => 'Consider how your current move shapes the menu of future moves—yours and theirs. Some moves open options; others close them.',
                ],
            ],
            [
                'key' => 'one_shot_vs_repeated',
                'label' => 'One-Shot vs. Repeated Distinction',
                'description' => 'Recognizing when a strategic interaction is truly one-time versus when relationship continuity changes optimal strategy.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Treats all interactions the same regardless of future relationship',
                    'mid' => 'Understands the distinction conceptually but misapplies it in context',
                    'high' => 'Accurately assesses whether future interactions matter and adjusts strategy',
                    'exemplary' => 'Sees how reputation effects extend even "one-shot" games; understands shadow of the future',
                ],
                'improvement_tips' => [
                    'low' => 'Ask: "Will I deal with this person again? Will they talk to people I\'ll deal with?" If yes, it\'s not one-shot.',
                    'mid' => 'Remember: reputation travels. Even apparent one-shots exist in a web of relationships.',
                    'high' => 'Consider the shadow of the future. How does the likelihood of future interaction change what\'s optimal now?',
                ],
            ],
            [
                'key' => 'information_asymmetry_awareness',
                'label' => 'Information Asymmetry Awareness',
                'description' => 'Noticing what each party knows that others don\'t, and understanding how this shapes strategic possibilities.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Assumes everyone has the same information; ignores private knowledge',
                    'mid' => 'Recognizes obvious information gaps but misses subtle asymmetries',
                    'high' => 'Maps what each party knows and doesn\'t know; considers how to use or reduce gaps',
                    'exemplary' => 'Understands second-order information (what they know about what you know); uses strategically',
                ],
                'improvement_tips' => [
                    'low' => 'For each party, ask: "What do they know that others don\'t? What do they not know that others do?"',
                    'mid' => 'Consider not just facts but beliefs. What does each party believe about the others\' information?',
                    'high' => 'Think about how information gaps can be exploited or closed. Sometimes revealing information is a strategic move.',
                ],
            ],
            [
                'key' => 'credibility_assessment',
                'label' => 'Credibility Assessment',
                'description' => 'Evaluating whether threats, promises, and commitments are believable based on incentives and constraints.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Takes threats and promises at face value without evaluating believability',
                    'mid' => 'Questions credibility but lacks systematic framework for assessment',
                    'high' => 'Evaluates commitments against incentives, costs, and track record',
                    'exemplary' => 'Understands how to make own commitments credible; sees commitment devices others use',
                ],
                'improvement_tips' => [
                    'low' => 'For any threat or promise, ask: "Would it actually be in their interest to follow through?" If not, it\'s not credible.',
                    'mid' => 'Look for commitment devices—things that make it costly NOT to follow through. Burned bridges, public statements, contracts.',
                    'high' => 'Consider how to make your own commitments credible. Sometimes constraining your options strengthens your position.',
                ],
            ],
            [
                'key' => 'signaling_recognition',
                'label' => 'Signaling Recognition',
                'description' => 'Identifying when actions are meant to communicate information or intent, not just accomplish a direct goal.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Sees actions only for their direct effects; misses communicative intent',
                    'mid' => 'Recognizes obvious signals but misses subtle or costly signaling',
                    'high' => 'Distinguishes signaling from substance; understands why costly signals are credible',
                    'exemplary' => 'Reads signal content accurately; uses signaling strategically in own actions',
                ],
                'improvement_tips' => [
                    'low' => 'When someone does something that seems inefficient, ask: "What might this be communicating?"',
                    'mid' => 'Credible signals are costly. If it\'s cheap to fake, it\'s not a reliable signal. Look for skin in the game.',
                    'high' => 'Consider what your own actions signal beyond their direct effects. Every move communicates.',
                ],
            ],
        ];

        foreach ($dimensions as $dimension) {
            SkillDimension::updateOrCreate(
                ['key' => $dimension['key']],
                $dimension
            );
        }
    }

    private function seedPrinciple(): void
    {
        Principle::updateOrCreate(
            ['slug' => 'strategic-thinking'],
            [
                'name' => 'Strategic Thinking',
                'slug' => 'strategic-thinking',
                'description' => 'Learn to see the strategic games hidden in everyday situations. Understand what others are optimizing for, anticipate their moves, and choose your response accordingly.',
                'icon' => 'target',
                'position' => 11,
                'is_active' => true,
                'blog_urls' => null,
            ]
        );
    }

    private function seedInsights(): void
    {
        $principle = Principle::where('slug', 'strategic-thinking')->first();

        if (! $principle) {
            return;
        }

        $insights = [
            [
                'name' => 'Name the Game',
                'slug' => 'name-the-game',
                'summary' => 'Every strategic situation has a structure. Prisoner\'s dilemma, coordination, chicken—the name tells you what moves make sense.',
                'content' => <<<'MARKDOWN'
You're in a negotiation. Should you be aggressive or collaborative? Competitive or cooperative?

The answer isn't a personality preference. It's determined by the structure of the game you're playing.

## The Four Games You're Usually Playing

Most strategic situations fit one of a few structures:

**Prisoner's Dilemma**: Both parties would be better off cooperating, but each has an individual incentive to defect. Arms races, price wars, and the tragedy of the commons live here.

**Coordination Game**: Both parties benefit from matching, but need to agree on what to match on. Scheduling meetings, choosing standards, picking a restaurant.

**Chicken**: Both parties lose if neither backs down, but whoever backs down first loses more. Deadline standoffs, merger negotiations, territorial disputes.

**Battle of the Sexes**: Both want to coordinate, but each prefers coordinating on their own preferred option. Project direction when stakeholders have different visions.

## Why Structure Matters

In a prisoner's dilemma, being cooperative while the other defects is the worst outcome. In a coordination game, being cooperative is nearly always right.

If you play the wrong strategy for the game you're in, you'll either leave value on the table or get exploited.

## The Diagnosis

Before choosing your approach, ask:

1. What happens if we both cooperate?
2. What happens if we both defect/compete?
3. What happens if one cooperates and one defects?
4. Who moves first? Does that matter?

The answers tell you which game you're playing. And the game tells you how to play.
MARKDOWN,
                'position' => 0,
            ],
            [
                'name' => 'What Are They Actually Optimizing For?',
                'slug' => 'what-are-they-optimizing-for',
                'summary' => 'Ignore what people say they want. Watch what they do. The revealed preference is the real preference.',
                'content' => <<<'MARKDOWN'
They say they want the project to succeed. But every decision they make delays it.

They say they care about quality. But they approve every cost cut that compromises it.

They say they want your honest feedback. But they punish you every time you give it.

Welcome to the gap between stated and revealed preferences.

## Revealed Preference

Economists have a useful concept: revealed preference. What people actually want is revealed by what they actually do, especially when it costs them something.

Talk is cheap. Behavior is expensive. Behavior tells the truth.

## The Incentive Stack

People aren't usually lying when their stated and revealed preferences diverge. They're often unaware of the divergence themselves.

The explanation is usually incentives. People optimize for what they're rewarded for, punished for, and measured on—often unconsciously.

Your colleague might genuinely believe they want the project to succeed. But if their bonus depends on their department's headcount, and the project threatens that, watch what happens.

## The Map

Before any strategic interaction, map the incentive stack:

1. **What do they say they want?** (stated preference)
2. **What do they actually do?** (revealed preference)
3. **What are they rewarded for?** (formal incentives)
4. **What do they fear?** (negative incentives)
5. **What does their boss want?** (inherited incentives)

The gap between #1 and #2-5 is where strategy lives.

## The Implication

Don't be angry when people act on their incentives rather than their words. Don't be surprised. Just factor it in.

And check your own incentives too. You might be doing the same thing without realizing it.
MARKDOWN,
                'position' => 1,
            ],
            [
                'name' => 'One-Shot vs. Repeated Changes Everything',
                'slug' => 'one-shot-vs-repeated',
                'summary' => 'In a one-shot game, defection often wins. In a repeated game, cooperation becomes rational. Know which one you\'re in.',
                'content' => <<<'MARKDOWN'
A used car salesman can lie to you and profit. A car dealer in a small town where reputation travels cannot.

The difference isn't ethics. It's game structure.

## The Shadow of the Future

In a truly one-shot interaction—you'll never see this person again, they have no connection to anyone you know—defection and exploitation become more attractive. There's no future to protect.

In a repeated game, the future casts a shadow over the present. Today's betrayal becomes tomorrow's retaliation. Cooperation becomes not just nice but rational.

## The Trap

Most of us underestimate how repeated our games are.

You think it's one-shot: "I'll never work with this vendor again."
Reality: They talk to other vendors. Who talk to your future partners.

You think it's one-shot: "This negotiation is a one-time thing."
Reality: You might need something from them later. They might end up at a company you work with.

The world is smaller and more connected than it feels. Most "one-shot" games are actually repeated games in disguise.

## When It Really Is One-Shot

True one-shot games do exist:

- Anonymous transactions with no reputation trail
- End-game situations with no future
- Interactions where counterparty has no network overlap with yours

In these cases, you should expect others to defect more readily—and protect yourself accordingly.

## The Calculation

Before any strategic move, ask: "What's the probability I'll interact with this person or their network again?"

If it's above 20%, treat it as a repeated game. If it's near 0%, protect yourself like it's one-shot—regardless of what they promise.
MARKDOWN,
                'position' => 2,
            ],
            [
                'name' => 'Credible Commitments',
                'slug' => 'credible-commitments',
                'summary' => 'A threat you won\'t follow through on isn\'t a threat. A promise you can\'t keep isn\'t a promise. Credibility requires constraints.',
                'content' => <<<'MARKDOWN'
"If you do that, I'll quit."

Will you, though? Really? Is it in your interest to actually quit?

If the answer is no—if following through would hurt you more than them—it's not a credible threat. And they probably know it.

## The Credibility Problem

For a threat or promise to change behavior, the other party must believe you'll follow through. That belief depends not on your words but on your incentives.

If carrying out the threat costs you more than backing down, rational opponents will call your bluff.

## Making Commitments Credible

The solution is counterintuitive: to make your commitment credible, you need to remove your own ability to back down.

This is why:
- Generals burned boats so retreat was impossible
- Companies announce product launches publicly before they're ready
- Politicians make promises in writing that would embarrass them to break

By eliminating your exit option, you make your commitment believable.

## Reading Others' Commitments

When someone makes a threat or promise, evaluate:

1. **Would following through serve their interests?** If yes, it's likely credible.
2. **Have they constrained their options?** Public commitments, contracts, burned bridges.
3. **What's their track record?** Past behavior predicts future behavior.
4. **What's the cost of backing down?** If they lose face or future credibility, they're more likely to follow through.

## The Paradox

Sometimes the best way to get what you want is to give up options. By constraining yourself, you strengthen your position.

It's not irrational. It's credible commitment.
MARKDOWN,
                'position' => 3,
            ],
            [
                'name' => 'The Signal and the Substance',
                'slug' => 'signal-and-substance',
                'summary' => 'Actions do two things: accomplish goals and send messages. Often the message matters more than the accomplishment.',
                'content' => <<<'MARKDOWN'
A company donates to charity. Is it altruism or marketing?

An employee stays late. Is it dedication or performance?

A country conducts military exercises. Is it training or intimidation?

The answer is often: both. And sometimes the signal is the point.

## Signaling Theory

Many actions serve two purposes: a direct effect and a communicative effect. The communicative effect is the signal.

Signals convey information that's hard to communicate directly:
- "I'm committed to this relationship"
- "I'm capable and confident"
- "I'll retaliate if you cross me"

Words are cheap. Anyone can claim these things. But costly actions are harder to fake.

## Why Costly Signals Work

The key insight: reliable signals must be costly to send, especially for those who don't have the underlying quality being signaled.

A wealthy person can easily donate $1,000. A struggling person cannot. So the donation signals wealth precisely because it's costly for non-wealthy people to fake.

Staying late is a credible signal of dedication because it's costly for non-dedicated employees to consistently fake it.

## Reading Signals

When you see behavior that seems excessive or inefficient for its direct purpose, ask:

1. What might this action be communicating?
2. Who is the audience?
3. Why is this a credible signal? (What makes it costly to fake?)

## Sending Signals

Your actions are always signaling, whether you intend them to or not. Consider:

1. What does this action communicate beyond its direct effect?
2. Is that the message I want to send?
3. Is my signal credible? (Am I paying enough of a cost for it to be believed?)

Sometimes the right move is to do something "inefficient" specifically because the signal value exceeds the direct cost.
MARKDOWN,
                'position' => 4,
            ],
        ];

        foreach ($insights as $insightData) {
            Insight::updateOrCreate(
                ['slug' => $insightData['slug']],
                array_merge($insightData, [
                    'principle_id' => $principle->id,
                    'is_active' => true,
                ])
            );
        }
    }

    private function seedPracticeMode(): void
    {
        $mode = PracticeMode::updateOrCreate(
            ['slug' => 'game-theory'],
            [
                'name' => 'Game Theory',
                'tagline' => 'See the game before you play it',
                'description' => 'Learn to recognize the strategic games hidden in everyday situations. Understand what others are optimizing for, anticipate their moves, and choose your response accordingly.',
                'instruction_set' => $this->getInstructionSet(),
                'config' => [
                    'input_character_limit' => 800,
                    'reflection_character_limit' => 300,
                    'max_response_tokens' => 1000,
                    'max_history_exchanges' => 6,
                    'model' => 'claude-sonnet-4-20250514',
                ],
                'required_plan' => 'free',
                'icon' => 'Crosshair',
                'is_active' => true,
                'sort_order' => 40,
            ]
        );

        $this->seedRequiredContext($mode);
        $this->seedDrills($mode);
    }

    private function getInstructionSet(): string
    {
        return <<<'INSTRUCTION'
You are a strategic thinking coach helping users develop the ability to recognize and navigate strategic interactions. Your role is to present game-theoretic situations and evaluate the user's ability to identify structures, anticipate responses, and choose optimal strategies.

## User Context
The user is a {{career_level}} {{job_title}} working in {{industry}}. They work in a {{team_composition}} environment.

## Difficulty Level
Current level: {{level}}

Level scaling:
- Levels 1-2: Classic, clear game structures (obvious prisoner's dilemma, straightforward negotiation). You explain frameworks explicitly and push back gently. User needs prompting to consider other party's incentives.
- Levels 3-4: Mixed or ambiguous structures. Multiple plausible interpretations. You name frameworks less, ask more, push back directly.
- Levels 5-6: Information asymmetry matters. Signaling and credibility come into play. You assume familiarity with basic game structures.
- Levels 7-8: Multiple players with different games. Reputation effects and repeated game dynamics. You challenge aggressively.
- Levels 9-10: Complex strategic environments with incomplete information, nested games, and coalition dynamics. You assume fluency and use full confrontational voice.

## Coaching Approach
1. Present realistic scenarios that contain strategic structure relevant to the user's context
2. Evaluate responses for strategic reasoning, not just "correct" answers
3. Challenge naive assumptions about other parties' motivations
4. Push users to think multiple moves ahead
5. Distinguish one-shot from repeated game thinking
6. Test understanding of credibility, signaling, and information asymmetry

## Key Principles
- Every strategic situation has a structure—name it
- What people actually do reveals what they actually want
- One-shot vs. repeated changes optimal strategy entirely
- Credible commitments require constraints, not just words
- Actions signal as well as accomplish

## Blind Spot Categories to Watch For
- Defaults to competition when cooperation dominates
- Defaults to cooperation when competition dominates
- Treats repeated games as one-shot
- Treats one-shot games as repeated
- Ignores information asymmetry
- Overweights own position, underweights other party's
- Misses credible commitment opportunities
- Fails to consider what happens if bluff is called
- Assumes rationality when other party may be emotional
- Overcomplicates simple coordination problems
- Fails to recognize signaling behavior
- Misidentifies game structure entirely
INSTRUCTION;
    }

    private function seedRequiredContext(PracticeMode $mode): void
    {
        $mode->requiredContext()->delete();

        $contextFields = [
            'career_level',
            'job_title',
            'industry',
            'team_composition',
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
                'name' => 'Name the Game',
                'position' => 0,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['game_structure_recognition', 'incentive_mapping', 'one_shot_vs_repeated'],
                'primary_insight_slug' => 'name-the-game',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a game identification scenario appropriate for the user's level and context.

Create a strategic situation that embodies a recognizable game structure (prisoner's dilemma, coordination, chicken, battle of the sexes, or variants).

Include:
- Two or more parties with clear interests
- A decision point where strategy matters
- Enough context to identify the payoff structure
- Relevance to the user's professional context

At higher levels, add:
- Mixed games that combine elements of multiple structures
- Ambiguous situations where structure isn't immediately clear
- Games where the obvious framing is wrong
- Multiple valid interpretations depending on assumptions

Format your response as JSON:
{
    "scenario": "[The strategic situation]\n\n[The parties involved and their apparent interests]\n\n[The decision being faced]",
    "task": "What game is being played here? Name the structure (prisoner's dilemma, coordination, chicken, etc.) and explain why. What does the structure tell you about optimal strategy? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's game identification.

Check for:
- Correct identification: Did they name the right game structure (or a defensible alternative)?
- Reasoning: Did they explain WHY this is that structure (payoffs, incentives)?
- Strategic implication: Did they connect structure to strategy?
- Nuance: Did they note any ambiguity or caveats?

Red flags:
- Naming a game without explaining the payoff logic
- Missing obvious structural features
- Applying generic "cooperate" or "compete" without structural reasoning
- Ignoring whether the game is one-shot or repeated

At higher levels, also check:
- Did they consider alternative framings?
- Did they note what would need to be true for the structure to be different?
- Did they identify mixed or nested game elements?

Quote their reasoning and explain what they got right or wrong about the structure.
PROMPT,
            ],
            [
                'name' => 'Incentive Mapping',
                'position' => 1,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['incentive_mapping', 'information_asymmetry_awareness', 'second_order_thinking'],
                'primary_insight_slug' => 'what-are-they-optimizing-for',
                'scenario_instruction_set' => <<<'PROMPT'
Generate an incentive mapping scenario appropriate for the user's level and context.

Create a situation where someone's stated preferences may differ from their actual incentives. The gap should be explainable by incentive structure, not malice.

Include:
- A party whose behavior seems inconsistent with their stated goals
- Enough context to identify likely real incentives
- Multiple stakeholders with different interests
- A decision that depends on understanding true preferences

At higher levels, add:
- Multiple layers of incentives (personal, role-based, organizational)
- Incentives that conflict with each other
- Hidden constraints the user needs to infer
- Situations where the person may not be aware of their own incentives

Format your response as JSON:
{
    "scenario": "[The situation with apparent inconsistency]\n\n[What the party says they want vs. what they're doing]\n\n[Relevant context about roles, pressures, stakeholders]",
    "task": "What is this person actually optimizing for? Map their incentives—stated, revealed, and structural. How should this change your approach? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's incentive mapping.

Check for:
- Revealed vs. stated: Did they distinguish what the person says from what they do?
- Incentive identification: Did they find the structural pressures driving behavior?
- Empathy: Did they explain the behavior without assuming bad faith?
- Strategic application: Did they connect incentive understanding to approach?

Red flags:
- Taking stated preferences at face value
- Assuming malice or stupidity instead of looking for incentives
- Missing obvious structural pressures (boss's preferences, metrics, rewards)
- Mapping incentives without strategic implications

At higher levels, also check:
- Did they identify multiple incentive layers?
- Did they consider what the person might not know about themselves?
- Did they note information asymmetries?

Quote their analysis. Point out insights and blind spots.
PROMPT,
            ],
            [
                'name' => 'Think Three Moves Ahead',
                'position' => 2,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'dimensions' => ['second_order_thinking', 'third_order_thinking', 'game_structure_recognition'],
                'primary_insight_slug' => 'one-shot-vs-repeated',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a multi-move thinking scenario appropriate for the user's level and context.

Create a situation where the user needs to think beyond their immediate move to anticipate responses and counter-responses.

Include:
- A clear first move the user is considering
- Other parties who will respond
- Stakes that matter
- Enough context to reason about likely responses

At higher levels, add:
- Multiple response pathways to consider
- Moves that constrain or enable future moves
- Information that will be revealed by the move
- Long-term relationship implications

Format your response as JSON:
{
    "scenario": "[The situation and the move being considered]\n\n[The other parties who will respond]\n\n[Relevant stakes and context]",
    "task": "Think this through. If you make this move, what happens next? What do they do? What do you do then? Trace at least three moves ahead and evaluate whether the initial move is wise. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's multi-move thinking.

Check for:
- Depth: Did they actually think 3+ moves ahead, or stop at 1-2?
- Response anticipation: Are their predicted responses plausible?
- Branching: Did they consider multiple possible responses?
- Final evaluation: Did they connect the analysis back to whether the initial move is good?

Red flags:
- Stopping at the immediate effect of their move
- Assuming others won't respond or will respond passively
- Only considering the best-case response scenario
- Thinking ahead without concluding anything about the initial move

At higher levels, also check:
- Did they consider how the move changes future option space?
- Did they note information revelation effects?
- Did they consider the repeated game implications?

Quote their reasoning chain. Point out where they went deep and where they stopped short.
PROMPT,
            ],
            [
                'name' => 'Credibility Check',
                'position' => 3,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['credibility_assessment', 'second_order_thinking', 'incentive_mapping'],
                'primary_insight_slug' => 'credible-commitments',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a credibility assessment scenario appropriate for the user's level and context.

Create a situation where someone is making a threat, promise, or commitment that may or may not be credible.

Include:
- A specific threat or promise being made
- Context about the party making the commitment
- Information relevant to assessing follow-through likelihood
- Stakes for both parties

At higher levels, add:
- Commitments that are partially credible
- Commitment devices that may or may not work
- Situations where credibility depends on information the user doesn't have
- Opportunities for the user to make their own commitments credible

Format your response as JSON:
{
    "scenario": "[The commitment being made and by whom]\n\n[Context about the party and their position]\n\n[What happens if they follow through vs. don't]",
    "task": "Is this commitment credible? Why or why not? What would make it more credible? If it's not credible, how should you respond? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's credibility assessment.

Check for:
- Incentive analysis: Did they check if follow-through serves the committer's interests?
- Constraint identification: Did they look for commitment devices?
- Track record: Did they consider past behavior?
- Strategic response: Did they connect credibility assessment to their own strategy?

Red flags:
- Taking the commitment at face value without analysis
- Dismissing commitments without reasoning
- Ignoring commitment devices that exist
- Assessing credibility without considering response strategy

At higher levels, also check:
- Did they consider what would make the commitment more credible?
- Did they note how to test credibility?
- Did they consider their own credible commitment opportunities?

Quote their reasoning and evaluate the quality of their credibility analysis.
PROMPT,
            ],
            [
                'name' => 'Signal or Substance',
                'position' => 4,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['signaling_recognition', 'incentive_mapping', 'information_asymmetry_awareness'],
                'primary_insight_slug' => 'signal-and-substance',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a signaling analysis scenario appropriate for the user's level and context.

Create a situation where someone's action may be serving a signaling purpose beyond (or instead of) its direct effect.

Include:
- An action that seems notable, excessive, or inefficient for its stated purpose
- Context about who might be the audience
- Information relevant to assessing signal vs. substance
- Stakes that make the signaling interpretation plausible

At higher levels, add:
- Multiple possible audiences for the signal
- Costly signals that are hard to fake
- Situations where the user needs to decide how to respond to a signal
- Opportunities for the user to send their own signals

Format your response as JSON:
{
    "scenario": "[The action being taken]\n\n[Context about the actor and potential audiences]\n\n[Why the action seems notable or worth analyzing]",
    "task": "Is this primarily signaling or substance? Who is the audience? What is being communicated? How should you interpret and respond to this signal? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's signaling analysis.

Check for:
- Signal identification: Did they recognize the signaling dimension?
- Audience identification: Did they consider who the signal is for?
- Costliness: Did they assess whether the signal is costly enough to be credible?
- Response: Did they consider how to respond to the signal?

Red flags:
- Seeing only the direct effect of the action
- Identifying signal without considering audience
- Missing the costly signaling logic
- Overinterpreting—seeing signals in everything

At higher levels, also check:
- Did they consider multiple possible audiences?
- Did they note what makes this signal credible (or not)?
- Did they consider sending counter-signals?

Quote their analysis and evaluate the sophistication of their signaling thinking.
PROMPT,
            ],
            [
                'name' => 'Repeated or One-Shot?',
                'position' => 5,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['one_shot_vs_repeated', 'game_structure_recognition', 'credibility_assessment'],
                'primary_insight_slug' => 'one-shot-vs-repeated',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a repeated vs. one-shot assessment scenario appropriate for the user's level and context.

Create a strategic situation where determining if it's one-shot or repeated significantly affects optimal strategy.

Include:
- A strategic interaction with clear stakes
- Factors relevant to assessing future interaction probability
- A decision that would be made differently depending on the answer
- Enough context to reason about relationship continuity

At higher levels, add:
- Situations that appear one-shot but have reputation effects
- Games embedded in larger repeated games
- Uncertainty about whether it's repeated
- Strategic choices about whether to MAKE it repeated

Format your response as JSON:
{
    "scenario": "[The strategic interaction]\n\n[The parties and their apparent relationship]\n\n[Factors relevant to future interaction probability]",
    "task": "Is this a one-shot game or a repeated game? What factors determine this? How does your answer change the optimal strategy? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's one-shot vs. repeated analysis.

Check for:
- Correct assessment: Did they reach a defensible conclusion about the game type?
- Factor identification: Did they consider the right factors (future interaction, reputation, network)?
- Strategic implication: Did they connect the assessment to strategy?
- Nuance: Did they note uncertainty or edge cases?

Red flags:
- Treating the question as binary without considering probability
- Ignoring reputation and network effects
- Saying "repeated" just because future interaction is possible
- Saying "one-shot" just because it's a first interaction

At higher levels, also check:
- Did they consider the shadow of the future?
- Did they note how to change the game type?
- Did they consider nested game dynamics?

Quote their reasoning and evaluate the quality of their one-shot vs. repeated thinking.
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

            if ($primaryInsightSlug) {
                $insight = Insight::where('slug', $primaryInsightSlug)->first();
                if ($insight) {
                    $drill->insights()->syncWithoutDetaching([
                        $insight->id => ['is_primary' => true],
                    ]);
                }
            }
        }
    }
}
