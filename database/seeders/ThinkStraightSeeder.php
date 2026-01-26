<?php

namespace Database\Seeders;

use App\Models\Drill;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\Principle;
use App\Models\SkillDimension;
use Illuminate\Database\Seeder;

class ThinkStraightSeeder extends Seeder
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
                'key' => 'bias_recognition',
                'label' => 'Bias Recognition',
                'description' => 'Identifying when a cognitive bias is influencing your thinking or someone else\'s, catching the systematic errors before they lead to bad decisions.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Unaware of biases; treats intuitions as reliable without examination',
                    'mid' => 'Recognizes biases in others but struggles to catch own biased thinking',
                    'high' => 'Regularly identifies biases in real-time; adjusts reasoning accordingly',
                    'exemplary' => 'Anticipates which biases will be triggered before they hit; builds debiasing into process',
                ],
                'improvement_tips' => [
                    'low' => 'Learn the big five: confirmation bias, availability bias, anchoring, sunk cost, and hindsight bias. Start noticing them in news and conversations.',
                    'mid' => 'When you feel certain, that\'s the moment to check for bias. Strong feelings of rightness often signal motivated reasoning.',
                    'high' => 'Before important decisions, run a bias pre-mortem: which biases are most likely to affect this specific situation?',
                ],
            ],
            [
                'key' => 'fallacy_detection',
                'label' => 'Fallacy Detection',
                'description' => 'Spotting flawed logic in arguments—yours and others\'—recognizing when a conclusion doesn\'t actually follow from its premises.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Persuaded by confident-sounding arguments regardless of logic',
                    'mid' => 'Catches obvious fallacies but fooled by sophisticated versions',
                    'high' => 'Identifies logical flaws quickly; distinguishes weak from strong arguments',
                    'exemplary' => 'Deconstructs complex arguments into components; identifies exactly where logic fails',
                ],
                'improvement_tips' => [
                    'low' => 'Learn the common fallacies: ad hominem, straw man, false dichotomy, appeal to authority. Practice naming them when you see them.',
                    'mid' => 'Focus on the structure, not the content. Ask: "Even if the premises are true, does the conclusion follow?"',
                    'high' => 'Apply the same rigor to arguments you agree with. You\'re more likely to miss fallacies when you like the conclusion.',
                ],
            ],
            [
                'key' => 'evidence_evaluation',
                'label' => 'Evidence Evaluation',
                'description' => 'Assessing whether evidence actually supports a conclusion—checking quality, relevance, and sufficiency rather than just presence.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Treats any supporting evidence as proof; ignores evidence quality',
                    'mid' => 'Considers evidence quality but may miss relevance or sufficiency issues',
                    'high' => 'Systematically evaluates evidence on multiple dimensions; weights appropriately',
                    'exemplary' => 'Distinguishes strong from weak evidence intuitively; seeks disconfirming evidence',
                ],
                'improvement_tips' => [
                    'low' => 'For any evidence, ask three questions: Is this reliable? Is this relevant to the specific claim? Is this sufficient to support the conclusion?',
                    'mid' => 'Watch for the difference between "some evidence" and "enough evidence." One data point isn\'t a pattern.',
                    'high' => 'Actively seek evidence that would disprove your conclusion. If you can\'t find any, you might not be looking hard enough.',
                ],
            ],
            [
                'key' => 'motivated_reasoning_awareness',
                'label' => 'Motivated Reasoning Awareness',
                'description' => 'Noticing when you\'re reasoning toward a desired conclusion rather than following the evidence wherever it leads.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Unaware of preference influence; believes own reasoning is objective',
                    'mid' => 'Recognizes motivated reasoning after the fact; struggles to catch it in real-time',
                    'high' => 'Notices when preferences are pulling conclusions; applies extra scrutiny',
                    'exemplary' => 'Treats strong preferences as warning signals; actively argues against desired conclusions',
                ],
                'improvement_tips' => [
                    'low' => 'Ask yourself: "Do I want this to be true?" If yes, that\'s a red flag that you need to scrutinize your reasoning more carefully.',
                    'mid' => 'Notice when you feel relieved by a conclusion. Relief suggests you were hoping for that answer—which means bias risk.',
                    'high' => 'Force yourself to argue the opposite of what you want to believe. If you can\'t make a strong case, your reasoning might be sound. If you can, investigate further.',
                ],
            ],
            [
                'key' => 'base_rate_consideration',
                'label' => 'Base Rate Consideration',
                'description' => 'Factoring in prior probabilities and general frequencies, not just the specific information in front of you.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Ignores base rates entirely; judges solely on specific case details',
                    'mid' => 'Knows base rates matter but struggles to integrate them with case-specific info',
                    'high' => 'Appropriately weights base rates; adjusts for new information correctly',
                    'exemplary' => 'Thinks in terms of probabilities; updates beliefs proportionally to evidence strength',
                ],
                'improvement_tips' => [
                    'low' => 'Before analyzing specifics, ask: "How often does this happen in general?" Start from the base rate, then adjust.',
                    'mid' => 'Practice Bayesian updating: "Given the base rate AND this new information, what should I believe now?"',
                    'high' => 'Be especially careful when specific information is vivid and base rates are abstract. Vividness doesn\'t equal validity.',
                ],
            ],
            [
                'key' => 'counterfactual_thinking',
                'label' => 'Counterfactual Thinking',
                'description' => 'Genuinely considering "what if I\'m wrong?" and exploring alternative explanations rather than defending your current position.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Dismisses alternatives without consideration; defends initial position reflexively',
                    'mid' => 'Considers alternatives when prompted but doesn\'t seek them naturally',
                    'high' => 'Proactively explores "what if I\'m wrong?" before committing to conclusions',
                    'exemplary' => 'Generates multiple hypotheses naturally; holds conclusions loosely until tested',
                ],
                'improvement_tips' => [
                    'low' => 'Before concluding, force yourself to generate two alternative explanations. They don\'t have to be likely—just possible.',
                    'mid' => 'Ask: "What would I expect to see if I were wrong?" Then look for that evidence.',
                    'high' => 'Assign rough probabilities to alternatives. This prevents dismissing unlikely-but-possible explanations prematurely.',
                ],
            ],
            [
                'key' => 'confidence_calibration',
                'label' => 'Confidence Calibration',
                'description' => 'Matching your certainty level to the actual strength of your evidence—being confident when warranted and uncertain when appropriate.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Confidence disconnected from evidence; either overconfident or underconfident systematically',
                    'mid' => 'Generally calibrated but drifts under pressure or on familiar topics',
                    'high' => 'Confidence reliably tracks evidence quality; expresses appropriate uncertainty',
                    'exemplary' => 'Precisely calibrated; thinks in probability ranges; updates smoothly with new information',
                ],
                'improvement_tips' => [
                    'low' => 'Start expressing confidence in percentages: "I\'m 70% sure." Track your predictions to see if you\'re calibrated.',
                    'mid' => 'Notice domains where you tend toward overconfidence (usually familiar topics) and underconfidence (usually social pressure).',
                    'high' => 'Give confidence intervals, not point estimates. "Between 40% and 60%" is more honest than "about 50%."',
                ],
            ],
            [
                'key' => 'source_separation',
                'label' => 'Source Separation',
                'description' => 'Distinguishing between what you directly know, what you inferred, and what you assumed—tracking the provenance of your beliefs.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Treats all beliefs as equally reliable; confuses assumptions with facts',
                    'mid' => 'Can distinguish sources when asked but doesn\'t track naturally',
                    'high' => 'Maintains clear mental tags on belief sources; knows which are shakier',
                    'exemplary' => 'Traces belief provenance automatically; adjusts confidence based on source quality',
                ],
                'improvement_tips' => [
                    'low' => 'When stating something as fact, ask: "How do I know this? Did I see it, infer it, or assume it?"',
                    'mid' => 'Use language that tracks source: "I read that..." vs. "I assume..." vs. "It seems like..." Be precise.',
                    'high' => 'Build a mental hierarchy: direct observation > reliable testimony > inference > assumption. Weight accordingly.',
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
            ['slug' => 'clear-thinking'],
            [
                'name' => 'Clear Thinking',
                'slug' => 'clear-thinking',
                'description' => 'Learn to catch the cognitive biases, logical fallacies, and motivated reasoning that corrupt your decisions. Your brain lies to you—learn to notice when it\'s happening.',
                'icon' => 'brain',
                'position' => 12,
                'is_active' => true,
                'blog_urls' => null,
            ]
        );
    }

    private function seedInsights(): void
    {
        $principle = Principle::where('slug', 'clear-thinking')->first();

        if (! $principle) {
            return;
        }

        $insights = [
            [
                'name' => 'Your Brain\'s Cheat Codes',
                'slug' => 'your-brains-cheat-codes',
                'summary' => 'Cognitive biases aren\'t bugs—they\'re features that made sense on the savannah. Understanding why they exist helps you catch them.',
                'content' => <<<'MARKDOWN'
Your brain is running ancient software optimized for a world that no longer exists.

Cognitive biases aren't random errors. They're shortcuts that helped our ancestors survive. Confirmation bias? It kept the tribe unified. Availability bias? It prioritized recent threats. Sunk cost thinking? It prevented abandoning half-finished projects.

The problem is we're now using stone-age heuristics for modern decisions.

## The Big Five

Five biases cause most thinking errors:

**Confirmation Bias**: You seek evidence that supports what you already believe and dismiss evidence that doesn't. Your brain treats belief-confirming information as more credible.

**Availability Bias**: You overweight information that comes to mind easily—usually recent, vivid, or emotional events. Plane crashes feel more dangerous than car accidents.

**Anchoring**: The first number you hear becomes a reference point that warps all subsequent judgments. The asking price shapes what you think is reasonable.

**Sunk Cost Fallacy**: You factor in past investments that can't be recovered, throwing good money (or time, or effort) after bad.

**Hindsight Bias**: After learning the outcome, you believe you knew it all along. This makes you overconfident about predicting the future.

## The Meta-Problem

The trickiest part: biases feel like clear thinking. When you're experiencing confirmation bias, it feels like you're just being appropriately skeptical of bad evidence. When you're anchored, the anchor feels like relevant information.

You can't trust your feeling of objectivity.

## The Fix

Since biases feel invisible from the inside, you need external checks:

1. **Process over intuition**: Follow procedures that counteract biases rather than relying on "thinking carefully"
2. **Adversarial input**: Seek out people who disagree and take them seriously
3. **Time delays**: Let important decisions sit before finalizing—your initial judgment is most biased

The goal isn't to eliminate biases. It's to build systems that catch them.
MARKDOWN,
                'position' => 0,
            ],
            [
                'name' => 'The Logic Trap',
                'slug' => 'the-logic-trap',
                'summary' => 'A conclusion can sound logical and still be completely wrong. Validity and truth are different things.',
                'content' => <<<'MARKDOWN'
"All experts agree, so it must be true."
"We've always done it this way, so it works."
"Everyone believes it, so there must be something to it."

These sound reasonable. They're all logical fallacies.

## The Fallacy Zoo

Logical fallacies are argument structures that look valid but aren't. Some common ones:

**Ad Hominem**: Attacking the person instead of the argument. "You're just saying that because you're biased." (Even biased people can be right.)

**Straw Man**: Misrepresenting someone's argument to make it easier to attack. "So you're saying we should just do nothing?"

**False Dichotomy**: Presenting two options when more exist. "Either you're with us or against us."

**Appeal to Authority**: Accepting a claim because of who said it, not its evidence. "The CEO believes this, so it must be right."

**Slippery Slope**: Assuming one step inevitably leads to extreme outcomes. "If we allow this, next they'll want..."

**Correlation = Causation**: Assuming connection implies cause. "Crime rose after the policy changed, so the policy caused it."

## The Real Danger

Fallacies are most dangerous when they support conclusions you already believe.

When someone you disagree with uses a fallacy, you spot it immediately. When your own side uses the same fallacy? It sounds like common sense.

## The Test

For any argument, ask two questions:

1. **Even if the premises are true, does the conclusion follow?** (This tests validity)
2. **Are the premises actually true?** (This tests soundness)

Both must be yes for the argument to work. Fallacies fail on #1—the conclusion doesn't actually follow, even if it feels like it does.
MARKDOWN,
                'position' => 1,
            ],
            [
                'name' => 'Evidence Is Not Proof',
                'slug' => 'evidence-is-not-proof',
                'summary' => 'Some evidence exists for almost anything. The question isn\'t whether there\'s evidence—it\'s whether the evidence is good enough.',
                'content' => <<<'MARKDOWN'
"Studies show..."
"There's evidence that..."
"Research suggests..."

These phrases sound scientific. They tell you almost nothing.

## The Evidence Hierarchy

Not all evidence is created equal:

**Strongest**:
- Replicated randomized controlled trials
- Meta-analyses of multiple studies
- Large sample sizes with proper controls

**Medium**:
- Single well-designed studies
- Expert consensus with clear methodology
- Strong observational data with controls

**Weakest**:
- Anecdotes and case studies
- Correlation without causation testing
- Expert opinion without data
- "Common knowledge" without verification

When someone says "there's evidence," your first question should be: what kind?

## The Sufficiency Test

Even good evidence might not be enough. Ask:

1. **Is it relevant?** Does this evidence actually bear on the specific claim being made?
2. **Is it sufficient?** Is there enough evidence to support this level of confidence?
3. **Is it representative?** Or is this cherry-picked from a larger body of mixed evidence?

A single study is evidence. It's not proof. It might not even be suggestive if it's the only positive result among ten negative ones.

## The Motivated Evidence Search

Here's the trap: for any position you want to believe, you can find supporting evidence. The internet makes this trivially easy.

The question isn't "can I find evidence for this?" The question is "if I looked at ALL the evidence fairly, what would it suggest?"

If you're only looking for evidence that supports your view, you're not evaluating evidence. You're lawyering.
MARKDOWN,
                'position' => 2,
            ],
            [
                'name' => 'Wanting Makes You Stupid',
                'slug' => 'wanting-makes-you-stupid',
                'summary' => 'The more you want a conclusion to be true, the worse your reasoning about it becomes. Desire is the enemy of clear thinking.',
                'content' => <<<'MARKDOWN'
You're evaluating a business opportunity. The potential upside is exciting. The people involved are people you like.

Notice: you've just been compromised.

## Motivated Reasoning

Motivated reasoning is the tendency to arrive at conclusions you want to be true, using reasoning that looks objective but isn't.

It works like this:

1. You have a preference (conscious or not) for a particular conclusion
2. You evaluate evidence favoring that conclusion less critically
3. You evaluate evidence against it more critically
4. You arrive at your preferred conclusion, feeling objective the whole time

The most dangerous part: it's invisible from the inside. Motivated reasoning feels like reasoning.

## The Warning Signs

Watch for these internal signals:

- **Relief**: When a conclusion feels like a relief, you probably wanted it
- **Certainty**: Suspiciously high confidence on questions where you have stakes
- **Easy dismissal**: Counter-evidence seems obviously flawed
- **Anger at challengers**: Getting defensive about your conclusion

These don't prove you're wrong. They prove you need to check harder.

## The Antidotes

**1. Name your preference**: Before analyzing anything, explicitly state what you want to be true. This makes the bias visible.

**2. Argue the other side**: Force yourself to make the strongest case against your preferred conclusion. If you can't, you haven't understood the issue.

**3. Consider the outside view**: What would you think if you had no stake in this? What would you tell a friend in the same situation?

**4. Delay**: Let important decisions sit. Initial reactions are most biased. Time creates distance.

## The Paradox

The smarter you are, the more dangerous motivated reasoning becomes. Intelligence doesn't protect against bias—it just makes you better at constructing sophisticated justifications for what you wanted to believe anyway.
MARKDOWN,
                'position' => 3,
            ],
            [
                'name' => 'How Often Does This Actually Happen?',
                'slug' => 'how-often-does-this-happen',
                'summary' => 'Before analyzing the specific case, ask about the general frequency. Base rates are boring but essential.',
                'content' => <<<'MARKDOWN'
Someone fits your mental profile of a threat. Should you be worried?

Before you dive into analyzing the specific signals, ask a different question: How often are people who seem like threats actually threats?

That's the base rate. And ignoring it is one of the most common thinking errors.

## The Base Rate Neglect

We naturally focus on specific, vivid information and ignore general statistical information. This is base rate neglect.

Classic example: A person is described as "shy, withdrawn, helpful, with a need for order." Is this person more likely a librarian or a farmer?

Most people say librarian. But there are vastly more farmers than librarians. The base rate matters more than the description.

## Why It Matters

Base rate neglect leads to:

- **False positives**: Treating rare events as likely because specific evidence is vivid
- **Overreaction**: Responding to risk based on fear rather than frequency
- **Poor prediction**: Expecting outcomes that are actually uncommon

When you hear hoofbeats, think horses, not zebras. Unless you're in Africa.

## The Integration

Good thinking integrates base rates with specific information:

1. **Start with the base rate**: "How often does this happen in general?"
2. **Adjust for specifics**: "Given this specific information, how much should I update?"
3. **Avoid overcorrection**: Specific information rarely justifies huge jumps from base rate

If something happens 1% of the time, and you see a suggestive sign, you shouldn't jump to 90% confidence. Maybe 5%. Maybe 10%. The base rate is an anchor, and you need strong evidence to move far from it.

## The Discipline

Before analyzing any specific situation, force yourself to ask:

- What's the base rate for this outcome?
- How often do situations like this turn out this way?
- If I had no specific information, what would I predict?

Then adjust from there. Not from zero.
MARKDOWN,
                'position' => 4,
            ],
            [
                'name' => 'What Would Change Your Mind?',
                'slug' => 'what-would-change-your-mind',
                'summary' => 'If nothing could change your mind, you\'re not reasoning—you\'re rationalizing. Real beliefs make testable predictions.',
                'content' => <<<'MARKDOWN'
Here's a question that separates real thinking from fake thinking:

"What evidence would change your mind?"

If you can't answer that, your belief isn't a conclusion. It's a commitment.

## The Falsifiability Test

A belief that can't be proven wrong isn't really being held based on evidence. It's being held despite evidence.

Real beliefs make predictions. If the belief is true, you'd expect to see certain things. If you don't see them, the belief should weaken.

Pseudo-beliefs explain everything. Good news confirms them. Bad news confirms them. No evidence could ever count against them.

## The Trap of "Yes, But..."

Watch for this pattern in your own thinking:

Evidence for your view: "See, I was right."
Evidence against your view: "Yes, but that's because [special circumstance]."

When you find yourself explaining away counter-evidence repeatedly, ask: Am I updating, or am I defending?

## The Counterfactual Exercise

Before committing to a conclusion, explicitly ask:

1. **What would I expect to see if I'm right?** (Make it specific)
2. **What would I expect to see if I'm wrong?** (Also specific)
3. **Which am I actually seeing?** (Be honest)

If you're only looking for #1 and ignoring #2, you're not testing your belief. You're collecting confirmation.

## The Courage Required

Truly asking "what would change my mind?" requires intellectual courage. You might find the answer, and then you'd have to actually change your mind.

Most people would rather not know. They protect their beliefs by never asking the question.

But a belief worth holding can survive examination. And a belief that can't survive examination wasn't worth holding in the first place.
MARKDOWN,
                'position' => 5,
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
            ['slug' => 'think-straight'],
            [
                'name' => 'Think Straight',
                'tagline' => 'Stop outsmarting yourself',
                'description' => 'Your brain lies to you constantly. Cognitive biases, logical fallacies, motivated reasoning—they\'re running in the background of every decision you make. Learn to catch yourself before you fool yourself.',
                'instruction_set' => $this->getInstructionSet(),
                'config' => [
                    'input_character_limit' => 800,
                    'reflection_character_limit' => 300,
                    'max_response_tokens' => 1000,
                    'max_history_exchanges' => 6,
                    'model' => 'claude-sonnet-4-20250514',
                ],
                'required_plan' => 'free',
                'icon' => 'Brain',
                'is_active' => true,
                'sort_order' => 50,
            ]
        );

        $this->seedRequiredContext($mode);
        $this->seedDrills($mode);
    }

    private function getInstructionSet(): string
    {
        return <<<'INSTRUCTION'
You are a critical thinking coach helping users identify and overcome cognitive biases, logical fallacies, and motivated reasoning. Your role is to present scenarios that test their ability to think clearly and catch errors in reasoning—including their own.

## User Context
The user is a {{career_level}} {{job_title}} working in {{industry}}. They work in a {{team_composition}} environment.

## Difficulty Level
Current level: {{level}}

Level scaling:
- Levels 1-2: Clear examples of single biases or fallacies. You name the error explicitly and explain the correction. User needs prompting to examine their own reasoning.
- Levels 3-4: Multiple layered reasoning errors or fallacies disguised in reasonable-sounding arguments. You ask "what might you be missing?" before revealing.
- Levels 5-6: Sophisticated arguments with well-hidden flaws. Situations where the user's own preferences might be at stake. You assume familiarity with common biases.
- Levels 7-8: Complex scenarios requiring integration of multiple clear-thinking skills. You challenge their reasoning directly.
- Levels 9-10: Scenarios where reasonable people disagree, testing calibration and intellectual humility. You assume fluency and use confrontational coaching.

## Coaching Approach
1. Present scenarios that contain reasoning errors relevant to the user's context
2. Evaluate responses for bias recognition and logical rigor, not just "correct" answers
3. Challenge conclusions that arrive too easily at preferred outcomes
4. Push users to steelman opposing views before dismissing them
5. Test confidence calibration—are they as certain as they should be?
6. Reward intellectual humility and genuine uncertainty

## Key Principles
- Biases feel like clear thinking from the inside
- Fallacies can be valid-sounding while being logically broken
- The smarter you are, the better you are at rationalizing
- Confidence should track evidence quality
- "What would change my mind?" is the key question

## Blind Spot Categories to Watch For
- Confirmation bias — seeks evidence supporting existing belief
- Availability bias — overweights recent or vivid examples
- Anchoring — over-relies on first piece of information
- Sunk cost reasoning — factors in unrecoverable investments
- Hindsight bias — believes past events were predictable
- Attribution error — explains own behavior situationally, others' dispositionally
- False dichotomy — sees only two options when more exist
- Appeal to authority — accepts conclusion because of source, not evidence
- Correlation/causation confusion — assumes connection implies cause
- Overconfidence — certainty exceeds evidence
- Underconfidence — hedges when evidence is strong
- Motivated reasoning — arrives at preferred conclusion regardless of evidence
- Ad hominem deflection — attacks source instead of argument
- Strawmanning — weakens opposing argument before addressing it
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
                'name' => 'Bias Spotter',
                'position' => 0,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['bias_recognition', 'motivated_reasoning_awareness', 'source_separation'],
                'primary_insight_slug' => 'your-brains-cheat-codes',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a cognitive bias identification scenario appropriate for the user's level and context.

Create a situation where one or more cognitive biases are influencing someone's reasoning. The bias should be embedded in realistic thinking, not cartoonishly obvious.

Include:
- A decision or judgment being made
- The reasoning being used to justify it
- Enough context to identify what bias is operating
- Relevance to the user's professional context

At higher levels, add:
- Multiple interacting biases
- Biases disguised as reasonable heuristics
- Situations where the user might share the bias
- Cases where the biased conclusion happens to be right (but for wrong reasons)

Format your response as JSON:
{
    "scenario": "[The situation and decision being made]\n\n[The reasoning being offered]\n\n[Relevant context and information]",
    "task": "What cognitive bias or biases are at play here? Name them, explain how they're operating, and describe what unbiased reasoning would look like. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's bias identification.

Check for:
- Correct identification: Did they name the right bias(es)?
- Mechanism explanation: Did they explain HOW the bias is operating, not just name it?
- Debiasing: Did they describe what better reasoning would look like?
- Nuance: Did they avoid over-diagnosing or seeing bias everywhere?

Red flags:
- Naming biases without explaining the mechanism
- Missing obvious biases that are present
- Calling valid reasoning "biased" without justification
- Failing to describe the alternative

At higher levels, also check:
- Did they catch subtle or interacting biases?
- Did they note when the biased conclusion might still be correct?
- Did they demonstrate awareness of their own potential bias in assessment?

Quote their analysis and provide specific feedback on what they caught and missed.
PROMPT,
            ],
            [
                'name' => 'Fallacy Finder',
                'position' => 1,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['fallacy_detection', 'evidence_evaluation', 'counterfactual_thinking'],
                'primary_insight_slug' => 'the-logic-trap',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a logical fallacy detection scenario appropriate for the user's level and context.

Create an argument that contains one or more logical fallacies. The argument should sound persuasive despite being logically flawed.

Include:
- A clear conclusion being argued for
- The reasoning/argument being used
- Context that makes the argument seem plausible
- Relevance to the user's professional domain

At higher levels, add:
- Multiple fallacies in one argument
- Sophisticated versions of common fallacies
- Fallacies embedded in technically correct premises
- Arguments where the conclusion is true but the reasoning is still fallacious

Format your response as JSON:
{
    "scenario": "[Context for the argument]\n\n[The argument being made, with its reasoning]\n\n[Why this might seem convincing]",
    "task": "What logical fallacy or fallacies are present in this argument? Name them, explain why the logic fails, and describe what a valid argument for this conclusion would need. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's fallacy detection.

Check for:
- Correct identification: Did they name the right fallacy(ies)?
- Logic explanation: Did they explain WHY the reasoning is invalid?
- Structure focus: Did they focus on logical structure, not just disagreement with conclusion?
- Reconstruction: Did they describe what valid reasoning would require?

Red flags:
- Naming fallacies without explaining the logical flaw
- Confusing "argument I disagree with" with "fallacious argument"
- Missing obvious fallacies
- Calling valid reasoning fallacious

At higher levels, also check:
- Did they catch subtle or combined fallacies?
- Did they note when the conclusion might still be true despite bad reasoning?
- Did they distinguish between formal and informal fallacies appropriately?

Quote their analysis and explain what they got right or wrong about the logic.
PROMPT,
            ],
            [
                'name' => 'Evidence Audit',
                'position' => 2,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['evidence_evaluation', 'base_rate_consideration', 'confidence_calibration'],
                'primary_insight_slug' => 'evidence-is-not-proof',
                'scenario_instruction_set' => <<<'PROMPT'
Generate an evidence evaluation scenario appropriate for the user's level and context.

Create a situation where evidence is being used to support a conclusion, but the evidence has problems (quality, relevance, sufficiency, or representativeness).

Include:
- A claim being made
- The evidence being offered to support it
- Context about where the evidence came from
- Stakes for getting the evaluation right

At higher levels, add:
- Mixed quality evidence (some good, some bad)
- Evidence that's been cherry-picked from a larger body
- Situations where the evidence is real but insufficient
- Cases requiring base rate integration

Format your response as JSON:
{
    "scenario": "[The claim being made]\n\n[The evidence being offered]\n\n[Context about source and stakes]",
    "task": "Evaluate this evidence. Is it reliable, relevant, and sufficient to support the conclusion? What's missing? What would change your assessment? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's evidence assessment.

Check for:
- Quality assessment: Did they evaluate the reliability of the evidence?
- Relevance check: Did they assess whether it actually bears on the claim?
- Sufficiency judgment: Did they consider whether it's enough evidence?
- Missing evidence: Did they identify what would strengthen or weaken the case?

Red flags:
- Accepting evidence uncritically because it supports the conclusion
- Rejecting evidence uncritically because it doesn't
- Ignoring sample size, methodology, or source quality
- Treating "some evidence" as "proof"

At higher levels, also check:
- Did they consider base rates?
- Did they assess whether the evidence might be cherry-picked?
- Did they note what confidence level the evidence warrants?

Quote their evaluation and explain what they assessed well or poorly.
PROMPT,
            ],
            [
                'name' => 'Steel Man Challenge',
                'position' => 3,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'dimensions' => ['counterfactual_thinking', 'motivated_reasoning_awareness', 'source_separation'],
                'primary_insight_slug' => 'wanting-makes-you-stupid',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a steel man challenge scenario appropriate for the user's level and context.

Present a position that the user is likely to initially disagree with, and ask them to construct the strongest possible version of that argument.

Include:
- A claim or position that's controversial or counter-intuitive
- Some context for why someone might hold this view
- A weak version of the argument that would be easy to dismiss
- Relevance to the user's professional domain

At higher levels, add:
- Positions that are genuinely unpopular or uncomfortable
- Views that conflict with the user's likely priors
- Topics where motivated reasoning is common
- Complex positions with multiple facets

Format your response as JSON:
{
    "scenario": "[The position to steel man]\n\n[A weak version of the argument someone might make]\n\n[Context for why this matters]",
    "task": "Steel man this position. Construct the strongest possible version of this argument—one that a thoughtful proponent would recognize as fair. What's the best case for this view? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's steel man attempt.

Check for:
- Genuine engagement: Did they actually try to make the strongest case, or did they subtly undermine it?
- Accuracy: Would a proponent of this view recognize their representation as fair?
- Strength: Did they find the strongest arguments, not just the most common ones?
- Intellectual honesty: Did they resist the urge to insert dismissals or caveats?

Red flags:
- Subtle straw-manning disguised as steel-manning
- Including dismissive language ("Some might say..., but...")
- Finding only the weakest or most mockable versions
- Refusing to engage with the strongest points

At higher levels, also check:
- Did they identify arguments that even they found somewhat persuasive?
- Did they discover any genuine insight in the opposing position?
- Did they resist the urge to immediately rebut?

Quote their steel man and assess its strength and fairness.
PROMPT,
            ],
            [
                'name' => 'Confidence Calibration',
                'position' => 4,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['confidence_calibration', 'evidence_evaluation', 'base_rate_consideration'],
                'primary_insight_slug' => 'how-often-does-this-happen',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a confidence calibration scenario appropriate for the user's level and context.

Present a question or prediction where the user must assess their confidence level and justify it based on available evidence.

Include:
- A specific claim or prediction to evaluate
- Relevant evidence (but not conclusive)
- Context that might affect confidence
- Stakes for being wrong in either direction (over or underconfident)

At higher levels, add:
- Situations where common knowledge is wrong
- Cases requiring base rate integration
- Domains where experts are often overconfident
- Questions with genuine uncertainty

Format your response as JSON:
{
    "scenario": "[The claim or prediction to evaluate]\n\n[Available evidence]\n\n[Context and stakes]",
    "task": "How confident should you be in this claim? Give a percentage and justify it. What would make you more or less confident? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's confidence calibration.

Check for:
- Reasonable probability: Is their confidence level appropriate given the evidence?
- Justification: Did they explain why this level and not higher/lower?
- Evidence linkage: Did they connect confidence to specific evidence quality?
- Updating criteria: Did they identify what would change their confidence?

Red flags:
- Extreme confidence (90%+) without strong evidence
- False precision (exactly 73%) without justification
- Confidence that doesn't track evidence quality
- Inability to articulate what would change their mind

At higher levels, also check:
- Did they consider base rates?
- Did they account for domains where they might be systematically biased?
- Did they express appropriate uncertainty about their uncertainty?

Quote their confidence assessment and evaluate its calibration.
PROMPT,
            ],
            [
                'name' => 'What Am I Missing?',
                'position' => 5,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'dimensions' => ['counterfactual_thinking', 'bias_recognition', 'motivated_reasoning_awareness'],
                'primary_insight_slug' => 'what-would-change-your-mind',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a "what am I missing?" scenario appropriate for the user's level and context.

Present a situation where the user has reached a conclusion, and challenge them to identify what they might be missing or getting wrong.

Include:
- A decision or conclusion that seems reasonable
- The reasoning behind it
- Context that might hide important considerations
- Stakes that make blind spots costly

At higher levels, add:
- Conclusions that align with the user's likely preferences
- Situations with hidden information asymmetries
- Cases where the obvious answer is wrong
- Scenarios requiring multiple perspective-taking

Format your response as JSON:
{
    "scenario": "[The situation and the conclusion reached]\n\n[The reasoning used]\n\n[Context and stakes]",
    "task": "What might be missing from this analysis? What assumptions haven't been examined? What evidence would change this conclusion? Audit the reasoning. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's self-audit.

Check for:
- Genuine critique: Did they find real weaknesses, not just token ones?
- Assumption identification: Did they surface hidden assumptions?
- Alternative generation: Did they consider other explanations or conclusions?
- Falsifiability: Did they identify what would change the conclusion?

Red flags:
- Surface-level critique that doesn't challenge the core
- Defending the conclusion while pretending to critique it
- Missing obvious weaknesses
- Unable to generate genuine alternatives

At higher levels, also check:
- Did they identify assumptions they themselves likely share?
- Did they find weaknesses even in reasoning they agree with?
- Did they demonstrate genuine intellectual humility?

Quote their self-audit and assess its depth and honesty.
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
