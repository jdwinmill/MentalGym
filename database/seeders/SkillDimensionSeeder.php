<?php

namespace Database\Seeders;

use App\Models\SkillDimension;
use Illuminate\Database\Seeder;

class SkillDimensionSeeder extends Seeder
{
    public function run(): void
    {
        $dimensions = [
            // Communication
            [
                'key' => 'assertiveness',
                'label' => 'Assertiveness',
                'description' => 'The ability to express thoughts, feelings, and needs directly and respectfully, standing firm on important points while remaining open to dialogue.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Passive or overly aggressive; struggles to express needs clearly',
                    'mid' => 'Can state position but may back down under pressure or come across as pushy',
                    'high' => 'Confidently expresses views while respecting others; maintains stance appropriately',
                    'exemplary' => 'Masterfully balances firmness with empathy; inspires confidence without intimidation',
                ],
                'improvement_tips' => [
                    'low' => 'Use "I" statements to express your needs clearly: "I need..." or "I think..." Practice stating your position before explaining your reasoning.',
                    'mid' => 'When you feel pressure to back down, pause and restate your position calmly. Try the broken record technique: repeat your main point without getting defensive.',
                    'high' => 'Focus on calibrating your assertiveness to the situation. Practice reading when to push harder and when to create space for dialogue.',
                ],
            ],
            [
                'key' => 'perspective_taking',
                'label' => 'Perspective Taking',
                'description' => 'The capacity to understand situations from others\' points of view, recognizing their motivations, concerns, and mental models.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Focused solely on own viewpoint; dismisses or ignores others\' perspectives',
                    'mid' => 'Acknowledges other perspectives exist but struggles to deeply understand them',
                    'high' => 'Actively considers multiple viewpoints; adjusts communication accordingly',
                    'exemplary' => 'Intuitively grasps others\' mental models; bridges diverse perspectives seamlessly',
                ],
                'improvement_tips' => [
                    'low' => 'Before responding, ask yourself: "What might they be worried about?" and "What do they care about most?" Try explicitly naming their perspective.',
                    'mid' => 'Go deeper than surface-level acknowledgment. Ask clarifying questions about their concerns and reflect back what you heard before sharing your view.',
                    'high' => 'Practice articulating others\' positions so well that they would say "yes, exactly!" Bridge perspectives by finding shared goals.',
                ],
            ],
            [
                'key' => 'active_listening',
                'label' => 'Active Listening',
                'description' => 'Fully concentrating on what is being said, understanding the complete message including subtext and emotion, and responding thoughtfully.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Interrupts frequently; misses key information; focused on own response',
                    'mid' => 'Hears words but may miss subtext or emotional cues',
                    'high' => 'Fully present; picks up on verbal and non-verbal cues; confirms understanding',
                    'exemplary' => 'Creates space for others to feel heard; surfaces unspoken concerns; remembers details',
                ],
                'improvement_tips' => [
                    'low' => 'Resist the urge to formulate your response while the other person is talking. Instead, focus entirely on understanding their message first.',
                    'mid' => 'Listen for emotion behind the words. When someone says "it\'s fine," notice their tone. Reflect back both content and feeling: "It sounds like you\'re frustrated because..."',
                    'high' => 'Practice sitting with silence after someone finishes speaking. This creates space for them to share more and signals that you\'re not rushing to respond.',
                ],
            ],
            [
                'key' => 'clarity',
                'label' => 'Clarity',
                'description' => 'Communicating ideas in a clear, concise, and well-organized manner that makes complex information accessible and memorable.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Rambling or confusing; key points buried or unclear',
                    'mid' => 'Generally understandable but may include unnecessary complexity',
                    'high' => 'Clear, structured communication; main points are evident and memorable',
                    'exemplary' => 'Crystalline clarity; complex ideas made accessible; every word serves a purpose',
                ],
                'improvement_tips' => [
                    'low' => 'Lead with your main point (BLUF: Bottom Line Up Front). State your conclusion first, then provide supporting details. Cut your message in half.',
                    'mid' => 'Remove filler words and hedge phrases. Instead of "I kind of think maybe we should..." say "I recommend we..." Use concrete examples.',
                    'high' => 'Structure longer communications with clear signposts: "I have three points..." Practice the "so what?" test for every sentence.',
                ],
            ],
            [
                'key' => 'diplomatic_framing',
                'label' => 'Diplomatic Framing',
                'description' => 'Presenting difficult or sensitive information in ways that minimize defensiveness and maintain positive relationships.',
                'category' => 'communication',
                'score_anchors' => [
                    'low' => 'Blunt or insensitive; creates unnecessary friction or defensiveness',
                    'mid' => 'Attempts tact but may still trigger negative reactions',
                    'high' => 'Frames difficult messages constructively; maintains relationships while being honest',
                    'exemplary' => 'Transforms potential conflicts into collaborative discussions; delivers hard truths gracefully',
                ],
                'improvement_tips' => [
                    'low' => 'Before delivering difficult feedback, acknowledge something positive and genuine. Frame issues as shared problems: "We need to figure out..." not "You did this wrong."',
                    'mid' => 'Separate the person from the behavior. Focus on specific actions and their impact rather than character judgments. Use "and" instead of "but."',
                    'high' => 'Practice the "newspaper test" - would you be comfortable if your words were quoted? Master the art of being honest AND kind simultaneously.',
                ],
            ],

            // Reasoning
            [
                'key' => 'logical_structure',
                'label' => 'Logical Structure',
                'description' => 'Building arguments with clear premises, valid reasoning, and well-supported conclusions that flow coherently.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Arguments are disorganized or contain obvious logical gaps',
                    'mid' => 'Basic logical flow but may have weak connections or missing steps',
                    'high' => 'Well-structured reasoning with clear premises leading to conclusions',
                    'exemplary' => 'Impeccable logic; anticipates and addresses counterarguments; reasoning is airtight',
                ],
                'improvement_tips' => [
                    'low' => 'Use explicit structure: "My argument is X because of Y and Z." Make each logical step explicit rather than assuming it\'s obvious.',
                    'mid' => 'Check your reasoning chain: does each step follow from the previous? Look for hidden assumptions that need to be stated.',
                    'high' => 'Steel-man counterarguments before presenting your case. Address the strongest objection, not the weakest one.',
                ],
            ],
            [
                'key' => 'cognitive_flexibility',
                'label' => 'Cognitive Flexibility',
                'description' => 'The ability to adapt thinking, switch between concepts, and consider multiple perspectives or approaches simultaneously.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Rigid thinking; struggles to adapt when circumstances change',
                    'mid' => 'Can shift approaches but may take time or resist initially',
                    'high' => 'Readily adapts thinking to new information; explores alternative approaches',
                    'exemplary' => 'Fluidly moves between frameworks; sees connections others miss; thrives in ambiguity',
                ],
                'improvement_tips' => [
                    'low' => 'When you feel certain, force yourself to argue the opposite position for 2 minutes. Ask "what would have to be true for me to be wrong?"',
                    'mid' => 'Practice "yes, and..." thinking. When facing new information, look for how it adds to your understanding rather than whether it confirms your existing view.',
                    'high' => 'Regularly switch between different mental frameworks. Ask: "How would an economist see this? A psychologist? An engineer?"',
                ],
            ],
            [
                'key' => 'critical_analysis',
                'label' => 'Critical Analysis',
                'description' => 'Systematically evaluating information, arguments, and situations to identify strengths, weaknesses, and implications.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Accepts information at face value; shallow analysis',
                    'mid' => 'Questions some aspects but may miss deeper issues',
                    'high' => 'Systematically evaluates information; identifies strengths and weaknesses',
                    'exemplary' => 'Penetrating analysis; uncovers hidden implications; stress-tests ideas rigorously',
                ],
                'improvement_tips' => [
                    'low' => 'Before accepting any claim, ask: "What evidence supports this?" and "Who benefits from me believing this?" Look for what\'s NOT being said.',
                    'mid' => 'Use the "5 Whys" technique - keep asking "why?" to get to root causes. Look for second and third-order effects.',
                    'high' => 'Seek out disconfirming evidence actively. Ask "What would disprove this?" and then look for it.',
                ],
            ],
            [
                'key' => 'assumption_identification',
                'label' => 'Assumption Identification',
                'description' => 'Recognizing the unstated beliefs, premises, and conditions that underlie arguments, plans, or decisions.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Unaware of underlying assumptions; takes premises for granted',
                    'mid' => 'Recognizes obvious assumptions but misses subtle ones',
                    'high' => 'Proactively surfaces and examines key assumptions',
                    'exemplary' => 'Expertly uncovers hidden assumptions; tests their validity; considers alternatives',
                ],
                'improvement_tips' => [
                    'low' => 'For any plan or argument, ask: "What has to be true for this to work?" List at least 3 things being taken for granted.',
                    'mid' => 'Look for "of course" statements - things presented as obvious that might not be. Question industry conventions and "best practices."',
                    'high' => 'Create an assumption audit: categorize assumptions by how testable they are and how critical they are to your conclusion.',
                ],
            ],
            [
                'key' => 'evidence_evaluation',
                'label' => 'Evidence Evaluation',
                'description' => 'Assessing the quality, relevance, and reliability of information used to support claims or decisions.',
                'category' => 'reasoning',
                'score_anchors' => [
                    'low' => 'Ignores evidence or accepts weak evidence uncritically',
                    'mid' => 'Considers evidence but may not assess quality or relevance well',
                    'high' => 'Weighs evidence appropriately; distinguishes strong from weak support',
                    'exemplary' => 'Sophisticated evidence assessment; understands limitations; seeks disconfirming data',
                ],
                'improvement_tips' => [
                    'low' => 'For every claim, ask "How do we know this?" Distinguish between anecdotes, opinions, and data. Look for sample sizes and methodology.',
                    'mid' => 'Consider the source\'s incentives and expertise. Ask whether the evidence is relevant to the specific situation at hand.',
                    'high' => 'Actively seek evidence that would change your mind. Understand confidence intervals and the difference between correlation and causation.',
                ],
            ],

            // Resilience
            [
                'key' => 'emotional_regulation',
                'label' => 'Emotional Regulation',
                'description' => 'Managing emotional responses effectively, processing feelings without being controlled by them, especially in challenging situations.',
                'category' => 'resilience',
                'score_anchors' => [
                    'low' => 'Emotions frequently overwhelm; reactive responses dominate',
                    'mid' => 'Generally manages emotions but struggles under high stress',
                    'high' => 'Maintains composure; processes emotions without being controlled by them',
                    'exemplary' => 'Exceptional emotional mastery; uses emotions as data; models calm for others',
                ],
                'improvement_tips' => [
                    'low' => 'Practice the STOP technique: Stop, Take a breath, Observe your feelings, Proceed thoughtfully. Name the emotion you\'re feeling.',
                    'mid' => 'Create space between stimulus and response. Use phrases like "Let me think about that" to buy processing time.',
                    'high' => 'Treat emotions as information rather than directives. Ask "What is this feeling telling me?" without automatically acting on it.',
                ],
            ],
            [
                'key' => 'pressure_composure',
                'label' => 'Pressure Composure',
                'description' => 'Maintaining effectiveness and clear thinking when facing high-stakes situations, tight deadlines, or intense scrutiny.',
                'category' => 'resilience',
                'score_anchors' => [
                    'low' => 'Falls apart under pressure; performance degrades significantly',
                    'mid' => 'Functions under pressure but shows visible strain',
                    'high' => 'Maintains effectiveness under pressure; stays focused on priorities',
                    'exemplary' => 'Thrives under pressure; elevates performance when stakes are highest',
                ],
                'improvement_tips' => [
                    'low' => 'Prepare for high-pressure moments in advance. Have a pre-game routine. Focus on what you can control, not the stakes.',
                    'mid' => 'Use box breathing (4-4-4-4) to activate your parasympathetic nervous system. Reframe pressure as excitement rather than threat.',
                    'high' => 'Develop mental models for different pressure scenarios. Practice "pre-mortems" to reduce surprise and increase confidence.',
                ],
            ],
            [
                'key' => 'recovery_speed',
                'label' => 'Recovery Speed',
                'description' => 'The ability to bounce back quickly from setbacks, failures, or disappointments and return to productive functioning.',
                'category' => 'resilience',
                'score_anchors' => [
                    'low' => 'Setbacks cause prolonged disruption; slow to bounce back',
                    'mid' => 'Recovers eventually but may dwell on failures',
                    'high' => 'Bounces back quickly; learns from setbacks and moves forward',
                    'exemplary' => 'Remarkably resilient; transforms setbacks into fuel; inspires others\' recovery',
                ],
                'improvement_tips' => [
                    'low' => 'After a setback, set a time limit for processing (e.g., 24 hours) then shift to "what can I learn?" Create a simple next action.',
                    'mid' => 'Separate "what happened" from "what it means about me." Setbacks are events, not verdicts on your worth or ability.',
                    'high' => 'Build a "setback playbook" - pre-planned responses for common failures. Focus on maintaining momentum rather than perfection.',
                ],
            ],
            [
                'key' => 'defensiveness_management',
                'label' => 'Defensiveness Management',
                'description' => 'Receiving criticism or challenges without becoming defensive, separating ego from ideas to engage constructively.',
                'category' => 'resilience',
                'score_anchors' => [
                    'low' => 'Highly defensive; perceived attacks trigger shutdown or counterattack',
                    'mid' => 'Initial defensiveness but can eventually hear feedback',
                    'high' => 'Receives criticism openly; separates ego from ideas',
                    'exemplary' => 'Welcomes challenges to thinking; creates safety for others to challenge them',
                ],
                'improvement_tips' => [
                    'low' => 'When you feel defensive, pause before responding. Say "Tell me more" to buy time and signal openness even when you don\'t feel it.',
                    'mid' => 'Separate your ideas from your identity. Treat feedback on your work as information about the work, not about you as a person.',
                    'high' => 'Actively seek criticism before it comes. Ask "What\'s the weakest part of this?" This gives you control and shows confidence.',
                ],
            ],
            [
                'key' => 'uncertainty_tolerance',
                'label' => 'Uncertainty Tolerance',
                'description' => 'Functioning effectively when facing ambiguous situations, incomplete information, or unpredictable outcomes.',
                'category' => 'resilience',
                'score_anchors' => [
                    'low' => 'Paralyzed by ambiguity; needs complete information to act',
                    'mid' => 'Uncomfortable with uncertainty but can function',
                    'high' => 'Comfortable making decisions with incomplete information',
                    'exemplary' => 'Embraces uncertainty as opportunity; makes sound judgments in fog of ambiguity',
                ],
                'improvement_tips' => [
                    'low' => 'Ask "What\'s the minimum I need to know to take the next small step?" Focus on reversible decisions that let you learn more.',
                    'mid' => 'Distinguish between uncertainty that matters and uncertainty that doesn\'t. Not all unknowns affect your decision equally.',
                    'high' => 'Frame uncertainty as optionality. Practice making "good enough" decisions quickly rather than perfect decisions slowly.',
                ],
            ],
            [
                'key' => 'stress_management',
                'label' => 'Stress Management',
                'description' => 'Effectively managing stress levels to maintain performance and well-being during challenging periods.',
                'category' => 'resilience',
                'score_anchors' => [
                    'low' => 'Stress significantly impairs functioning; lacks coping strategies',
                    'mid' => 'Manages routine stress but struggles with intense or prolonged pressure',
                    'high' => 'Maintains effectiveness under stress; employs healthy coping mechanisms',
                    'exemplary' => 'Transforms stress into productive energy; models healthy stress management for others',
                ],
                'improvement_tips' => [
                    'low' => 'Build basic stress hygiene: sleep, exercise, breaks. Identify your early warning signs of stress overload.',
                    'mid' => 'Develop multiple stress release valves - don\'t rely on just one. Schedule recovery time rather than hoping it happens.',
                    'high' => 'Use stress as a signal to check alignment. Chronic stress often indicates a mismatch between demands and resources.',
                ],
            ],
            [
                'key' => 'self_confidence',
                'label' => 'Self-Confidence',
                'description' => 'Trusting in one\'s abilities and judgment while remaining open to feedback and growth.',
                'category' => 'resilience',
                'score_anchors' => [
                    'low' => 'Excessive self-doubt; seeks constant validation; avoids taking stands',
                    'mid' => 'Generally confident but shaken by criticism or setbacks',
                    'high' => 'Secure in abilities; takes appropriate risks; handles criticism constructively',
                    'exemplary' => 'Grounded confidence that inspires others; admits limitations without losing credibility',
                ],
                'improvement_tips' => [
                    'low' => 'Keep a "wins" journal - record small successes daily. Before seeking validation, ask yourself what YOU think first.',
                    'mid' => 'Build confidence through competence. Identify skill gaps and address them directly. Prepare thoroughly for important moments.',
                    'high' => 'Practice stating your view first in meetings. Be comfortable being wrong sometimes - it builds credibility when you\'re right.',
                ],
            ],

            // Influence
            [
                'key' => 'persuasion',
                'label' => 'Persuasion',
                'description' => 'Moving others toward a particular viewpoint or action through compelling arguments, emotional appeal, and credibility.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Arguments fail to move others; comes across as unconvincing',
                    'mid' => 'Can persuade in straightforward situations but struggles with resistance',
                    'high' => 'Effectively tailors arguments to audience; builds compelling cases',
                    'exemplary' => 'Masterful persuader; changes minds on difficult topics; creates lasting buy-in',
                ],
                'improvement_tips' => [
                    'low' => 'Start with what your audience cares about, not what you care about. Lead with "what\'s in it for them" before your reasoning.',
                    'mid' => 'Use social proof and concrete examples. Abstract arguments rarely persuade; stories and specific cases do.',
                    'high' => 'Master the art of letting people convince themselves. Ask questions that lead to your conclusion rather than asserting it.',
                ],
            ],
            [
                'key' => 'negotiation_leverage',
                'label' => 'Negotiation Leverage',
                'description' => 'Identifying and utilizing sources of power in negotiations to achieve favorable outcomes while building relationships.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Gives away value; fails to recognize or use leverage',
                    'mid' => 'Basic negotiation skills but may leave value on table',
                    'high' => 'Identifies and uses leverage effectively; creates win-win outcomes',
                    'exemplary' => 'Expert at finding hidden leverage; expands the pie; builds long-term relationships',
                ],
                'improvement_tips' => [
                    'low' => 'Know your BATNA (Best Alternative To Negotiated Agreement) before any negotiation. Never negotiate without knowing your walkaway point.',
                    'mid' => 'Look for interests behind positions. Ask "why is that important to you?" to find creative solutions that satisfy both parties.',
                    'high' => 'Focus on expanding the pie before dividing it. Identify variables where you have different valuations and trade strategically.',
                ],
            ],
            [
                'key' => 'stakeholder_reading',
                'label' => 'Stakeholder Reading',
                'description' => 'Accurately assessing the interests, motivations, concerns, and influence of different parties in a situation.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Misreads stakeholder interests and motivations',
                    'mid' => 'Understands obvious stakeholder needs but misses subtleties',
                    'high' => 'Accurately assesses stakeholder landscape; anticipates concerns',
                    'exemplary' => 'Intuitively maps complex stakeholder dynamics; predicts reactions precisely',
                ],
                'improvement_tips' => [
                    'low' => 'Before any important interaction, write down: What do they want? What do they fear? What pressures are they under?',
                    'mid' => 'Look for hidden stakeholders - who else is affected by the decision? Who influences the decision-maker?',
                    'high' => 'Map the informal influence network, not just the org chart. Understand who has the ear of key decision-makers.',
                ],
            ],
            [
                'key' => 'objection_handling',
                'label' => 'Objection Handling',
                'description' => 'Addressing concerns, resistance, or counterarguments in ways that resolve issues and advance the conversation.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Objections derail or frustrate; responses are weak or dismissive',
                    'mid' => 'Addresses objections but may not fully resolve concerns',
                    'high' => 'Handles objections smoothly; turns concerns into opportunities',
                    'exemplary' => 'Anticipates objections before they arise; makes challengers feel heard and valued',
                ],
                'improvement_tips' => [
                    'low' => 'Welcome objections as buying signals - they mean engagement. Respond with "I\'m glad you raised that" and address specifically.',
                    'mid' => 'Use the feel-felt-found framework: "I understand how you feel. Others have felt the same way. What they found was..."',
                    'high' => 'Preempt common objections by raising them yourself. This builds credibility and removes ammunition.',
                ],
            ],
            [
                'key' => 'timing_awareness',
                'label' => 'Timing Awareness',
                'description' => 'Recognizing when conditions are favorable for action, knowing when to push forward and when to wait.',
                'category' => 'influence',
                'score_anchors' => [
                    'low' => 'Poor sense of timing; raises issues at wrong moments',
                    'mid' => 'Generally appropriate timing but may miss optimal windows',
                    'high' => 'Good instincts for when to push and when to wait',
                    'exemplary' => 'Impeccable timing; knows exactly when conditions are right; patient yet decisive',
                ],
                'improvement_tips' => [
                    'low' => 'Before raising an issue, ask: Is this person in a receptive state? Is this the right forum? Is this the right time?',
                    'mid' => 'Watch for windows of opportunity - after wins, during crises, at transition points. These are moments of openness.',
                    'high' => 'Develop patience muscle. Sometimes the best action is to wait. Practice "strategic silence" and let situations develop.',
                ],
            ],

            // Self-Awareness
            [
                'key' => 'blind_spot_recognition',
                'label' => 'Blind Spot Recognition',
                'description' => 'Identifying areas where one\'s perception or judgment may be limited or skewed without awareness.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Unaware of own blind spots; surprised by feedback',
                    'mid' => 'Acknowledges blind spots exist but struggles to identify them',
                    'high' => 'Actively works to uncover and address blind spots',
                    'exemplary' => 'Proactively seeks out blind spots; creates systems to catch them; helps others see theirs',
                ],
                'improvement_tips' => [
                    'low' => 'Ask trusted colleagues: "What\'s something I do that I might not be aware of?" Accept feedback without defending.',
                    'mid' => 'Keep a "surprise journal" - when outcomes differ from expectations, explore what you missed.',
                    'high' => 'Create formal feedback loops. Assign someone to play devil\'s advocate. Seek out people with different perspectives.',
                ],
            ],
            [
                'key' => 'bias_detection',
                'label' => 'Bias Detection',
                'description' => 'Recognizing when cognitive biases may be influencing one\'s thinking or decisions.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Unaware of own biases; decisions heavily influenced by hidden preferences',
                    'mid' => 'Recognizes common biases intellectually but struggles to catch them in action',
                    'high' => 'Actively monitors for biases; adjusts decision-making accordingly',
                    'exemplary' => 'Sophisticated understanding of own biases; builds debiasing into process',
                ],
                'improvement_tips' => [
                    'low' => 'Learn the common biases: confirmation bias, anchoring, availability heuristic. Ask "What would I believe if I had different information?"',
                    'mid' => 'Before important decisions, explicitly check for common biases. Use a checklist if needed.',
                    'high' => 'Build debiasing into your process: seek disconfirming evidence, consider the opposite, use base rates.',
                ],
            ],
            [
                'key' => 'overconfidence_calibration',
                'label' => 'Overconfidence Calibration',
                'description' => 'Accurately assessing the limits of one\'s knowledge and abilities, matching confidence to actual competence.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Significantly over or underestimates own abilities and knowledge',
                    'mid' => 'Reasonable self-assessment but blind spots in certain areas',
                    'high' => 'Well-calibrated confidence; knows what they know and don\'t know',
                    'exemplary' => 'Precisely calibrated; confidence matches actual ability; comfortable saying "I don\'t know"',
                ],
                'improvement_tips' => [
                    'low' => 'Track your predictions and confidence levels. Review regularly to calibrate. Most people are overconfident 80% of the time.',
                    'mid' => 'Express uncertainty in ranges rather than point estimates. "I\'m 70% confident" is more useful than "I think so."',
                    'high' => 'Model intellectual humility. Saying "I don\'t know" or "I was wrong" builds credibility, not weakens it.',
                ],
            ],
            [
                'key' => 'emotional_triggers',
                'label' => 'Emotional Triggers',
                'description' => 'Understanding what situations, words, or behaviors tend to provoke strong emotional reactions in oneself.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Unaware of what triggers emotional reactions; blindsided by own responses',
                    'mid' => 'Knows some triggers but still caught off guard',
                    'high' => 'Clear understanding of triggers; can anticipate and manage reactions',
                    'exemplary' => 'Deep self-knowledge of triggers; uses awareness to maintain effectiveness',
                ],
                'improvement_tips' => [
                    'low' => 'Keep an emotion log for a week. Note when you felt strongly and what preceded it. Look for patterns.',
                    'mid' => 'Identify your top 3 triggers and create specific plans for when they occur. Prepare responses in advance.',
                    'high' => 'Share your triggers with trusted colleagues so they can help you notice when you\'re activated.',
                ],
            ],
            [
                'key' => 'feedback_receptivity',
                'label' => 'Feedback Receptivity',
                'description' => 'Openness to receiving and acting on input from others about one\'s performance or behavior.',
                'category' => 'self_awareness',
                'score_anchors' => [
                    'low' => 'Resistant to feedback; dismisses or argues against input',
                    'mid' => 'Accepts feedback but may not fully integrate it',
                    'high' => 'Actively seeks and incorporates feedback; shows visible growth',
                    'exemplary' => 'Treats feedback as gift; creates psychological safety for honest input; models receptivity',
                ],
                'improvement_tips' => [
                    'low' => 'When receiving feedback, your only job is to understand it. Say "thank you" and "tell me more" - save evaluation for later.',
                    'mid' => 'Create a feedback action plan: pick one piece of feedback and commit to specific behavior change. Follow up with the giver.',
                    'high' => 'Make it easy for people to give you feedback. Ask specific questions like "What\'s one thing I could do better in meetings?"',
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
}
