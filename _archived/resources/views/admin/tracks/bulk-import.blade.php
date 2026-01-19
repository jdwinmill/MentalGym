@extends('admin.layout')

@section('content')
<div class="px-4 sm:px-0">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Bulk Track Import</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Import a complete track with all skill levels, lessons, content blocks, questions, and answers.
            </p>
        </div>
        <a href="{{ route('admin.tracks.index') }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to Tracks
        </a>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form action="{{ route('admin.tracks.bulk-import.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-6">
                <!-- JSON Input -->
                <div>
                    <label for="json_data" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Track JSON Data <span class="text-red-500">*</span>
                    </label>
                    <textarea name="json_data" id="json_data" rows="25" required
                              placeholder='Paste your track JSON here...'
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('json_data') }}</textarea>
                    @error('json_data')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expected JSON Structure -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expected JSON Structure:</h3>
                    <pre class="text-xs text-gray-600 dark:text-gray-400 overflow-x-auto"><code>{
  "name": "Track Name",
  "slug": "track-slug",
  "description": "Track description",
  "pitch": "Short tagline",
  "duration_weeks": 4,
  "sessions_per_week": 3,
  "session_duration_minutes": 10,
  "is_active": true,
  "sort_order": 1,
  "skill_levels": [
    {
      "name": "Skill Level Name",
      "slug": "skill-slug",
      "description": "Skill description",
      "level_number": 1,
      "pass_threshold": 0.8,
      "lessons": [
        {
          "title": "Lesson Title",
          "lesson_number": 1,
          "estimated_duration_minutes": 5,
          "learning_objectives": ["Objective 1", "Objective 2"],
          "is_active": true,
          "content_blocks": [
            {
              "block_type": "audio",
              "sort_order": 1,
              "content": {
                "audio_url": "audio/file.mp3",
                "title": "Audio Title",
                "transcript": "...",
                "duration_seconds": 60
              }
            }
          ],
          "questions": [
            {
              "question_text": "Question?",
              "question_type": "multiple_choice",
              "explanation": "Explanation...",
              "points": 10,
              "sort_order": 1,
              "answer_options": [
                {
                  "option_text": "Answer A",
                  "is_correct": true,
                  "sort_order": 1
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}</code></pre>
                </div>

                @if(session('dry_run_summary'))
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-3">Dry Run Summary - What would be created:</h3>
                        @php $summary = session('dry_run_summary'); @endphp
                        <div class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                            <div class="font-semibold text-blue-900 dark:text-blue-100">
                                Track: {{ $summary['track']['name'] }} ({{ $summary['track']['slug'] }})
                            </div>
                            <ul class="list-disc list-inside ml-4 space-y-1">
                                <li><strong>{{ $summary['counts']['skill_levels'] }}</strong> Skill Level(s)</li>
                                <li><strong>{{ $summary['counts']['lessons'] }}</strong> Lesson(s)</li>
                                <li><strong>{{ $summary['counts']['content_blocks'] }}</strong> Content Block(s)</li>
                                <li><strong>{{ $summary['counts']['questions'] }}</strong> Question(s)</li>
                                <li><strong>{{ $summary['counts']['answer_options'] }}</strong> Answer Option(s)</li>
                            </ul>

                            @if(count($summary['details']) > 0)
                                <div class="mt-4 pt-3 border-t border-blue-200 dark:border-blue-700">
                                    <div class="font-medium mb-2">Structure Preview:</div>
                                    @foreach($summary['details'] as $skill)
                                        <div class="ml-2 mb-2">
                                            <div class="font-medium">Level {{ $skill['level_number'] }}: {{ $skill['name'] }}</div>
                                            @foreach($skill['lessons'] as $lesson)
                                                <div class="ml-4 text-xs">
                                                    Lesson {{ $lesson['lesson_number'] }}: {{ $lesson['title'] }}
                                                    <span class="text-blue-500">({{ $lesson['content_blocks'] }} blocks, {{ $lesson['questions'] }} questions)</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <p class="mt-3 text-xs text-blue-600 dark:text-blue-400">
                            This is a preview only. Click "Import Track" to create these records.
                        </p>
                    </div>
                @endif

                <input type="hidden" name="dry_run" id="dry_run" value="0">

                <div class="flex justify-end gap-3">
                    <button type="submit" onclick="document.getElementById('dry_run').value='1'"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Test
                    </button>
                    <button type="submit" onclick="document.getElementById('dry_run').value='0'"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Import Track
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
