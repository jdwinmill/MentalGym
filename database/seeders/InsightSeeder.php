<?php

namespace Database\Seeders;

use App\Models\Drill;
use App\Models\Insight;
use App\Models\Principle;
use Illuminate\Database\Seeder;

class InsightSeeder extends Seeder
{
    public function run(): void
    {
        $insights = [
            // Clarity Under Pressure
            [
                'principle_slug' => 'clarity-under-pressure',
                'name' => 'Find the Buried Lead',
                'slug' => 'compression-find-the-buried-lead',
                'summary' => 'Your most important point is probably hiding in the middle of your message. Here\'s how to dig it out and lead with it.',
                'content' => <<<'MARKDOWN'
You've been there. Someone asks you a question in a meeting, and you launch into the full story—the context, the challenges, what you tried, what didn't work. Somewhere around minute three, you finally get to the actual answer.

By then, half the room has checked out.

This is the "buried lead"—journalism's term for hiding the most newsworthy information deep in a story. In business communication, it's epidemic.

## Why We Bury the Lead

Our brains want to explain *how* we arrived at a conclusion before sharing the conclusion itself. It feels logical. It feels thorough.

But your audience doesn't experience it that way. They experience confusion, impatience, and the growing suspicion that you don't actually have an answer.

## The Fix

Before you speak or write anything important, ask yourself one question:

> "If they only remember one thing, what should it be?"

That's your lead. Start there.

Everything else—the context, the caveats, the backstory—comes *after* you've delivered the headline. Your audience can now process supporting details because they know where you're going.

## Try This

Next time you're about to send an important email, write it normally. Then look at your last paragraph. Nine times out of ten, that's where your actual point is hiding.

Move it to the top. Delete the throat-clearing that came before it. You'll be shocked how much stronger it reads.
MARKDOWN,
                'position' => 0,
                'drill_name' => 'Compression',
            ],
            [
                'principle_slug' => 'clarity-under-pressure',
                'name' => 'The 30-Second Discipline',
                'slug' => 'the-30-second-rule',
                'summary' => 'If you can\'t explain it in 30 seconds, the problem isn\'t time—it\'s clarity. This constraint reveals what you actually understand.',
                'content' => <<<'MARKDOWN'
Here's a test that will humble you: try explaining your current project to someone unfamiliar with it in 30 seconds.

Not a rushed, breathless 30 seconds. A calm, confident 30 seconds where they actually understand what you said.

Most people can't do it. And that's revealing.

## The Uncomfortable Truth

When you can't explain something quickly, it usually means one of three things:

1. You don't actually understand what matters most
2. You're attached to details that don't matter
3. You haven't done the work to simplify it

The 30-second constraint isn't about speaking fast. It's about *thinking clearly*.

## Why This Works

Constraints force choices. When you only have 30 seconds, you can't include everything. You have to decide: what's essential? What can go?

That decision-making process is where clarity comes from. You can't trim what you don't understand.

## The Practice

Time yourself explaining something you're working on. Don't cheat—actually use a timer.

If you go over, don't speak faster. Cut content. Ask yourself: "Which part of this could I remove and still get my point across?"

Keep cutting until you hit 30 seconds.

What remains is the core of your message. Everything else was decoration.
MARKDOWN,
                'position' => 1,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'clarity-under-pressure',
                'name' => 'Reframe the Pressure',
                'slug' => 'pressure-reframe',
                'summary' => 'That anxiety before a big moment? It\'s not a bug—it\'s a feature. You just need to relabel it.',
                'content' => <<<'MARKDOWN'
Your heart is racing. Your palms are sweating. You're about to present to the executive team, and your brain is screaming that something is wrong.

Here's the thing: your brain is right that something is happening. It's wrong about what.

## Anxiety and Excitement Are Twins

Physiologically, anxiety and excitement produce nearly identical responses: elevated heart rate, heightened alertness, adrenaline flowing. Your body can't tell the difference.

The only difference is the story you tell yourself about what those sensations mean.

"I'm nervous" triggers threat mode. Your thinking narrows. You focus on what could go wrong. You become defensive.

"I'm excited" triggers opportunity mode. Your thinking stays expansive. You focus on what you want to convey. You become engaged.

Same sensations. Wildly different outcomes.

## The Relabel

Before your next high-stakes moment, try this: when you notice the physical symptoms of pressure, say to yourself—out loud if you can—"I'm excited."

It feels awkward. It might even feel false. But research shows it works. The relabel interrupts the anxiety narrative and gives your brain a different frame.

## The Deeper Truth

You're in the room because you belong there. You were invited because someone believes you have something valuable to contribute. The pressure you feel? That's your body getting ready to deliver.

Let it.
MARKDOWN,
                'position' => 2,
                'drill_name' => null,
            ],

            // Executive Presence
            [
                'principle_slug' => 'executive-presence',
                'name' => 'Bottom Line Up Front',
                'slug' => 'lead-with-the-conclusion',
                'summary' => 'Executives don\'t want to go on a journey with you. They want to know where you landed—then decide if the journey matters.',
                'content' => <<<'MARKDOWN'
Remember writing essays in school? Introduction, body paragraphs building your argument, then conclusion. You were trained to *build* toward your point.

Forget all of that.

Executive communication flips the script entirely. You start with the conclusion. Everything else is supporting material they may or may not need.

## Why This Works

Executives are making dozens of decisions a day. Their scarcest resource is attention. When you make them wait for your point, you're spending their attention on context they can't yet evaluate.

When you lead with the conclusion, you give them what they need immediately: "Here's what I think we should do." Now they can decide if they need the supporting details or if they're ready to move forward.

## The Structure

1. **Recommendation or key point** — State it directly, in one sentence
2. **Two to three supporting reasons** — Not everything, just the strongest points
3. **Key risks or tradeoffs** — Show you've thought it through
4. **Clear ask** — What do you need from them?

That's it. Resist the urge to add more context "just in case."

## The Mindset Shift

You're not writing an essay for a grade. You're briefing a decision-maker who trusts you. Honor that trust by being direct.

If they want more detail, they'll ask. And that question will tell you exactly what they need to know.
MARKDOWN,
                'position' => 0,
                'drill_name' => 'Executive Opener',
            ],
            [
                'principle_slug' => 'executive-presence',
                'name' => 'Stop Undermining Yourself',
                'slug' => 'speak-as-a-peer',
                'summary' => 'Every time you say "I might be wrong, but..." you chip away at your credibility. Here\'s how to state your views without hedging.',
                'content' => <<<'MARKDOWN'
Listen to yourself in your next few meetings. Count how many times you say things like:

- "This might be a dumb question, but..."
- "I could be wrong, but..."
- "I just think that maybe..."
- "Sorry, but..."

Each one is a small self-demotion. You're telling the room, before you've even made your point, that your point probably isn't worth hearing.

## Why We Do This

We hedge because we're afraid of being wrong. The hedge feels like insurance—if we're wrong, at least we signaled uncertainty.

But here's what actually happens: people hear the hedge, not the idea. Your credibility takes the hit whether you're right or wrong.

## The Alternative

State your view directly. If you're uncertain, say so specifically—"I'm not sure about the timeline"—rather than wrapping your entire contribution in doubt.

"I think we should delay the launch" hits differently than "I might be wrong, but maybe we should consider possibly delaying the launch?"

The first is a position. The second is noise.

## The Peer Mindset

You're in the room because of your expertise. Executives don't want another person who defers constantly—they have enough of those. They want people who bring clear perspectives.

That doesn't mean being arrogant or dismissive. It means trusting that your expertise has value, and presenting it accordingly.

You're not reporting to them. You're advising them. There's a difference.
MARKDOWN,
                'position' => 1,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'executive-presence',
                'name' => 'Guide the Room',
                'slug' => 'control-the-room',
                'summary' => 'Presence isn\'t about talking the most or the loudest. It\'s about being the person who moves things forward.',
                'content' => <<<'MARKDOWN'
The person with presence in a meeting isn't necessarily the most senior, the loudest, or the one who talks the most.

They're the one who keeps things moving forward. The one people look to when the conversation stalls or spirals.

## The Toolkit

**Framing**: Before diving into details, set context. "Before we get into the specifics, let me frame what we're trying to solve here." This orients everyone and establishes you as someone who thinks structurally.

**Redirecting**: When discussions go off-track, bring them back—without being heavy-handed. "That's an important point, and I want to make sure we address it. Let's capture it and come back after we settle the main question."

**Summarizing**: Periodically capture what's been said. "So if I'm understanding correctly, we're aligned on A and B, but still working through C?" This clarifies, advances the conversation, and demonstrates that you're tracking the full picture.

**Strategic silence**: You don't need to fill every pause. Let moments breathe. When you do speak, it carries more weight because you haven't diluted your voice.

## The Through-Line

Notice what all these have in common: they serve the conversation, not your ego. Presence isn't about being seen. It's about being useful in a way that others notice.

The person who consistently moves discussions toward decisions? That's who people remember as having presence.
MARKDOWN,
                'position' => 2,
                'drill_name' => null,
            ],

            // Decision Architecture
            [
                'principle_slug' => 'decision-architecture',
                'name' => 'The DRR Framework',
                'slug' => 'the-decision-rationale-risk-framework',
                'summary' => 'A simple structure for presenting any decision: what you\'re recommending, why, and what could go wrong.',
                'content' => <<<'MARKDOWN'
Most decision presentations fail not because the decision is wrong, but because the presentation is disorganized. People can't follow your logic, so they don't trust your conclusion.

The Decision-Rationale-Risk (DRR) framework solves this. It gives your audience exactly what they need, in the order they need it.

## The Three Parts

**Decision**: State what you're recommending, specifically and directly. Not "we should think about possibly doing something about the launch timing" but "we should delay launch to Q2."

**Rationale**: Give two to three supporting reasons. These should be your strongest arguments, not an exhaustive list. Quality over quantity. "First, customer research shows the current version would disappoint our power users. Second, engineering can add the key features in six weeks. Third, the competitive window doesn't close until May."

**Risk**: Acknowledge what could go wrong, and how you'd address it. This isn't weakness—it's thoroughness. It shows you've stress-tested your own thinking. "The main risk is that our competitor announces first. We'd mitigate this by pre-announcing our timeline to key accounts."

## Why This Works

DRR works because it mirrors how decision-makers think. They want to know what you're proposing (so they can orient), why it makes sense (so they can evaluate), and what you might be missing (so they can trust you've thought it through).

Give them that, in that order, and you'll be miles ahead of the typical meandering proposal.

## Universal Application

This isn't just for big strategic decisions. Use it for project proposals, hiring recommendations, budget requests—any time you need someone to agree with your thinking.
MARKDOWN,
                'position' => 0,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'decision-architecture',
                'name' => 'Disagree, Then Commit',
                'slug' => 'disagree-and-commit',
                'summary' => 'Voice your concerns clearly while the decision is being made. Then, once it\'s made, throw your full weight behind it.',
                'content' => <<<'MARKDOWN'
There's a destructive pattern in organizations: people stay silent during discussions, then undermine decisions afterward through passive resistance or hallway complaints.

"Disagree and commit" is the antidote. It's a discipline with two equally important parts.

## Part One: Disagree

Before a decision is final, you have an *obligation* to voice concerns. Silence doesn't mean you agree—but it will be taken that way. If you think a decision is wrong, say so. Clearly, specifically, with reasoning.

This takes courage. It might be uncomfortable. But it's infinitely better than the alternative: watching a preventable mistake unfold while thinking "I knew this wouldn't work."

Your dissent is a gift to the decision-making process. Offer it.

## Part Two: Commit

Once a decision is made, commit fully. This doesn't mean you were wrong to disagree. It means the organization functions better when everyone rows in the same direction.

What does real commitment look like?

- Executing as if it were your own idea
- Defending the decision to others (even if you argued against it)
- Giving your full effort to making it succeed

What does fake commitment look like?

- "Well, we'll see how this goes..."
- Undermining the decision in side conversations
- Waiting for it to fail so you can say "I told you so"

## The Point

You can think a decision is wrong and still work wholeheartedly to make it succeed. These aren't contradictory—they're the mark of a professional.
MARKDOWN,
                'position' => 1,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'decision-architecture',
                'name' => 'One-Way vs. Two-Way Doors',
                'slug' => 'two-way-door-decisions',
                'summary' => 'Not all decisions deserve the same deliberation. Learn to tell the reversible ones from the irreversible ones.',
                'content' => <<<'MARKDOWN'
Most organizations have a decision-making disease: they treat every choice like it's permanent, critical, and high-stakes.

The result? Paralysis. Projects stall waiting for sign-off. Teams spend weeks debating things that don't matter. Speed dies.

Amazon popularized a useful distinction: one-way doors versus two-way doors.

## Two-Way Doors

These are decisions you can easily reverse. Wrong choice? Walk back through the door you came in.

- Trying a new meeting format
- Testing a different pricing page
- Hiring a contractor for a trial project

Two-way doors should be made quickly. If you can undo it, perfectionism is waste. Move fast, learn from results, adjust.

## One-Way Doors

These are decisions that are difficult or impossible to reverse. Once you walk through, you're on the other side.

- Major hires for key roles
- Fundamental architecture choices
- Entering or exiting a market

One-way doors deserve deliberation. Take your time. Gather input. Stress-test your thinking.

## The Diagnostic

When facing a decision, ask yourself:

- If this doesn't work, can we undo it?
- What's the cost of reversal?
- Is the learning from trying worth more than the cost of being wrong?

Most decisions are two-way doors that we treat like one-way doors. That misclassification is costing you speed and sanity.
MARKDOWN,
                'position' => 2,
                'drill_name' => null,
            ],

            // Precision Writing
            [
                'principle_slug' => 'precision-writing',
                'name' => 'One Idea, One Sentence',
                'slug' => 'one-idea-per-sentence',
                'summary' => 'The most common writing mistake in business? Cramming multiple ideas into a single sentence until readers give up.',
                'content' => <<<'MARKDOWN'
Read this sentence:

> "We need to consider both the short-term revenue implications, which are significant given Q3 targets, and the long-term strategic positioning, particularly regarding our enterprise segment, which has been growing 40% year-over-year but faces new competitive pressure from Acme Corp."

Did you follow all of that? Probably not on the first pass. Maybe not on the second either.

Now read this:

> "We face two considerations. First, short-term revenue—this decision impacts Q3 targets. Second, long-term positioning. Our enterprise segment is growing 40% YoY but facing new pressure from Acme Corp."

Same information. Actually comprehensible.

## The Rule

One idea per sentence. If you have multiple ideas, use multiple sentences.

It sounds simple. It's surprisingly hard to do. We're addicted to cramming everything into one sprawling construction.

## The Test

Read your writing out loud. Where you run out of breath? That's where you need a period.

If you can't say a sentence in one breath without rushing, your reader can't process it in one mental pass either.

## The Objection

"But it feels choppy!"

Does it? Or does it feel *clear*?

Readers don't want elegance. They want to understand what you're saying. Short sentences aren't unsophisticated—they're considerate.

Ernest Hemingway made a career of short sentences. You'll survive.
MARKDOWN,
                'position' => 0,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'precision-writing',
                'name' => 'Cut Before You Polish',
                'slug' => 'cut-first-then-edit',
                'summary' => 'Most first drafts are 30-50% too long. Before you start polishing sentences, ask which sentences shouldn\'t exist.',
                'content' => <<<'MARKDOWN'
You've finished a draft. Time to edit. You start tinkering with word choices, smoothing transitions, fixing typos.

Stop. You've skipped the most important step.

## Phase One: Cut

Before you worry about *how* something is written, ask if it should be written at all.

Most first drafts contain:
- Unnecessary context that the reader doesn't need
- Redundant points that say the same thing twice
- Throat-clearing that delays getting to the point
- Qualifications that weaken the message without adding value

Don't polish these. Delete them.

Set yourself a target: cut 30% of your word count. Not 5%. Not 10%. Thirty percent. Yes, really.

"But I need all of it!"

You probably don't. That feeling of needing it all? That's attachment to your own words, not a judgment about what the reader needs.

## Phase Two: Polish

Only after cutting should you refine what remains. Now you can focus on:
- Word choice
- Rhythm
- Transitions
- Opening and closing strength

This is the work most people think of as "editing." But it's phase two, not phase one.

## The Mistake

Jumping straight to polishing. You spend twenty minutes perfecting a sentence that shouldn't exist. Meanwhile, your document is still 40% too long and the reader gave up on paragraph three.

Cut first. Then polish what survives.
MARKDOWN,
                'position' => 1,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'precision-writing',
                'name' => 'Subject Lines That Get Opened',
                'slug' => 'action-oriented-subject-lines',
                'summary' => 'Your subject line shouldn\'t describe the topic. It should tell readers exactly what you need from them.',
                'content' => <<<'MARKDOWN'
Most email subject lines are descriptions:
- "Q3 Marketing Update"
- "Project Phoenix Status"
- "Thoughts on Proposal"

These tell readers what the email is *about*. They don't tell readers why they should *care* or what they need to *do*.

That's a missed opportunity.

## The Shift

Your subject line is prime real estate. Use it to communicate the action you need:

| Instead of | Try |
|------------|-----|
| "Budget Discussion" | "Decision Needed: Q2 Budget by Friday" |
| "Team Offsite" | "Action Required: RSVP for March 15 Offsite" |
| "Thoughts on Proposal" | "Review Request: Product Proposal (15 min read)" |
| "Quick Question" | "Need Input: Should we proceed with Vendor A?" |

## Why This Works

Your reader is scanning an inbox with 50 unread messages. They're making split-second decisions about what to open now, what to open later, and what to ignore.

An action-oriented subject line gives them the information they need to prioritize. They know exactly what you need and can act accordingly.

## The Bonus

This approach forces *you* to clarify what you actually want. If you can't write an action-oriented subject line, maybe you don't know what you're asking for yet.

Figure that out before you send the email.
MARKDOWN,
                'position' => 2,
                'drill_name' => null,
            ],

            // Meeting Leadership
            [
                'principle_slug' => 'meeting-leadership',
                'name' => 'State the Objective First',
                'slug' => 'start-with-the-end',
                'summary' => 'Most meetings fail in the first 30 seconds—because no one states what success looks like.',
                'content' => <<<'MARKDOWN'
Picture a meeting that just... ended. People drifted away. Conversation wound down. Someone finally said "Okay, I think we're good?" and everyone closed their laptops.

What was accomplished? Hard to say.

Now picture a meeting that started with: "By the end of this meeting, we need to decide between Option A and Option B for the product launch."

That meeting had a fighting chance.

## The First 30 Seconds

Every meeting should begin with a clear statement of the objective. What does success look like? What needs to be different when we leave?

Good objectives:
- "We need to decide between these three vendors"
- "We need to align on the top 3 priorities for Q2"
- "We need to identify the key risks in this proposal"

Bad objectives:
- "Let's discuss the project" (too vague)
- "I'll give an update" (that's an email)
- No objective stated (the most common failure)

## What This Enables

Once you have a stated objective, everything else follows:
- You can evaluate whether the discussion is on-track
- You can tell when you're done
- You can measure success
- Participants can prepare effectively

Without it, you're just a group of people talking until the calendar tells you to stop.

## Whose Job Is This?

Whoever called the meeting owns this. If you scheduled it, you owe the group an objective in the first 30 seconds.

If you're attending a meeting without a stated objective, ask for one. "What are we trying to accomplish today?" It's not rude—it's helpful.
MARKDOWN,
                'position' => 0,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'meeting-leadership',
                'name' => 'The Parking Lot Technique',
                'slug' => 'the-parking-lot',
                'summary' => 'Important tangents will derail your meeting—unless you have a system for capturing them without losing focus.',
                'content' => <<<'MARKDOWN'
You're thirty minutes into a meeting, making good progress, when someone raises an adjacent topic. It's legitimately important. It's also completely off-track.

Now you're stuck. Follow the tangent and lose your momentum? Dismiss it and seem like you don't care?

The parking lot solves this.

## How It Works

The parking lot is simply a visible list—a whiteboard section, a shared doc, a chat thread—where you capture topics to address later.

When a tangent emerges:

1. **Acknowledge it**: "That's an important point."
2. **Park it**: "Let's add it to the parking lot and come back to it after we resolve the main question."
3. **Physically capture it**: Write it down where everyone can see.

The topic is honored without derailing the meeting.

## The Critical Part: Follow Through

At the end of every meeting, review the parking lot. For each item, decide:
- Address it now (if time permits)
- Assign an owner and timeline
- Schedule a separate discussion
- Decide it doesn't actually need follow-up

Without this step, parking lots become graveyards—a place where topics go to die. People stop trusting the system. They start fighting harder to address tangents immediately.

## The Double Benefit

The parking lot serves two purposes:
1. It keeps the current discussion focused
2. It signals respect—the tangent is important enough to capture, just not right now

Both matter.
MARKDOWN,
                'position' => 1,
                'drill_name' => null,
            ],
            [
                'principle_slug' => 'meeting-leadership',
                'name' => 'No Owner, No Decision',
                'slug' => 'decisions-need-owners',
                'summary' => 'A decision without an owner isn\'t actually a decision. It\'s a hope.',
                'content' => <<<'MARKDOWN'
Meetings end. Decisions were made—or so everyone thought. Then... nothing happens.

Three weeks later, someone asks "Whatever happened with X?" and everyone looks at each other. Nobody owned it. Nobody did it.

This is the most common meeting failure, and it's entirely preventable.

## The Rule

Every decision needs three things before the meeting ends:

**An owner**: One specific person (not "the team," not "we") who is accountable for making it happen. "Sarah will own this."

**A timeline**: A specific date by which the action will be complete. Not "soon" or "ASAP." A date. "By end of day Friday."

**Documentation**: Write it down in a place everyone can see. Capture it in real time, not from memory afterward.

If you can't identify all three, the decision isn't finished. Keep discussing until you can.

## Common Resistance

"This feels bureaucratic."

It's not bureaucratic. It's operational. The alternative isn't freedom—it's dropped balls and confusion.

"We all know what to do."

Then it should take fifteen seconds to confirm who's doing it and by when. If that's hard, you don't actually all know what to do.

## The Habit

Capture decisions as they happen, not at the end. By the end of a long meeting, details are fuzzy and energy is low. In the moment, clarity is high.

"Great, so we're moving forward with option B. Who's owning this? Sarah? Great. When can you have it done? Friday? Perfect. I've captured it."

That's all it takes. Ten seconds of discipline to prevent weeks of drift.
MARKDOWN,
                'position' => 2,
                'drill_name' => null,
            ],
        ];

        foreach ($insights as $insightData) {
            $principle = Principle::where('slug', $insightData['principle_slug'])->first();

            if (! $principle) {
                continue;
            }

            $drillName = $insightData['drill_name'];
            unset($insightData['principle_slug'], $insightData['drill_name']);

            $insight = Insight::updateOrCreate(
                ['slug' => $insightData['slug']],
                array_merge($insightData, ['principle_id' => $principle->id])
            );

            // Link to drill if drill_name is specified
            if ($drillName) {
                $drill = Drill::where('name', $drillName)->first();
                if ($drill) {
                    // Sync the relationship with is_primary = true
                    $drill->insights()->syncWithoutDetaching([
                        $insight->id => ['is_primary' => true],
                    ]);
                }
            }
        }
    }
}
