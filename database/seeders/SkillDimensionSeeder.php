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
