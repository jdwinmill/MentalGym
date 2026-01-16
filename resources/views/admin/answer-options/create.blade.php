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
            <li><a href="{{ route('admin.lesson-questions.answer-options.index', $lessonQuestion) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Answers</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 dark:text-white font-medium">New Answer</li>
        </ol>
    </nav>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Add Answer Option</h1>
        <a href="{{ route('admin.lesson-questions.answer-options.index', $lessonQuestion) }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to Answers
        </a>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form action="{{ route('admin.lesson-questions.answer-options.store', $lessonQuestion) }}" method="POST" class="p-6">
            @csrf

            <!-- Question Context -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Adding answer to:</p>
                <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $lessonQuestion->question_text }}</p>
            </div>

            <div class="space-y-6">
                <div>
                    <label for="option_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Answer Text <span class="text-red-500">*</span>
                    </label>
                    <textarea name="option_text" id="option_text" rows="3" required
                              maxlength="500"
                              placeholder="Enter the answer option text..."
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('option_text') }}</textarea>
                    @error('option_text')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center">
                        <input type="hidden" name="is_correct" value="0">
                        <input type="checkbox" name="is_correct" id="is_correct" value="1"
                               {{ old('is_correct') ? 'checked' : '' }}
                               class="h-5 w-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <label for="is_correct" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            This is a correct answer
                        </label>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Sort Order
                        </label>
                        <input type="number" name="sort_order" id="sort_order"
                               value="{{ old('sort_order', $nextSortOrder) }}"
                               min="1"
                               class="mt-1 block w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit" name="save_and_add_another" value="1"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Save & Add Another
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Answer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
