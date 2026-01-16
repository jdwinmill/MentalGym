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
            <li class="text-gray-900 dark:text-white font-medium">{{ $lesson->title }} - Questions</li>
        </ol>
    </nav>

    <!-- Lesson Context -->
    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3 mb-2">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 text-lg font-bold">
                {{ $lesson->lesson_number }}
            </span>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $lesson->title }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $track->name }} &rarr; Level {{ $skillLevel->level_number }}</p>
            </div>
        </div>
    </div>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Questions</h1>
        <div class="mt-3 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.lessons.lesson-questions.create', $lesson) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Question
            </a>
            <a href="{{ route('admin.skill-levels.lessons.index', $skillLevel) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                Back to Lessons
            </a>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow overflow-x-auto sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Question</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Points</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Answers</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($questions as $question)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $question->sort_order }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                            <div class="truncate max-w-md">{{ Str::limit($question->question_text, 80) }}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            @switch($question->question_type)
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
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $question->points }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            @php
                                $hasCorrect = $question->answerOptions->contains('is_correct', true);
                            @endphp
                            <span class="{{ $hasCorrect ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                                {{ $question->answer_options_count }}
                                @if(!$hasCorrect && $question->answer_options_count > 0)
                                    <span class="text-xs">(no correct)</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.lesson-questions.answer-options.index', $question) }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                Answers
                            </a>
                            <a href="{{ route('admin.lesson-questions.edit', $question) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Edit
                            </a>
                            <form action="{{ route('admin.lesson-questions.destroy', $question) }}" method="POST" class="inline" onsubmit="return confirm('{{ $question->answer_options_count > 0 ? "This question has {$question->answer_options_count} answer(s). Delete anyway?" : "Are you sure you want to delete this question?" }}');">
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
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No questions found. <a href="{{ route('admin.lessons.lesson-questions.create', $lesson) }}" class="text-indigo-600 hover:text-indigo-900">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
