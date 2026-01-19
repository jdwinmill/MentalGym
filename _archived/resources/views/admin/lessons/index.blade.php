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
            <li class="text-gray-900 dark:text-white font-medium">Level {{ $skillLevel->level_number }}: {{ $skillLevel->name }}</li>
        </ol>
    </nav>

    <!-- Skill Context -->
    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3 mb-2">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 text-lg font-bold">
                {{ $skillLevel->level_number }}
            </span>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $skillLevel->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $track->name }}</p>
            </div>
        </div>
        @if($skillLevel->description)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ Str::limit($skillLevel->description, 150) }}</p>
        @endif
    </div>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Lessons</h1>
        <div class="mt-3 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.skill-levels.lessons.create', $skillLevel) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Lesson
            </a>
            <a href="{{ route('admin.tracks.skill-levels.index', $track) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                Back to Skills
            </a>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow overflow-x-auto sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Content</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Questions</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($lessons as $lesson)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $lesson->lesson_number }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                            <div class="font-medium">{{ $lesson->title }}</div>
                            @if($lesson->learning_objectives && count($lesson->learning_objectives) > 0)
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ count($lesson->learning_objectives) }} objective(s)
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $lesson->estimated_duration_minutes }} min
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $lesson->content_blocks_count }} blocks
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $lesson->questions_count }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            @if($lesson->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.lessons.content-blocks.index', $lesson) }}" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                Content
                            </a>
                            <a href="{{ route('admin.lessons.lesson-questions.index', $lesson) }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                Questions
                            </a>
                            <a href="{{ route('admin.lessons.edit', $lesson) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Edit
                            </a>
                            <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" class="inline" onsubmit="return confirm('{{ ($lesson->content_blocks_count > 0 || $lesson->questions_count > 0) ? "This lesson has {$lesson->content_blocks_count} content block(s) and {$lesson->questions_count} question(s). Deleting will remove all associated content. Are you sure?" : "Are you sure you want to delete this lesson?" }}');">
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
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No lessons found. <a href="{{ route('admin.skill-levels.lessons.create', $skillLevel) }}" class="text-indigo-600 hover:text-indigo-900">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($lessons->hasPages())
        <div class="mt-4">
            {{ $lessons->links() }}
        </div>
    @endif
</div>
@endsection
