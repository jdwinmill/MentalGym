<?php

namespace Database\Seeders;

use App\Models\Drill;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\Principle;
use App\Models\SkillDimension;
use Illuminate\Database\Seeder;

class ReadTheRoomSeeder extends Seeder
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
                'key' => 'audience_reading',
                'label' => 'Audience Reading',
                'description' => 'Assessing who you\'re talking to and what they need from the conversation—their priorities, constraints, communication preferences, and current state.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Communicates the same way regardless of audience; surprised when messages don\'t land',
                    'mid' => 'Adjusts for obvious differences but misses subtler cues or individual variation',
                    'high' => 'Reads audiences accurately; picks up on role, context, and individual preferences',
                    'exemplary' => 'Intuits what people need before they say it; reads individuals, not just roles',
                ],
                'improvement_tips' => [
                    'low' => 'Before any important communication, ask: "Who am I talking to? What do they care about? What do they already know?"',
                    'mid' => 'Look past role stereotypes. Not all executives want brevity. Not all engineers want detail. Read the individual.',
                    'high' => 'Notice what\'s not being said. What questions aren\'t they asking? What signals suggest your read might be off?',
                ],
            ],
            [
                'key' => 'detail_calibration',
                'label' => 'Detail Calibration',
                'description' => 'Knowing when to go deep versus stay high-level, matching the depth of information to what the audience needs and can use.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Defaults to one level of detail regardless of audience; either drowns in weeds or stays too shallow',
                    'mid' => 'Adjusts detail level but may miscalibrate—too much for execs, too little for implementers',
                    'high' => 'Matches detail to audience need; layers information so people can go deeper if they want',
                    'exemplary' => 'Provides exactly what\'s needed; reads cues to know when to expand or compress in real-time',
                ],
                'improvement_tips' => [
                    'low' => 'Ask yourself: "What decision does this person need to make?" Give them exactly what they need for that, no more.',
                    'mid' => 'Watch for glazed eyes (too much detail) or follow-up questions (too little). Calibrate from feedback.',
                    'high' => 'Layer your communication: headline first, then offer to go deeper. Let them pull detail rather than pushing it.',
                ],
            ],
            [
                'key' => 'formality_matching',
                'label' => 'Formality Matching',
                'description' => 'Adjusting tone and register to context—knowing when formality signals respect and when it creates distance.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Uses one register regardless of context; either too stiff or too casual',
                    'mid' => 'Adjusts for obvious contexts but may miscalibrate in ambiguous situations',
                    'high' => 'Matches formality to context; knows when to be crisp and when to be warm',
                    'exemplary' => 'Fluidly adjusts within conversations; uses formality strategically to achieve goals',
                ],
                'improvement_tips' => [
                    'low' => 'Mirror the other person\'s tone as a starting point. If they\'re formal, be formal. If they\'re casual, relax.',
                    'mid' => 'Remember: formality can signal respect OR create distance. Casualness can build rapport OR undermine credibility. Context matters.',
                    'high' => 'Use formality shifts intentionally. Going more formal can signal seriousness; going more casual can build alliance.',
                ],
            ],
            [
                'key' => 'translation_ability',
                'label' => 'Translation Ability',
                'description' => 'Conveying the same core message in different ways for different audiences without losing accuracy or impact.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Struggles to reframe messages; stuck in one mode of explanation',
                    'mid' => 'Can translate for obvious differences but loses nuance or accuracy in translation',
                    'high' => 'Translates fluently across contexts; preserves meaning while adapting form',
                    'exemplary' => 'Finds the perfect framing for each audience; makes complex ideas accessible without dumbing down',
                ],
                'improvement_tips' => [
                    'low' => 'Practice explaining the same thing three ways: to a technical expert, to a smart generalist, to a busy executive.',
                    'mid' => 'Check that your translation preserves accuracy. Simplification shouldn\'t mean losing important nuance.',
                    'high' => 'Build a library of analogies and framings. The best translators have multiple ways to explain any concept.',
                ],
            ],
            [
                'key' => 'real_time_adjustment',
                'label' => 'Real-Time Adjustment',
                'description' => 'Recognizing when your approach isn\'t landing and pivoting mid-conversation rather than plowing ahead.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Plows ahead regardless of reception; doesn\'t notice or respond to feedback signals',
                    'mid' => 'Notices when things aren\'t landing but struggles to pivot effectively',
                    'high' => 'Reads feedback signals and adjusts approach smoothly mid-conversation',
                    'exemplary' => 'Anticipates when adjustment is needed; pivots so smoothly the other person barely notices',
                ],
                'improvement_tips' => [
                    'low' => 'Watch for signals: confusion, checking phones, asking clarifying questions, body language shifts. These mean your approach needs adjustment.',
                    'mid' => 'When you notice it\'s not landing, pause and check in: "I\'m not sure I\'m explaining this well. What would be most helpful?"',
                    'high' => 'Build flexibility into your communication. Have multiple ways to make any point so you can pivot without losing momentum.',
                ],
            ],
            [
                'key' => 'priority_alignment',
                'label' => 'Priority Alignment',
                'description' => 'Leading with what matters to the audience, not what matters to you—framing messages around their concerns.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Leads with own priorities and interests; frames everything from own perspective',
                    'mid' => 'Attempts to align but often reverts to own framing under pressure',
                    'high' => 'Consistently frames messages around audience priorities; finds the overlap',
                    'exemplary' => 'Makes audience feel understood; they see you as advocating for their interests',
                ],
                'improvement_tips' => [
                    'low' => 'Before communicating, answer: "Why should THEY care?" Lead with that, not with why YOU care.',
                    'mid' => 'Find the genuine overlap between your interests and theirs. If there isn\'t one, be honest about that.',
                    'high' => 'Learn what each key stakeholder actually cares about. Build a mental model of their priorities.',
                ],
            ],
            [
                'key' => 'style_flexibility',
                'label' => 'Style Flexibility',
                'description' => 'Moving fluidly between direct and indirect, concise and thorough, formal and casual as the situation requires.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Stuck in one style; can\'t or won\'t adapt even when it\'s clearly not working',
                    'mid' => 'Has two or three modes but transitions awkwardly between them',
                    'high' => 'Moves between styles smoothly; selects style to match situation',
                    'exemplary' => 'Full range of styles; transitions feel natural; uses style as a tool',
                ],
                'improvement_tips' => [
                    'low' => 'Identify your default style. Practice its opposite deliberately. If you\'re always direct, practice being indirect.',
                    'mid' => 'Study people who are good at styles different from your default. What makes their approach effective?',
                    'high' => 'Think of style as a dial, not a switch. You can be 70% direct instead of 100%. Calibrate precisely.',
                ],
            ],
            [
                'key' => 'assumption_checking',
                'label' => 'Assumption Checking',
                'description' => 'Noticing when you\'re assuming shared context, knowledge, or priorities that may not exist.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Assumes shared context constantly; confused when others don\'t follow',
                    'mid' => 'Checks assumptions for obvious gaps but misses subtle ones',
                    'high' => 'Proactively surfaces and checks assumptions; provides context appropriately',
                    'exemplary' => 'Intuits what context is missing; fills gaps seamlessly without being condescending',
                ],
                'improvement_tips' => [
                    'low' => 'When someone looks confused, your first thought should be: "What context am I assuming that they don\'t have?"',
                    'mid' => 'Briefly establish shared context at the start: "Just to make sure we\'re on the same page about X..."',
                    'high' => 'Different audiences lack different context. An exec might lack technical context; an engineer might lack business context. Both need bridging.',
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
            ['slug' => 'adaptive-communication'],
            [
                'name' => 'Adaptive Communication',
                'slug' => 'adaptive-communication',
                'description' => 'Learn to read your audience and adapt your communication style in real-time. Different people need different things—the message that lands with one person falls flat with another.',
                'icon' => 'users',
                'position' => 13,
                'is_active' => true,
                'blog_urls' => null,
            ]
        );
    }

    private function seedInsights(): void
    {
        $principle = Principle::where('slug', 'adaptive-communication')->first();

        if (! $principle) {
            return;
        }

        $insights = [
            [
                'name' => 'The Message Isn\'t the Message',
                'slug' => 'message-isnt-the-message',
                'summary' => 'What you said and what they heard are rarely the same thing. Communication isn\'t about transmission—it\'s about reception.',
                'content' => <<<'MARKDOWN'
You explained it perfectly. You were clear, thorough, and accurate.

And they completely misunderstood.

This happens constantly. Because communication isn't about what you transmit. It's about what they receive.

## The Transmission Fallacy

We tend to think of communication like sending a file. You package up your thoughts, send them over, and the other person opens the same file you sent.

But that's not how it works. The other person doesn't receive your message—they construct their own interpretation of it, filtered through:

- What they already believe
- What they're worried about
- What they think you want
- What context they have (and don't have)
- How much attention they're paying

You're not sending a file. You're sending ingredients that they cook into something.

## The Implications

If communication is about reception, not transmission, then:

**Your job isn't to say things clearly. It's to be understood.**
These are different. Sometimes being understood requires saying things that would sound redundant or obvious to you.

**Confusion is information.**
When someone misunderstands, they're telling you something about their mental model. Listen to the misunderstanding—it reveals what you need to address.

**Different people need different messages.**
The same words land differently with different audiences. Adapting isn't dumbing down—it's communicating effectively.

## The Test

After any important communication, don't ask "Did I explain that well?" Ask "Did they understand what they need to understand?"

If not, the communication failed—no matter how clear you think you were.
MARKDOWN,
                'position' => 0,
            ],
            [
                'name' => 'Read the Individual, Not the Role',
                'slug' => 'read-individual-not-role',
                'summary' => 'Not all executives want brevity. Not all engineers want details. Stereotypes are starting points, not answers.',
                'content' => <<<'MARKDOWN'
"Executives want the bottom line."
"Engineers want technical details."
"Sales people want to talk about relationships."

These stereotypes contain some truth. They're also traps.

## The Stereotype Problem

Role-based stereotypes are useful as starting points. They give you a default setting when you have no other information.

But they become dangerous when you stop there. Because:

- The executive who rose through engineering might want to go deep
- The engineer who's burned out on details might want the high-level view
- The sales person might be frustrated that no one takes them seriously on technical issues

When you treat people as role-stereotypes, you miss who they actually are. And they feel it.

## Read the Individual

Stereotypes are hypotheses, not conclusions. Use them to make an initial guess, then update based on actual signals:

**What questions do they ask?** Detail-oriented questions signal they want depth. "What's the bottom line?" signals they want brevity.

**How do they communicate with you?** People often want to receive communication in the style they send it.

**What do they complain about?** If they complain about "too many meetings," they probably value efficiency. If they complain about "not enough context," they want more depth.

**What's their background?** Someone's current role isn't their whole history. A CEO who was a designer thinks differently than a CEO who was a CFO.

## The Adaptation

Don't abandon stereotypes—they're useful priors. But hold them loosely. Watch for evidence that this individual deviates from type, and adjust.

The goal isn't to have one approach for "executives" and another for "engineers." It's to have an approach for this specific person in this specific context.
MARKDOWN,
                'position' => 1,
            ],
            [
                'name' => 'Lead with Their Priority',
                'slug' => 'lead-with-their-priority',
                'summary' => 'You care about your project. They care about their problems. Start with their problems, and you\'ll get to your project.',
                'content' => <<<'MARKDOWN'
You have an idea you're excited about. A project that matters to you. Something you've been working hard on.

None of that matters to your audience. They care about their own problems.

This isn't selfish on their part. It's human. Everyone is the protagonist of their own story, and your project is a side character.

## The Priority Mismatch

Most communication failures stem from leading with your priorities instead of theirs:

- You want to explain your solution → They want to know if it solves their problem
- You want to share what you learned → They want to know what they should do
- You want to show your work → They want the answer
- You want credit for effort → They want results

When you lead with your priorities, you're asking them to do work—to figure out why they should care. Most won't bother.

## The Flip

Effective communication starts with their world, not yours:

**Identify their priorities**: What problems are they trying to solve? What pressures are they under? What do they actually care about?

**Find the overlap**: Where does what you want connect to what they want? There's almost always a connection, but you have to find it.

**Lead with that**: Start with why this matters to them, not why it matters to you. You can get to your interests after you've established relevance to theirs.

## The Technique

Before any important communication, answer these questions:

1. What does this person care about most right now?
2. What problem of theirs does my message relate to?
3. How can I frame my message as helping them with their problem?

Then open with #3, not with what you want.

## The Exception

Sometimes there is no overlap. Your interests genuinely conflict, or your message doesn't serve their priorities.

In those cases, be honest about it. "This isn't about your priorities—it's about mine, and I'm asking for your help" is more effective than pretending alignment that doesn't exist.
MARKDOWN,
                'position' => 2,
            ],
            [
                'name' => 'Adjust Before You Crash',
                'slug' => 'adjust-before-you-crash',
                'summary' => 'When your message isn\'t landing, don\'t keep pushing. Stop, read the room, and try a different approach.',
                'content' => <<<'MARKDOWN'
You're two minutes into your explanation. You can see it in their face—they're not following. Or they're not buying it. Or they've already decided and are waiting for you to finish.

What do you do?

Most people keep going. They committed to this approach, they prepared this explanation, and they're going to deliver it, dammit.

This is how communication crashes.

## The Sunk Cost Trap

We treat our prepared communication like a speech to be delivered rather than a conversation to be navigated. We confuse "I practiced this" with "this is working."

But communication isn't about executing your plan. It's about achieving understanding. When your plan isn't working, staying on plan is failure.

## The Signals

Watch for these cues that your approach isn't landing:

**Confusion signals**: Furrowed brows, "wait, what?", clarifying questions about basic points
**Disagreement signals**: Crossed arms, "yes, but...", bringing up objections before you've finished
**Disengagement signals**: Phone checking, eyes wandering, perfunctory nodding
**Impatience signals**: "What's the bottom line?", looking at the clock, trying to jump ahead

These aren't failures of your audience. They're information about your approach.

## The Pivot

When you notice it's not landing:

**1. Stop**: Don't keep pushing ahead. The hole only gets deeper.

**2. Acknowledge**: "I'm not sure I'm explaining this well" or "Let me try a different angle."

**3. Check in**: "What would be most helpful?" or "What's your main concern?"

**4. Adjust**: Based on their answer, try a different approach. More concrete? More high-level? Address a specific objection?

## The Meta-Skill

The ability to adjust in real-time is more valuable than the ability to prepare perfectly. Preparation gets you started; adaptation gets you across the finish line.

The best communicators aren't the ones who never need to adjust. They're the ones who adjust so smoothly you barely notice.
MARKDOWN,
                'position' => 3,
            ],
            [
                'name' => 'Context Is Not Optional',
                'slug' => 'context-is-not-optional',
                'summary' => 'The curse of knowledge: you can\'t remember what it\'s like not to know. So you skip context that your audience desperately needs.',
                'content' => <<<'MARKDOWN'
You know this topic cold. You've lived it for months. Every detail, every consideration, every tradeoff is crystal clear in your mind.

So you start from where you are—and lose your audience in the first thirty seconds.

This is the curse of knowledge. Once you know something, you can't remember what it was like not to know it.

## The Context Gap

When you're deep in a topic, you forget how much context you're carrying:

- Background that took you weeks to absorb
- Terminology that's second nature to you
- Assumptions that seem obvious (to you)
- History that explains why things are the way they are

You skip all of this because it feels obvious. To your audience, it's a wall of confusion.

## The Symptoms

You have a context gap problem when:

- People ask questions you thought you answered
- The same misunderstanding keeps coming up
- People seem to miss the point of what you said
- Discussions get stuck on foundational issues

These aren't signs that your audience is slow. They're signs that you're assuming context that doesn't exist.

## The Bridge

**Start earlier than you think you need to**: What feels like "too basic" to you is often "helpful foundation" to them.

**Make assumptions explicit**: "I'm assuming you know X—is that right?" gives them a chance to ask for context.

**Use the "clean slate" test**: If someone knew nothing about this topic, what would they need to understand your point?

**Watch for confusion**: When someone looks lost, your first assumption should be "missing context," not "not paying attention."

## The Balance

There's a risk of going too far—over-explaining can be condescending. The goal is to calibrate:

- Experts in your area need less context
- Smart people from other areas need more context than you think
- Everyone needs some orientation, even if it's just "here's where we are in the conversation"

Err toward more context, but stay alert to signals that you've provided enough.
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
            ['slug' => 'read-the-room'],
            [
                'name' => 'Read the Room',
                'tagline' => 'Adapt your message to your audience',
                'description' => 'The same message lands differently with different people. Learn to read your audience, adjust your approach in real-time, and communicate in ways that actually get through.',
                'instruction_set' => $this->getInstructionSet(),
                'config' => [
                    'input_character_limit' => 800,
                    'reflection_character_limit' => 300,
                    'max_response_tokens' => 1000,
                    'max_history_exchanges' => 8,
                    'model' => 'claude-sonnet-4-20250514',
                ],
                'required_plan' => 'free',
                'icon' => 'Users',
                'is_active' => true,
                'sort_order' => 60,
            ]
        );

        $this->seedRequiredContext($mode);
        $this->seedDrills($mode);
    }

    private function getInstructionSet(): string
    {
        return <<<'INSTRUCTION'
You are a communication coach helping users develop the ability to read audiences and adapt their communication style. Your role is to present scenarios requiring audience adaptation and evaluate the user's ability to match their message to their listener.

## User Context
The user is a {{career_level}} {{job_title}} working in {{industry}}. They work in a {{team_composition}} environment and interact with {{cross_functional_teams}}.

## Difficulty Level
Current level: {{level}}

Level scaling:
- Levels 1-2: Straightforward audience differences (exec vs. IC, technical vs. non-technical). You point out mismatches explicitly and coach on adjustment.
- Levels 3-4: Mixed audiences, people who defy role stereotypes, or situations where initial read was wrong. You ask "how do you know that's what they need?" and push on assumptions.
- Levels 5-6: Subtle cues, cultural differences, audiences with hidden agendas. You challenge assumptions about audience needs.
- Levels 7-8: Hostile audiences, rapid context shifts, people actively resistant to the message. You play the difficult audience member.
- Levels 9-10: Complex multi-stakeholder scenarios, real-time pivots required, audiences that actively mislead. You don't make it easy.

## Coaching Approach
1. Present scenarios requiring audience adaptation relevant to the user's context
2. Evaluate not just WHAT they communicate but HOW they adapt it
3. Challenge one-size-fits-all communication approaches
4. Push users to read individuals, not just roles
5. Test real-time adjustment when initial approaches don't land
6. Reward genuine flexibility over surface-level adaptation

## Key Principles
- Communication is about reception, not transmission
- Read the individual, not just the role stereotype
- Lead with their priorities, not yours
- Adjust before you crash—don't keep pushing what isn't working
- Context you assume is often context they lack

## Blind Spot Categories to Watch For
- Defaults to too much detail with executives
- Over-simplifies with technical audiences
- Too formal when casual would build rapport
- Too casual when formality signals respect
- Leads with own priorities instead of audience's
- Assumes shared context that doesn't exist
- Fails to adjust when message isn't landing
- One communication style regardless of audience
- Talks up to senior people unnecessarily
- Talks down to junior people unnecessarily
- Ignores individual variation within role categories
- Mistakes role for communication preference (not all execs want brevity)
- Over-indexes on stereotypes instead of reading the individual
- Uses jargon with wrong audience
- Avoids jargon when it would signal credibility
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
                'name' => 'Exec Translation',
                'position' => 0,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['audience_reading', 'detail_calibration', 'priority_alignment'],
                'primary_insight_slug' => 'lead-with-their-priority',
                'scenario_instruction_set' => <<<'PROMPT'
Generate an executive communication scenario appropriate for the user's level and context.

Create a situation where the user must communicate technical or detailed information to an executive or senior leader.

Include:
- Specific technical or operational content to communicate
- Context about the executive (their background, priorities, time constraints)
- The stakes of the communication
- What the executive needs to decide or understand

At higher levels, add:
- Executives who defy the "just give me the bottom line" stereotype
- Time pressure or interruptions
- Executives with technical backgrounds who want to go deep
- Political complexity or competing priorities

Format your response as JSON:
{
    "scenario": "[The content you need to communicate]\n\n[Context about the executive and situation]\n\n[What they need from this communication]",
    "task": "Communicate this to the executive. Match your level of detail, framing, and priorities to what they need. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's executive communication.

Check for:
- Priority alignment: Did they lead with what matters to the exec, not what matters to them?
- Detail calibration: Was the level of detail appropriate for this specific executive?
- Bottom line clarity: Could the exec quickly understand the key point?
- Flexibility: Did they show awareness that not all execs want the same thing?

Red flags:
- Burying the lead in detail
- Assuming all execs want identical communication style
- Leading with their own priorities or excitement
- Too much or too little context for this specific exec

At higher levels, also check:
- Did they read the specific executive, not just "an executive"?
- Did they anticipate likely questions?
- Did they avoid both over-simplifying and over-complicating?

Quote their communication and explain what worked and what didn't for this specific audience.
PROMPT,
            ],
            [
                'name' => 'Technical Deep Dive',
                'position' => 1,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['detail_calibration', 'translation_ability', 'assumption_checking'],
                'primary_insight_slug' => 'context-is-not-optional',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a technical audience communication scenario appropriate for the user's level and context.

Create a situation where the user must communicate with a technical audience—but the trap is over-simplifying or under-estimating their sophistication.

Include:
- Technical content to communicate
- Context about the technical audience (their expertise level, domain, what they care about)
- The goal of the communication
- Ways the user might underestimate or overestimate the audience

At higher levels, add:
- Technical experts who want business context, not just technical details
- Mixed technical audiences with varying expertise
- Audiences who will catch and call out oversimplifications
- Situations where the user's technical level is lower than the audience's

Format your response as JSON:
{
    "scenario": "[The content you need to communicate]\n\n[Context about the technical audience]\n\n[What success looks like]",
    "task": "Communicate this to the technical audience. Match their level of sophistication—don't over-simplify or overwhelm. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's technical communication.

Check for:
- Appropriate sophistication: Did they match the audience's level without over-simplifying?
- Respect: Did they communicate as a peer, not talking down?
- Precision: Did they use terminology correctly?
- Context: Did they provide necessary context without over-explaining basics?

Red flags:
- Dumbing down for experts
- Using jargon incorrectly
- Over-explaining basics that the audience already knows
- Missing what technical audiences actually care about

At higher levels, also check:
- Did they recognize when technical audiences want business context?
- Did they calibrate to this specific technical audience?
- Did they avoid the trap of showing off technical knowledge unnecessarily?

Quote their communication and explain what landed and what missed for this audience.
PROMPT,
            ],
            [
                'name' => 'Cross-Functional Bridge',
                'position' => 2,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['translation_ability', 'priority_alignment', 'style_flexibility'],
                'primary_insight_slug' => 'message-isnt-the-message',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a cross-functional communication scenario appropriate for the user's level and context.

Create a situation where the user must communicate across functional boundaries—engineering to sales, product to finance, design to legal, etc.

Include:
- Content that needs to be translated across functions
- Context about both functions involved
- Different priorities and mental models each function has
- What success looks like for this communication

At higher levels, add:
- Historical tension between the functions
- Situations where the user's function is viewed skeptically
- Complex tradeoffs that look different from each perspective
- Need to build long-term cross-functional relationship

Format your response as JSON:
{
    "scenario": "[The content to communicate across functions]\n\n[Context about both functions and their relationship]\n\n[What you need from this communication]",
    "task": "Communicate this across the functional divide. Translate your priorities into their language and concerns. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's cross-functional communication.

Check for:
- Translation: Did they reframe their message in terms the other function cares about?
- Priority alignment: Did they connect to the other function's concerns?
- Mutual respect: Did they avoid dismissing the other function's perspective?
- Bridge-building: Did they find common ground?

Red flags:
- Using own function's jargon without translation
- Dismissing the other function's priorities
- "Us vs. them" framing
- Assuming the other function should just "get it"

At higher levels, also check:
- Did they acknowledge legitimate differences in perspective?
- Did they build toward long-term collaboration, not just this transaction?
- Did they avoid both condescension and over-deference?

Quote their communication and explain what worked for bridging the functional gap.
PROMPT,
            ],
            [
                'name' => 'Managing Up',
                'position' => 3,
                'timer_seconds' => 90,
                'input_type' => 'text',
                'dimensions' => ['audience_reading', 'priority_alignment', 'formality_matching'],
                'primary_insight_slug' => 'read-individual-not-role',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a managing up scenario appropriate for the user's level and context.

Create a situation where the user must communicate upward—pitching an idea, raising a concern, requesting resources, or delivering difficult news to someone more senior.

Include:
- The specific message to communicate upward
- Context about the senior person (their style, pressures, history)
- The power dynamics at play
- What the user needs from this interaction

At higher levels, add:
- Senior people with unpredictable or difficult communication styles
- Situations where the user is delivering news the senior person doesn't want to hear
- Political complexity or competing senior stakeholders
- Need to be both respectful and assertive

Format your response as JSON:
{
    "scenario": "[What you need to communicate upward]\n\n[Context about the senior person and dynamics]\n\n[What you're trying to achieve]",
    "task": "Communicate this upward. Balance respect for the relationship with clarity of your message. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's upward communication.

Check for:
- Appropriate confidence: Did they communicate as a valued contributor, not a supplicant?
- Clear message: Was the core point clear despite the power dynamic?
- Relationship awareness: Did they show awareness of the political context?
- Reading the individual: Did they adapt to this specific senior person?

Red flags:
- Excessive deference that obscures the message
- Arrogance that damages the relationship
- Ignoring the power dynamic entirely
- Treating "senior person" as a monolithic category

At higher levels, also check:
- Did they navigate the tension between clarity and political safety?
- Did they read this specific leader, not just "a leader"?
- Did they maintain their position while respecting the relationship?

Quote their communication and explain what worked for managing up effectively.
PROMPT,
            ],
            [
                'name' => 'Real-Time Pivot',
                'position' => 4,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'dimensions' => ['real_time_adjustment', 'audience_reading', 'style_flexibility'],
                'primary_insight_slug' => 'adjust-before-you-crash',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a real-time adjustment scenario appropriate for the user's level and context.

Create a situation where the user's initial communication approach isn't landing, and they need to recognize this and pivot.

Present the scenario in two parts:
1. Initial context and their first attempt
2. Feedback signals that their approach isn't working

Include:
- An initial communication attempt
- Clear (or subtle) signals that it's not landing
- Context about why the initial approach failed
- What the audience actually needs

At higher levels, add:
- Subtle signals that are easy to miss
- Audiences who won't tell you directly that you've lost them
- Situations where multiple pivots might be needed
- High stakes for failing to adjust

Format your response as JSON:
{
    "scenario": "[Initial context and the user's first approach]\n\n[Signals that it's not working: what the audience is doing/saying]\n\n[What's actually going wrong]",
    "task": "Your approach isn't landing. Recognize what's happening and pivot. How do you adjust? What do you say next? 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's real-time adjustment.

Check for:
- Signal recognition: Did they correctly identify what wasn't working?
- Willingness to pivot: Did they actually change approach rather than push harder?
- Diagnosis: Did they understand WHY it wasn't working?
- New approach: Is the pivot likely to work better?

Red flags:
- Plowing ahead with the same approach
- Blaming the audience for not understanding
- Pivoting without understanding why the first approach failed
- Token adjustment that doesn't address the real problem

At higher levels, also check:
- Did they read subtle signals?
- Was the pivot smooth or awkward?
- Did they avoid over-correcting?

Quote their pivot and evaluate both the diagnosis and the new approach.
PROMPT,
            ],
            [
                'name' => 'Mixed Audience',
                'position' => 5,
                'timer_seconds' => 120,
                'input_type' => 'text',
                'dimensions' => ['translation_ability', 'detail_calibration', 'assumption_checking'],
                'primary_insight_slug' => 'message-isnt-the-message',
                'scenario_instruction_set' => <<<'PROMPT'
Generate a mixed audience scenario appropriate for the user's level and context.

Create a situation where the user must communicate to a group with diverse backgrounds, levels, or priorities—and can't fully optimize for any single person.

Include:
- The content to communicate
- The mix of people in the audience (roles, levels, expertise, priorities)
- The challenges of addressing everyone
- What success looks like

At higher levels, add:
- Audiences with actively conflicting needs
- Political dynamics within the group
- People who will call out communication that seems to exclude them
- Time constraints that prevent addressing everyone sequentially

Format your response as JSON:
{
    "scenario": "[The content to communicate]\n\n[The mix of people: who they are, what they need, how they differ]\n\n[The challenge of addressing everyone]",
    "task": "Communicate to this mixed group. You can't fully optimize for everyone—make strategic choices about how to reach the room. 4-6 sentences."
}
PROMPT,
                'evaluation_instruction_set' => <<<'PROMPT'
Evaluate the user's mixed audience communication.

Check for:
- Strategic choices: Did they make conscious decisions about how to balance competing needs?
- Inclusion: Did they avoid completely alienating any segment?
- Layering: Did they layer information so different audiences could engage at different levels?
- Acknowledgment: Did they acknowledge the mixed nature of the audience?

Red flags:
- Optimizing for one segment while ignoring others
- Trying to address everyone sequentially (often impossible)
- Defaulting to one style without acknowledging tradeoffs
- Alienating a segment through jargon, level, or framing

At higher levels, also check:
- Did they find creative ways to serve multiple audiences?
- Did they handle political dynamics within the group?
- Did they make explicit choices rather than hoping for the best?

Quote their communication and evaluate how well they served the mixed audience.
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
