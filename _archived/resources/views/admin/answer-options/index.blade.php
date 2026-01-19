@extends('admin.layout')

@section('content')
<div class="px-4 sm:px-0">
    <!-- Breadcrumb -->
    <nav class="mb-4 text-sm">
        <ol class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('admin.tracks.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Tracks</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('admin.tracks.skill-levels.index', $track) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $track->name }}</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('admin.skill-levels.lessons.index', $skillLevel) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Level {{ $skillLevel->level_number }}</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('admin.lessons.lesson-questions.index', $lesson) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $lesson->title }}</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 dark:text-white font-medium">Answers</li>
        </ol>
    </nav>

    <!-- Question Context -->
    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Question:</p>
        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $lessonQuestion->question_text }}</p>
        <div class="mt-2 flex items-center gap-3">
            @switch($lessonQuestion->question_type)
                @case('multiple_choice')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Multiple Choice
                    </span>
                    @break
                @case('true_false')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        True/False
                    </span>
                    @break
                @case('open_ended')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        Open Ended
                    </span>
                    @break
            @endswitch
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $lessonQuestion->points }} points</span>
        </div>
    </div>

    @if(!$hasCorrectAnswer && $answerOptions->count() > 0)
        <div class="mb-4 px-4 py-3 bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 rounded">
            Warning: This question has no correct answer marked. At least one answer should be marked as correct.
        </div>
    @endif

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Answer Options</h1>
        <div class="mt-3 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.lesson-questions.answer-options.create', $lessonQuestion) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Answer
            </a>
            <a href="{{ route('admin.lessons.lesson-questions.index', $lesson) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                Back to Questions
            </a>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow overflow-x-auto sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Answer Text</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Correct</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($answerOptions as $option)
                    <tr class="{{ $option->is_correct ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $option->sort_order }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                            {{ $option->option_text }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            @if($option->is_correct)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Correct
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Incorrect
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.answer-options.edit', $option) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Edit
                            </a>
                            <form action="{{ route('admin.answer-options.destroy', $option) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ $option->is_correct && $answerOptions->where('is_correct', true)->count() === 1 ? "This is the only correct answer. Deleting will leave no correct answer. Continue?" : "Are you sure you want to delete this answer option?" }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No answer options found. <a href="{{ route('admin.lesson-questions.answer-options.create', $lessonQuestion) }}" class="text-indigo-600 hover:text-indigo-900">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
