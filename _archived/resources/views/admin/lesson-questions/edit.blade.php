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
            <li class="text-gray-900 dark:text-white font-medium">Edit Question</li>
        </ol>
    </nav>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Question</h1>
        <a href="{{ route('admin.lessons.lesson-questions.index', $lesson) }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to Questions
        </a>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form action="{{ route('admin.lesson-questions.update', $lessonQuestion) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="question_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Question Text <span class="text-red-500">*</span>
                    </label>
                    <textarea name="question_text" id="question_text" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('question_text', $lessonQuestion->question_text) }}</textarea>
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
                            <option value="multiple_choice" {{ old('question_type', $lessonQuestion->question_type) === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                            <option value="true_false" {{ old('question_type', $lessonQuestion->question_type) === 'true_false' ? 'selected' : '' }}>True/False</option>
                            <option value="open_ended" {{ old('question_type', $lessonQuestion->question_type) === 'open_ended' ? 'selected' : '' }}>Open Ended</option>
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
                               value="{{ old('points', $lessonQuestion->points) }}"
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
                               value="{{ old('sort_order', $lessonQuestion->sort_order) }}"
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
                                <option value="{{ $block->id }}" {{ old('related_block_id', $lessonQuestion->related_block_id) == $block->id ? 'selected' : '' }}>
                                    {{ $block->sort_order }}. {{ ucwords(str_replace('_', ' ', $block->block_type)) }}
                                    @if($block->isText())
                                        - {{ Str::limit($block->getText(), 40) }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="explanation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Explanation
                    </label>
                    <textarea name="explanation" id="explanation" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('explanation', $lessonQuestion->explanation) }}</textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Question
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Answer Options Section -->
    <div class="mt-8">
        <div class="sm:flex sm:items-center sm:justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Answer Options</h2>
            <a href="{{ route('admin.lesson-questions.answer-options.create', $lessonQuestion) }}"
               class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                Add Answer
            </a>
        </div>

        @php
            $hasCorrect = $answerOptions->contains('is_correct', true);
        @endphp

        @if($answerOptions->count() > 0 && !$hasCorrect)
            <div class="mb-4 px-4 py-3 bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 rounded">
                Warning: This question has no correct answer marked.
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
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
                                <div class="truncate max-w-md">{{ Str::limit($option->option_text, 80) }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                @if($option->is_correct)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Correct
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.answer-options.edit', $option) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Edit
                                </a>
                                <form action="{{ route('admin.answer-options.destroy', $option) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this answer option?');">
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
                                No answer options. <a href="{{ route('admin.lesson-questions.answer-options.create', $lessonQuestion) }}" class="text-indigo-600 hover:text-indigo-900">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
