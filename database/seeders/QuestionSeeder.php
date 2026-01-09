<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'text' => 'What belief are you defending that you\'re not sure is still true?',
                'principle' => 'Our beliefs shape how we interpret events. When we defend beliefs without evidence, we often miss opportunities to update our thinking and grow. The beliefs we hold most tightly are sometimes the ones most worth examining.',
                'intent_tag' => 'clarity',
                'active' => true,
            ],
            [
                'text' => 'If you weren\'t afraid of judgment, what would you say out loud?',
                'principle' => 'Fear of judgment often silences our most authentic thoughts. We carry unspoken truths that weigh us down, editing ourselves to fit expectations. What we suppress doesn\'t disappear—it shapes us in hidden ways.',
                'intent_tag' => 'avoidance',
                'active' => true,
            ],
            [
                'text' => 'What decision are you avoiding by staying busy?',
                'principle' => 'Busyness can be a sophisticated form of avoidance. When we fill every moment with tasks, we leave no space for the difficult choices that actually matter. Sometimes the most productive thing is to stop and face what we\'ve been running from.',
                'intent_tag' => 'avoidance',
                'active' => true,
            ],
            [
                'text' => 'Who would you be if you couldn\'t rely on being "right"?',
                'principle' => 'Much of our identity can get wrapped up in being correct. But needing to be right often prevents us from learning, connecting, and growing. The most interesting version of ourselves might emerge when we let go of certainty.',
                'intent_tag' => 'identity',
                'active' => true,
            ],
            [
                'text' => 'What matters to you that you\'re not making time for?',
                'principle' => 'Our calendars reveal our actual priorities, which often differ from our stated values. The gap between what we say matters and how we spend our time creates a quiet tension that affects our wellbeing.',
                'intent_tag' => 'values',
                'active' => true,
            ],
            [
                'text' => 'What would doing the right thing cost you right now?',
                'principle' => 'Integrity has a price. We often know what\'s right but hesitate because of what we\'d lose—comfort, approval, convenience. Understanding this cost clearly helps us make conscious choices about who we want to be.',
                'intent_tag' => 'leadership',
                'active' => true,
            ],
            [
                'text' => 'If someone described your life to you anonymously, what would surprise you?',
                'principle' => 'We live too close to our own lives to see them clearly. Patterns that would be obvious to an outsider often hide in plain sight. Stepping outside our perspective, even mentally, can reveal blind spots we\'ve normalized.',
                'intent_tag' => 'clarity',
                'active' => true,
            ],
            [
                'text' => 'What pattern in your relationships are you pretending not to see?',
                'principle' => 'Relationship patterns repeat until we acknowledge them. We often recognize dynamics we don\'t want to face—the same conflict, the same distance, the same disappointment. Naming the pattern is the first step to changing it.',
                'intent_tag' => 'avoidance',
                'active' => true,
            ],
            [
                'text' => 'What value are you compromising for the sake of comfort?',
                'principle' => 'Comfort and growth rarely coexist. We often trade what we believe in for what feels easy, eroding our integrity in small, daily choices. These compromises accumulate quietly until we no longer recognize ourselves.',
                'intent_tag' => 'values',
                'active' => true,
            ],
            [
                'text' => 'If you led by example today, what would you do differently?',
                'principle' => 'Leadership isn\'t a title—it\'s a choice we make moment by moment. When we imagine others watching and learning from our actions, we often find a gap between our current behavior and our highest standards.',
                'intent_tag' => 'leadership',
                'active' => true,
            ],
        ];

        foreach ($questions as $question) {
            Question::updateOrCreate(
                ['text' => $question['text']],
                $question
            );
        }
    }
}
