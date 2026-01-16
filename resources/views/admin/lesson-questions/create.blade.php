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
            <li class="text-gray-900 dark:text-white font-medium">New Question</li>
        </ol>
    </nav>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Add Question</h1>
        <a href="{{ route('admin.lessons.lesson-questions.index', $lesson) }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to Questions
        </a>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form action="{{ route('admin.lessons.lesson-questions.store', $lesson) }}" method="POST" class="p-6">
            @csrf

            <!-- Context -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Adding to:</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $lesson->title }}
                </p>
            </div>

            <div class="space-y-6">
                <div>
                    <label for="question_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Question Text <span class="text-red-500">*</span>
                    </label>
                    <textarea name="question_text" id="question_text" rows="3" required
                              placeholder="What was the main factual claim made by the speaker?"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('question_text') }}</textarea>
                    @error('question_text')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="question_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Question Type <span class="text-red-500">*</span>
                        </label>
                        <select name="question_type" id="question_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="multiple_choice" {{ old('question_type') === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="true_false" {{ old('question_type') === 'true_false' ? 'selected' : '' }}>True/False</option>
                            <option value="open_ended" {{ old('question_type') === 'open_ended' ? 'selected' : '' }}>Open Ended</option>
                        </select>
                        @error('question_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="points" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Points <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="points" id="points"
                               value="{{ old('points', 10) }}"
                               required min="1" max="100"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('points')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Sort Order
                        </label>
                        <input type="number" name="sort_order" id="sort_order"
                               value="{{ old('sort_order', $nextSortOrder) }}"
                               min="1"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                @if($contentBlocks->count() > 0)
                    <div>
                        <label for="related_block_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Related Content Block
                        </label>
                        <select name="related_block_id" id="related_block_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- None --</option>
                            @foreach($contentBlocks as $block)
                                <option value="{{ $block->id }}" {{ old('related_block_id') == $block->id ? 'selected' : '' }}>
                                    {{ $block->sort_order }}. {{ ucwords(str_replace('_', ' ', $block->block_type)) }}
                                    @if($block->isText())
                                        - {{ Str::limit($block->getText(), 40) }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Link this question to specific content</p>
                    </div>
                @endif

                <div>
                    <label for="explanation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Explanation
                    </label>
                    <textarea name="explanation" id="explanation" rows="3"
                              placeholder="Explain why the correct answer is correct..."
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('explanation') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Shown after user answers</p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit" name="save_and_add_answers" value="1"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Create & Add Answers
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Question
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
