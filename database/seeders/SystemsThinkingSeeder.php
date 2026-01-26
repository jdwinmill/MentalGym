<?php

namespace Database\Seeders;

use App\Models\Drill;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\Principle;
use App\Models\SkillDimension;
use Illuminate\Database\Seeder;

class SystemsThinkingSeeder extends Seeder
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
                'key' => 'feedback_loop_recognition',
                'label' => 'Feedback Loop Recognition',
                'description' => 'Ability to identify reinforcing and balancing loops in a system, understanding how outputs become inputs that amplify or stabilize behavior.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Sees events as isolated; misses circular causality entirely',
                    'mid' => 'Recognizes obvious loops when prompted; may miss subtle or delayed feedback',
                    'high' => 'Spontaneously identifies both reinforcing and balancing loops; understands their interactions',
                    'exemplary' => 'Maps complex nested loops; predicts how loop dynamics will evolve over time',
                ],
                'improvement_tips' => [
                    'low' => 'When something grows or shrinks over time, ask: "What feeds this trend?" Look for outputs that become inputs.',
                    'mid' => 'Practice distinguishing reinforcing loops (snowball effects) from balancing loops (thermostats). Ask: "Does this amplify or stabilize?"',
                    'high' => 'Map competing loops in the same system. Identify which loop dominates under different conditions.',
                ],
            ],
            [
                'key' => 'delay_awareness',
                'label' => 'Delay Awareness',
                'description' => 'Recognizing when consequences are delayed and accounting for lag between action and effect in system behavior.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Expects immediate results; frustrated when interventions don\'t work instantly',
                    'mid' => 'Acknowledges delays exist but struggles to estimate their duration or impact',
                    'high' => 'Factors delays into analysis; avoids overreaction during lag periods',
                    'exemplary' => 'Anticipates how delays create oscillation and instability; designs interventions that account for timing',
                ],
                'improvement_tips' => [
                    'low' => 'For any intervention, ask: "When will I see results?" Consider days, weeks, months—not just immediate effects.',
                    'mid' => 'Watch for oscillation patterns (boom-bust cycles). These often signal delays causing overreaction.',
                    'high' => 'Map the delay structure of your system. Identify which delays are fixed vs. variable, and how they interact.',
                ],
            ],
            [
                'key' => 'leverage_point_identification',
                'label' => 'Leverage Point Identification',
                'description' => 'Finding small interventions that create disproportionate system-wide change by targeting high-impact nodes.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Focuses on obvious symptoms; applies brute force to problems',
                    'mid' => 'Understands leverage exists but struggles to identify where it lies',
                    'high' => 'Identifies non-obvious intervention points; prioritizes actions by systemic impact',
                    'exemplary' => 'Finds leverage at the level of goals, paradigms, and system structure—not just parameters',
                ],
                'improvement_tips' => [
                    'low' => 'Before acting, ask: "Where would a small change create the biggest ripple?" Resist the urge to push harder on the obvious.',
                    'mid' => 'Learn Donella Meadows\' leverage points hierarchy. Higher leverage often lies in rules and goals, not just numbers.',
                    'high' => 'Look for places where information flows are blocked or distorted. Improving feedback often has more leverage than direct intervention.',
                ],
            ],
            [
                'key' => 'local_vs_global_optimization',
                'label' => 'Local vs. Global Optimization',
                'description' => 'Recognizing when optimizing one part of a system harms the whole, and balancing component performance with system health.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Optimizes own area without considering system effects; surprised by downstream problems',
                    'mid' => 'Aware of tradeoffs but struggles to quantify or prioritize them',
                    'high' => 'Consistently evaluates local decisions against global impact; willing to suboptimize locally for system benefit',
                    'exemplary' => 'Designs incentives and structures that align local and global optimization naturally',
                ],
                'improvement_tips' => [
                    'low' => 'For every local improvement, ask: "What might get worse elsewhere?" Trace the connections.',
                    'mid' => 'Map who else depends on the resources or outputs you\'re optimizing. Their constraints matter.',
                    'high' => 'Look for ways to change the game—redesign boundaries or incentives so local optimization serves global goals.',
                ],
            ],
            [
                'key' => 'side_effect_anticipation',
                'label' => 'Side Effect Anticipation',
                'description' => 'Predicting unintended consequences of interventions by tracing how changes propagate through connected elements.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Surprised by unintended consequences; treats side effects as random bad luck',
                    'mid' => 'Considers some second-order effects but misses non-obvious pathways',
                    'high' => 'Systematically traces intervention effects through multiple pathways; anticipates common failure modes',
                    'exemplary' => 'Predicts third-order effects and interaction effects; designs interventions with side effects in mind',
                ],
                'improvement_tips' => [
                    'low' => 'After proposing any solution, force yourself to list three possible negative consequences. Assume interconnection.',
                    'mid' => 'Use "and then what?" thinking. Trace each effect forward two or three steps.',
                    'high' => 'Consider not just direct effects but behavioral responses. How will people adapt to your intervention?',
                ],
            ],
            [
                'key' => 'root_cause_distinction',
                'label' => 'Root Cause vs. Symptom Distinction',
                'description' => 'Differentiating between surface problems and underlying structural issues that generate recurring patterns.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Treats symptoms as the problem; fixes recur because root cause remains',
                    'mid' => 'Asks "why" but may stop too early; identifies proximate but not ultimate causes',
                    'high' => 'Consistently digs to structural causes; distinguishes event, pattern, and structure levels',
                    'exemplary' => 'Sees how mental models and system structures generate the patterns that produce events',
                ],
                'improvement_tips' => [
                    'low' => 'When a problem recurs, stop fixing it and ask: "Why does this keep happening?" The recurrence IS the clue.',
                    'mid' => 'Use the "Five Whys" but go beyond. Ask: "What structure makes this outcome likely?"',
                    'high' => 'Distinguish three levels: events (what happened), patterns (what keeps happening), structure (what causes the pattern).',
                ],
            ],
            [
                'key' => 'system_boundary_recognition',
                'label' => 'System Boundary Recognition',
                'description' => 'Knowing what to include and exclude when defining a system, drawing boundaries that capture essential dynamics without unnecessary complexity.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Draws boundaries arbitrarily; misses key actors or includes irrelevant noise',
                    'mid' => 'Reasonable boundaries but may miss important external dependencies or include too much',
                    'high' => 'Draws boundaries that capture essential dynamics; knows when to expand or contract scope',
                    'exemplary' => 'Fluidly adjusts boundaries based on the question being asked; sees systems nested within systems',
                ],
                'improvement_tips' => [
                    'low' => 'Ask: "Who or what influences this outcome?" and "Who or what is affected?" Include those in your system.',
                    'mid' => 'Test your boundaries by asking: "What important dynamics would I miss if I stopped here?"',
                    'high' => 'Practice zooming in and out. The right boundary depends on the question—be explicit about what you\'re including and why.',
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
            ['slug' => 'systems-thinking'],
            [
                'name' => 'Systems Thinking',
                'slug' => 'systems-thinking',
                'description' => 'Learn to see the whole system, not just the parts. Understand feedback loops, delays, and leverage points to solve problems at their root instead of chasing symptoms.',
                'icon' => 'git-fork',
                'position' => 10,
                'is_active' => true,
                'blog_urls' => null,
            ]
        );
    }

    private function seedInsights(): void
    {
        $principle = Principle::where('slug', 'systems-thinking')->first();

        if (! $principle) {
            return;
        }

        $insights = [
            [
                'name' => 'The System Fights Back',
                'slug' => 'the-system-fights-back',
                'summary' => 'Every intervention triggers a response. Systems have built-in resistance to change—and your "fix" often becomes part of the problem.',
                'content' => <<<'MARKDOWN'
You push on a system. The system pushes back.

This is one of the most fundamental—and most ignored—principles of systems thinking. Every intervention triggers responses that partially or completely offset your intended effect.

## Why Systems Resist

Systems persist because they have balancing feedback loops. These loops exist to maintain stability. When you intervene, you're disrupting that stability, and the system will try to restore it.

Push productivity metrics, and people game the metrics.
Crack down on one distribution channel, and the market shifts to another.
Add capacity to a road, and more people drive until it's congested again.

This isn't malice or stupidity. It's how systems work.

## The Intervention Trap

Here's where it gets insidious: when your intervention doesn't work, the natural response is to push harder. More pressure. More resources. More enforcement.

But pushing harder on a resisting system often makes the resistance stronger. You enter an arms race against the system's balancing mechanisms.

The road gets wider. More people drive. Congestion returns. The road gets wider again.

## Finding the Way Out

Instead of overpowering resistance, look for interventions that work *with* system dynamics:

1. **Change the feedback** — If people game metrics, change what you measure
2. **Shift incentives** — Align individual benefit with system benefit
3. **Remove blockages** — Sometimes systems resist because information isn't flowing

The goal isn't to defeat the system. It's to redirect it.

## The Question to Ask

Before any intervention: "How will the system respond to this? What balancing loops will I trigger?"

If you can't answer that, you're not ready to intervene.
MARKDOWN,
                'position' => 0,
            ],
            [
                'name' => 'Delays Change Everything',
                'slug' => 'delays-change-everything',
                'summary' => 'The gap between action and consequence is where most system failures live. Ignore delays and you\'ll overshoot, oscillate, and overcorrect.',
                'content' => <<<'MARKDOWN'
You turn on the hot water. Nothing happens. You turn it more. Still cold. You crank it all the way—and then scald yourself.

That's delay in action.

## The Delay Problem

Most of the systems we work with have significant delays between action and result:

- Hire someone today, see their impact in months
- Launch a marketing campaign, measure results in quarters
- Change a process, observe cultural effects in years

When we ignore these delays, we make predictable mistakes.

## The Oscillation Pattern

Here's what delay-blindness looks like:

1. You intervene
2. Nothing seems to happen (you're in the delay period)
3. You intervene more aggressively
4. The results of intervention #1 finally arrive
5. Combined with intervention #2, you overshoot
6. You correct in the opposite direction
7. Repeat, with growing oscillations

This is why systems swing between boom and bust, crackdown and neglect, hiring spree and layoff.

## The Patience Discipline

The antidote is uncomfortable: patience and restraint.

Before adding to your intervention:
- Have you waited long enough for the first action to show results?
- Is the delay inherent to the system, or a sign that the intervention isn't working?
- What's the cost of waiting versus the cost of overcorrecting?

## Mapping Delays

For any system you work with, map the major delays:

- How long between action and first visible result?
- How long until full effect materializes?
- Are there multiple delays that compound?

This map is your guide to intervention timing. Without it, you're flying blind.
MARKDOWN,
                'position' => 1,
            ],
            [
                'name' => 'The Symptom Is Not the Problem',
                'slug' => 'symptom-is-not-the-problem',
                'summary' => 'When you treat symptoms, they come back. The recurring fix is a sign that you\'re working at the wrong level.',
                'content' => <<<'MARKDOWN'
Every month, the same fire drill. Every quarter, the same scramble. Every year, the same "unexpected" crisis.

If you're fixing the same problem repeatedly, you're not fixing the problem. You're managing symptoms.

## The Three Levels

Systems thinkers distinguish three levels of understanding:

**Events**: What happened. The visible incident.
"We missed the deadline."

**Patterns**: What keeps happening. The trend over time.
"We miss deadlines every release cycle."

**Structure**: What causes the pattern. The underlying system.
"Our estimation process ignores integration time, and we have no slack in schedules."

Most problem-solving happens at the event level. That's symptom treatment.

## The Structural Fix

Structural problems require structural solutions. But structural solutions are:

- Harder to see (they require pattern recognition)
- Harder to implement (they require changing how things work)
- Slower to show results (structure changes don't produce instant wins)

So we default to event-level fixes. They're visible, immediate, and feel productive.

And the problem comes back.

## The Recurrence Test

Here's a simple diagnostic: **Is this problem recurring?**

If yes, you've been treating symptoms. The recurrence is evidence that the structure generating the problem is still intact.

Stop. Step back. Ask: "What structure makes this problem likely to happen?"

That's where your intervention belongs.

## The Courage Required

Structural fixes often mean:
- Changing processes people are used to
- Addressing incentives that benefit powerful people
- Admitting that past "solutions" didn't work

This is why symptoms get treated and root causes persist. Structural change is politically harder.

But it's the only change that sticks.
MARKDOWN,
                'position' => 2,
            ],
            [
                'name' => 'Small Changes, Large Effects',
                'slug' => 'small-changes-large-effects',
                'summary' => 'The highest-leverage interventions are rarely obvious. They\'re small shifts in the right place—not brute force applied to symptoms.',
                'content' => <<<'MARKDOWN'
We tend to believe that big problems require big solutions. More resources. More people. More force.

Systems thinking reveals a different truth: the most powerful interventions are often small changes in the right place.

## The Leverage Hierarchy

Donella Meadows identified a hierarchy of leverage points, from weakest to strongest:

**Weakest:**
- Numbers (budgets, quotas, metrics)
- Buffers (inventory, reserves, slack)

**Medium:**
- Feedback loops (what information flows where)
- Rules (incentives, constraints, permissions)

**Strongest:**
- Goals (what the system is trying to achieve)
- Paradigms (the mental models that created the system)

Most interventions happen at the bottom—adjusting numbers. The leverage is at the top.

## Why We Miss Leverage

High-leverage points are counterintuitive. They often look like:

- Doing less, not more
- Changing information flow, not adding resources
- Questioning goals, not optimizing execution

This doesn't feel productive. We're wired to *do something*—preferably something visible and effortful.

But effort at a low-leverage point is just expensive symptom management.

## Finding Leverage

Ask these questions:

1. **What rules or incentives are driving this behavior?** (Often more leverage than changing the behavior directly)

2. **What information is missing or distorted?** (Systems fail when feedback loops are broken)

3. **What goal is this system actually optimizing for?** (Often different from the stated goal)

4. **What assumption does everyone take for granted?** (Paradigm shifts are the highest leverage of all)

## The Patience Tax

High-leverage interventions often have longer delays before results show. Changing a paradigm doesn't produce immediate metrics improvement.

This is why they're underused. We optimize for visible, immediate results—and that optimization itself is a low-leverage approach.
MARKDOWN,
                'position' => 3,
            ],
            [
                'name' => 'Your Boundary Is a Choice',
                'slug' => 'your-boundary-is-a-choice',
                'summary' => 'Every system map is a choice about what to include. Draw the boundary wrong and you\'ll miss the dynamics that matter most.',
                'content' => <<<'MARKDOWN'
When you analyze a system, you draw a boundary. Inside the boundary: the system. Outside: the environment.

That boundary isn't given to you. You chose it. And that choice determines everything you'll see and miss.

## The Boundary Problem

Draw the boundary too narrowly:
- You'll miss external factors that drive internal behavior
- Your "solutions" will be undermined by forces you didn't account for
- You'll be perpetually surprised

Draw the boundary too broadly:
- You'll drown in complexity
- Everything connects to everything, and you'll analyze forever
- You'll never act because the problem seems too big

## Common Boundary Mistakes

**Too narrow**: Analyzing a team's performance without including the incentives set by leadership, the tools they're given, or the other teams they depend on.

**Too broad**: Including market dynamics, competitor behavior, macroeconomic trends, and regulatory environment when you're trying to fix a process problem.

## The Boundary Test

Good boundaries share three characteristics:

1. **They include what you can influence.** If it's inside your boundary but outside your control, you can only react to it—consider putting it outside.

2. **They include what influences you significantly.** If external factors dominate your outcomes, your boundary might be too narrow.

3. **They're appropriate for your question.** Different questions require different boundaries. "How do we fix this bug?" and "Why do we keep shipping bugs?" need different system maps.

## Boundaries Are Provisional

The right boundary for exploration might be different from the right boundary for action.

Start broad to understand the full picture. Narrow for implementation. Be explicit about what you're including and excluding, and why.

When someone challenges your analysis, often they're challenging your boundary. Make it visible so the conversation can be productive.
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
            ['slug' => 'systems-thinking'],
            [
                'name' => 'Systems Thinking',
                'tagline' => 'See the whole system, not just the parts',
                'description' => 'Train yourself to see how parts connect—feedback loops, delays, unintended consequences. Stop fixing symptoms and start fixing systems.',
                'instruction_set' => $this->getInstructionSet(),
                'config' => [
                    'input_character_limit' => 800,
                    'reflection_character_limit' => 300,
                    'max_response_tokens' => 1000,
                    'max_history_exchanges' => 6,
                    'model' => 'claude-sonnet-4-20250514',
                ],
                'required_plan' => 'free',
                'icon' => 'GitFork',
                'is_active' => true,
                'sort_order' => 30,
            ]
        );

        $this->seedRequiredContext($mode);
        $this->seedDrills($mode);
    }

    private function getInstructionSet(): string
    {
        return <<<'INSTRUCTION'
You are a systems thinking coach helping users develop the ability to see interconnections, feedback loops, and leverage points in complex situations. Your role is to present system dynamics problems and evaluate the user's ability to think structurally rather than linearly.

## User Context
The user is a {{career_level}} {{job_title}} working in {{industry}}. They work in a {{team_composition}} environment.

## Difficulty Level
Current level: {{level}}

Level scaling:
- Levels 1-2: Simple two-part cause and effect with obvious feedback loops. You explain concepts explicitly. User needs prompting to consider system effects.
- Levels 3-4: Multiple actors with competing incentives. Delayed feedback between action and consequence. You question more than explain.
- Levels 5-6: Reinforcing and balancing loops interact. Local optimization conflicts with global outcomes. You assume basic systems literacy.
- Levels 7-8: Nested systems with non-obvious leverage points. Interventions that backfire. You challenge aggressively and expect sophisticated analysis.
- Levels 9-10: Complex adaptive systems. Multiple viable framings. You assume fluency and probe for paradigm-level thinking.

## Coaching Approach
1. Present realistic scenarios that reveal system dynamics relevant to the user's context
2. Evaluate responses for structural thinking, not just correct answers
3. Challenge linear cause-and-effect thinking when you see it
4. Reward identification of feedback loops, delays, and non-obvious connections
5. Push users to distinguish symptoms from root causes
6. Ask about unintended consequences and system boundaries

## Key Principles
- Every intervention triggers a system response—ask what it is
- Delays between action and consequence cause most system failures
- Local optimization often harms global performance
- The highest leverage is rarely the most obvious intervention
- System boundaries are choices, not givens

## Blind Spot Categories to Watch For
- Treats symptoms while ignoring underlying structure
- Misses reinforcing feedback loops (snowball effects)
- Misses balancing feedback loops (resistance to change)
- Ignores delays between action and consequence
- Optimizes locally at expense of global system health
- Assumes interventions are isolated (misses side effects)
- Draws system boundaries too narrowly or too broadly
- Overcomplicates simple dynamics OR oversimplifies interconnected ones
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
                'name' => 'Map the Feedback',
                'position' => 0,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'dimensions' => ['feedback_loop_recognition', 'root_cause_distinction', 'system_boundary_recognition'],
                'primary_insight_slug' => 'the-system-fights-back',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a feedback loop mapping scenario appropriate for the user's level and context.

Create a situation where something is growing, shrinking, or oscillating over time. The underlying cause should be circular (feedback), not linear.

Include:
- A visible trend or pattern (growth, decline, oscillation)
- Multiple actors or components that influence each other
- At least one non-obvious connection
- Context from the user's industry/role

At higher levels, add:
- Multiple interacting loops (reinforcing AND balancing)
- Delays that obscure the feedback relationship
- Loops that work against each other
- Actors who benefit from the current dynamics

Format your response as JSON:
{
    "scenario": "[The situation with the visible trend]\n\n[Key actors and their behaviors]\n\n[Relevant context and history]",
    "task": "Identify the feedback loop(s) driving this pattern. For each loop: name the key elements, explain how they connect, and state whether it's reinforcing (amplifying) or balancing (stabilizing). 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's feedback loop analysis.

Check for:
- Loop identification: Did they find the circular causality, not just linear cause-effect?
- Completeness: Did they identify the key elements in the loop?
- Classification: Did they correctly identify reinforcing vs. balancing dynamics?
- Non-obvious connections: Did they see beyond the surface?
- Boundary awareness: Did they include the right actors?

Red flags:
- Pure linear thinking ("A causes B causes C" with no circularity)
- Missing the loop that drives the main pattern
- Confusing reinforcing and balancing loops
- Including irrelevant elements while missing key ones
- Stopping at the obvious first-order connections

At higher levels, also check:
- Did they identify multiple interacting loops?
- Did they note which loop dominates?
- Did they account for delays in the feedback?

Quote their analysis when providing feedback. Be specific about what they saw and missed.
PROMPT,
            ],
            [
                'name' => 'Consequence Cascade',
                'position' => 1,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['side_effect_anticipation', 'delay_awareness', 'local_vs_global_optimization'],
                'primary_insight_slug' => 'delays-change-everything',
                'scenario_instruction_set' => <<<'PROMPT'
Generate an intervention analysis scenario appropriate for the user's level and context.

Present a proposed solution or intervention to a problem. The intervention should have non-obvious second and third-order effects.

Include:
- A clear problem being addressed
- A specific proposed intervention
- Enough system context to trace consequences
- Stakeholders who will be affected

At higher levels, add:
- Interventions that seem obviously good but have hidden costs
- Delayed consequences that only emerge later
- Behavioral adaptations by actors in the system
- Tradeoffs between local and global optimization

Format your response as JSON:
{
    "scenario": "[The problem being addressed]\n\n[The proposed intervention]\n\n[Relevant system context and stakeholders]",
    "task": "Analyze this intervention. What are the likely second and third-order effects? Who adapts and how? What might go wrong that isn't obvious? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's consequence analysis.

Check for:
- Second-order thinking: Did they trace effects beyond the immediate?
- Behavioral anticipation: Did they consider how actors will adapt?
- Delay awareness: Did they note when consequences will emerge?
- Local vs. global: Did they check for harm to the broader system?
- Unintended effects: Did they identify non-obvious downsides?

Red flags:
- Only analyzing intended effects
- Assuming people won't change their behavior
- Ignoring delays ("this will immediately...")
- Missing obvious stakeholders who are affected
- Treating the intervention as isolated

At higher levels, also check:
- Did they identify feedback loops the intervention might trigger?
- Did they note how the system might resist?
- Did they consider interaction effects with other system elements?

Quote specific parts of their analysis. Point out both insights and blind spots.
PROMPT,
            ],
            [
                'name' => 'Root or Symptom',
                'position' => 2,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['root_cause_distinction', 'feedback_loop_recognition', 'leverage_point_identification'],
                'primary_insight_slug' => 'symptom-is-not-the-problem',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a root cause analysis scenario appropriate for the user's level and context.

Present a recurring problem or persistent pattern. The visible issue should be a symptom of deeper structural causes.

Include:
- A problem that has been "fixed" multiple times
- The fixes that have been tried
- Pattern of recurrence
- Hints about deeper structure

At higher levels, add:
- Multiple possible root causes at different levels
- Structural causes embedded in incentives or mental models
- Root causes that benefit powerful stakeholders
- Situations where the "fix" has become part of the problem

Format your response as JSON:
{
    "scenario": "[The recurring problem]\n\n[History of attempted fixes]\n\n[Pattern of how it keeps coming back]",
    "task": "What's the root cause? Distinguish the symptom (what keeps happening) from the structure (what causes the pattern). Explain what would need to change for the problem to stop recurring. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's root cause analysis.

Check for:
- Level distinction: Did they separate event/pattern/structure levels?
- Structural insight: Did they identify the underlying mechanism, not just proximate cause?
- Loop thinking: Did they see what perpetuates the pattern?
- Actionability: Did they point to what could actually change?
- Depth: Did they go beyond the obvious "five whys"?

Red flags:
- Treating the symptom as the root cause
- Stopping at proximate causes (one "why" deep)
- Proposing more of the same fixes that already failed
- Missing feedback loops that regenerate the problem
- Blaming individuals rather than identifying structure

At higher levels, also check:
- Did they identify structural causes at the incentive or mental model level?
- Did they note who benefits from the current structure?
- Did they explain why past fixes didn't address root cause?

Be specific about where their analysis stopped and where it should have gone deeper.
PROMPT,
            ],
            [
                'name' => 'Find the Leverage',
                'position' => 3,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'dimensions' => ['leverage_point_identification', 'local_vs_global_optimization', 'side_effect_anticipation'],
                'primary_insight_slug' => 'small-changes-large-effects',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a leverage point identification scenario appropriate for the user's level and context.

Present a complex problem where brute-force solutions are tempting but inefficient. There should be high-leverage intervention points that aren't immediately obvious.

Include:
- A significant problem with multiple possible intervention points
- Context showing where effort is currently being applied
- Information about system structure (incentives, information flows, goals)
- Constraints on resources

At higher levels, add:
- Situations where the obvious intervention has low leverage
- Leverage at the level of rules, goals, or paradigms
- Trade-offs between different leverage points
- Political or cultural barriers to high-leverage interventions

Format your response as JSON:
{
    "scenario": "[The problem and its significance]\n\n[Current efforts and why they're struggling]\n\n[Relevant system structure and constraints]",
    "task": "Identify the highest-leverage intervention point. Where would a small change create disproportionate impact? Explain why this point has more leverage than the obvious alternatives. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's leverage point identification.

Check for:
- Non-obvious insight: Did they look beyond the symptom-level interventions?
- Leverage reasoning: Did they explain WHY this point has high leverage?
- System awareness: Did they consider feedback loops, information flows, or incentives?
- Proportionality: Does the intervention size match the expected impact?
- Feasibility: Is the intervention actually possible?

Red flags:
- Defaulting to "more resources" or "try harder"
- Focusing only on parameter changes (numbers) when rules or goals are the issue
- Missing leverage in information flows or feedback
- Proposing interventions without explaining the leverage mechanism
- Choosing politically easy over systemically effective

At higher levels, also check:
- Did they consider leverage at the goal or paradigm level?
- Did they address why high-leverage points are often underutilized?
- Did they anticipate resistance to the high-leverage intervention?

Quote their reasoning and evaluate the quality of their leverage logic.
PROMPT,
            ],
            [
                'name' => 'Boundary Check',
                'position' => 4,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['system_boundary_recognition', 'side_effect_anticipation', 'local_vs_global_optimization'],
                'primary_insight_slug' => 'your-boundary-is-a-choice',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a system boundary analysis scenario appropriate for the user's level and context.

Present a problem analysis or proposed solution that has a questionable system boundary—either too narrow (missing important dynamics) or too broad (drowning in complexity).

Include:
- A problem framing with an implicit boundary
- Information about what's included and excluded
- Hints about important factors outside the current boundary
- Context about the goal of the analysis

At higher levels, add:
- Nested systems where boundary choice significantly affects conclusions
- External factors that dominate internal dynamics
- Stakeholders who are affected but excluded from analysis
- Cases where the "right" boundary depends on the question being asked

Format your response as JSON:
{
    "scenario": "[The problem as currently framed]\n\n[What's included in the current analysis]\n\n[Context about related factors and stakeholders]",
    "task": "Evaluate the system boundary. Is it too narrow, too broad, or appropriate? What should be added or removed? How does the boundary choice affect the conclusions? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's boundary analysis.

Check for:
- Boundary identification: Did they recognize what's currently in/out?
- Critical assessment: Did they evaluate whether the boundary serves the goal?
- Specific recommendations: Did they say what should be added or removed and why?
- Goal awareness: Did they connect boundary choice to the question being asked?
- Trade-off recognition: Did they acknowledge the cost of expanding/contracting?

Red flags:
- Accepting the boundary as given rather than as a choice
- Suggesting expansion without considering complexity costs
- Missing obviously important external factors
- Including everything without prioritization
- Not connecting boundary to the analytical goal

At higher levels, also check:
- Did they note how different boundaries would lead to different conclusions?
- Did they identify nested systems or multiple relevant boundaries?
- Did they consider which stakeholders' interests the boundary serves?

Be specific about what their boundary assessment caught and missed.
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
