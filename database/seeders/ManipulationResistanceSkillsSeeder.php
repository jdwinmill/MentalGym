<?php

namespace Database\Seeders;

use App\Models\SkillDimension;
use Illuminate\Database\Seeder;

class ManipulationResistanceSkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            [
                'key' => 'detection_speed',
                'label' => 'Detection Speed',
                'description' => 'How quickly you recognize manipulation is occurring. Catching tactics in real-time vs. only in retrospect.',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'Only recognized manipulation during debrief or not at all. Tactics landed fully before any awareness kicked in.',
                    'mid' => 'Noticed something felt off mid-conversation but couldn\'t act on it in time. Awareness came after key concessions.',
                    'high' => 'Caught tactics early enough to adjust approach. Recognized pressure patterns and responded with intention.',
                    'exemplary' => 'Identified manipulation immediately and responded from full awareness. Never operated on autopilot.',
                ],
                'improvement_tips' => [
                    'low' => 'Notice when your emotions shift suddenly—that\'s often a signal. If you feel rushed, guilty, or flattered out of nowhere, pause.',
                    'mid' => 'Trust the "something\'s off" feeling before you can name it. Practice naming the emotion they\'re trying to trigger.',
                    'high' => 'You\'re catching tactics early. Focus on responding from awareness rather than just detection.',
                ],
                'active' => true,
            ],
            [
                'key' => 'tactic_identification',
                'label' => 'Tactic Identification',
                'description' => 'Accurately naming the specific technique being used. "This is anchoring" vs. vague sense something\'s off.',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'Couldn\'t identify tactics used. Vague sense of discomfort but no ability to name what was happening.',
                    'mid' => 'Identified the general category (e.g., "emotional pressure") but couldn\'t name specific techniques.',
                    'high' => 'Named specific tactics accurately—anchoring, false dichotomy, manufactured consensus—as they occurred.',
                    'exemplary' => 'Precisely identified all tactics including layered and secondary techniques. Could articulate the full playbook.',
                ],
                'improvement_tips' => [
                    'low' => 'Learn the names: anchoring, false dichotomy, manufactured consensus, sunk cost. Naming it breaks its power.',
                    'mid' => 'You can sense manipulation—now practice categorizing it. Ask: "What type of pressure is this?"',
                    'high' => 'Strong identification skills. Practice spotting layered tactics where multiple techniques combine.',
                ],
                'active' => true,
            ],
            [
                'key' => 'frame_awareness',
                'label' => 'Frame Awareness',
                'description' => 'Recognizing when you\'re operating inside someone else\'s frame. Noticing you\'re debating "how" when you never agreed to "whether".',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'Fully operated within manipulator\'s frame without noticing. Debated on their terms, accepted their premises.',
                    'mid' => 'Recognized the frame late, after making concessions within it. Felt constrained but couldn\'t articulate why.',
                    'high' => 'Identified the frame and stepped outside it. Refused to debate "how" when "whether" wasn\'t settled.',
                    'exemplary' => 'Immediately recognized false framing and set own terms. Proactively established the conversation\'s structure.',
                ],
                'improvement_tips' => [
                    'low' => 'If you\'re defending, ask: "Who set the terms of this debate?" Watch for questions that assume conclusions you haven\'t agreed to.',
                    'mid' => 'Practice stepping back: "Wait—should we even be discussing this?" Reject the premise before engaging with the question.',
                    'high' => 'You recognize frames well. Focus on proactively setting your own frame rather than just rejecting theirs.',
                ],
                'active' => true,
            ],
            [
                'key' => 'emotional_regulation',
                'label' => 'Emotional Regulation',
                'description' => 'Maintaining clear thinking under pressure, guilt, flattery, or urgency. Responding from assessment, not reaction.',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'Emotional response completely drove decision-making. Got swept up in guilt, flattery, or urgency without realizing it.',
                    'mid' => 'Recognized emotional pressure but it still landed. Managed some tactics but others hijacked clear thinking.',
                    'high' => 'Maintained composure throughout. Made decisions from reason, not reaction. Acknowledged emotions without being controlled.',
                    'exemplary' => 'Fully regulated under intense pressure. Used emotional awareness as data while staying completely grounded.',
                ],
                'improvement_tips' => [
                    'low' => 'Name the emotion they\'re trying to trigger: "I notice I feel guilty." This creates distance. Buy time with "Let me think about that."',
                    'mid' => 'You\'re aware of emotional pressure but it still lands. Practice pausing before responding—even 5 seconds helps.',
                    'high' => 'Strong regulation. Remember: flattery that feels too good is usually doing work. Stay skeptical of positive emotions too.',
                ],
                'active' => true,
            ],
            [
                'key' => 'premise_challenging',
                'label' => 'Premise Challenging',
                'description' => 'Questioning hidden assumptions before engaging with the surface question. "That assumes X—do we agree on X?"',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'Accepted all premises without question. Engaged with loaded questions and false dichotomies at face value.',
                    'mid' => 'Challenged some premises but missed others. Occasionally caught hidden assumptions but not consistently.',
                    'high' => 'Consistently questioned assumptions before engaging. Called out loaded questions and false choices.',
                    'exemplary' => 'Identified and rejected hidden premises, then reframed on own terms. Never answered a question that assumed too much.',
                ],
                'improvement_tips' => [
                    'low' => 'Before answering any question, ask yourself: "What does this question assume?" Loaded questions have hidden premises.',
                    'mid' => 'You catch some assumptions. Practice saying: "I don\'t agree with how that\'s framed" as a complete response.',
                    'high' => 'Strong premise awareness. Focus on reframing on your own terms rather than just rejecting theirs.',
                ],
                'active' => true,
            ],
            [
                'key' => 'boundary_assertion',
                'label' => 'Boundary Assertion',
                'description' => 'Clearly declining or redirecting without over-explaining or caving. Clean "no" vs. waffling, apologizing, or eventual capitulation.',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'No boundaries set. Agreed to requests, caved under pressure, or waffled until capitulating.',
                    'mid' => 'Held some boundaries but over-explained, apologized excessively, or weakened position through repetition.',
                    'high' => 'Set clear boundaries with minimal justification. Declined cleanly without defensiveness or excessive explanation.',
                    'exemplary' => 'Maintained firm boundaries calmly and warmly. Used silence effectively. Position was unmistakable yet relationship preserved.',
                ],
                'improvement_tips' => [
                    'low' => 'Start with small nos. You don\'t need to justify your position with reasons they\'ll accept. "No" is a complete sentence.',
                    'mid' => 'You set boundaries but weaken them with explanation. State your position once clearly—repetition weakens it.',
                    'high' => 'Strong boundary skills. Remember: silence after declining is powerful—don\'t fill it.',
                ],
                'active' => true,
            ],
            [
                'key' => 'counter_move',
                'label' => 'Counter-Move Execution',
                'description' => 'Deploying effective responses that shift the dynamic. Reframing, naming, redirecting—not just enduring.',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'No counter-moves attempted. Purely reactive or passive—endured rather than redirected.',
                    'mid' => 'Some pushback but manipulator maintained control. Resistance didn\'t shift the conversation dynamic.',
                    'high' => 'Deployed counter-moves that changed the flow. Asked burden-shifting questions, named tactics, or reframed effectively.',
                    'exemplary' => 'Skillfully took control of the dynamic. Turned manipulation attempts into opportunities to establish stronger footing.',
                ],
                'improvement_tips' => [
                    'low' => 'Move from passive to active. Try naming the tactic: "It sounds like you\'re creating urgency here." This shifts the dynamic.',
                    'mid' => 'You push back but don\'t take control. Ask questions that shift burden: "What makes you say that?" or "Help me understand."',
                    'high' => 'Strong counter-moves. Practice redirecting to your terms: "Before we go there, let\'s establish..."',
                ],
                'active' => true,
            ],
            [
                'key' => 'recovery',
                'label' => 'Recovery',
                'description' => 'Regaining footing after getting caught or partially conceding. Returning to solid ground mid-conversation.',
                'category' => 'manipulation_resistance',
                'score_anchors' => [
                    'low' => 'Once caught, stayed off-balance for remainder. Couldn\'t course-correct after missteps or concessions.',
                    'mid' => 'Partial recovery—stopped the bleeding but didn\'t regain ground. Recognized slip but struggled to reset.',
                    'high' => 'Recovered effectively after initial misstep. Used "let me reconsider" or similar to change direction mid-conversation.',
                    'exemplary' => 'Turned a caught moment into a stronger position. Used recovery as an opportunity to reset the entire dynamic.',
                ],
                'improvement_tips' => [
                    'low' => '"Actually, let me reconsider what I just said" is always available. A partial concession doesn\'t mean total surrender.',
                    'mid' => 'You recognize slips but struggle to recover. Practice: pause, breathe, reset. You can change direction mid-conversation.',
                    'high' => 'Strong recovery skills. Practice turning caught moments into opportunities to reset the entire dynamic.',
                ],
                'active' => true,
            ],
        ];

        foreach ($skills as $skill) {
            SkillDimension::updateOrCreate(
                ['key' => $skill['key']],
                $skill
            );
        }

        $this->command->info('Created/updated 8 manipulation resistance skill dimensions.');
    }
}
