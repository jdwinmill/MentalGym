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
            <li class="text-gray-900 dark:text-white font-medium">{{ $lesson->title }} - Content</li>
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
        @if($lesson->learning_objectives && count($lesson->learning_objectives) > 0)
            <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">Objectives:</span> {{ implode(', ', array_slice($lesson->learning_objectives, 0, 2)) }}{{ count($lesson->learning_objectives) > 2 ? '...' : '' }}
            </div>
        @endif
    </div>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Content Blocks</h1>
        <div class="mt-3 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.lessons.content-blocks.create', $lesson) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Content Block
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Preview</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($contentBlocks as $block)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $block->sort_order }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                            @switch($block->block_type)
                                @case('audio')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        Audio
                                    </span>
                                    @break
                                @case('video')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Video
                                    </span>
                                    @break
                                @case('principle_text')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Principle Text
                                    </span>
                                    @break
                                @case('instruction_text')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Instruction Text
                                    </span>
                                    @break
                                @case('image')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200">
                                        Image
                                    </span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                            @if($block->isAudio() || $block->isVideo())
                                <div class="truncate max-w-xs">
                                    {{ $block->getMediaUrl() ? basename($block->getMediaUrl()) : 'No URL' }}
                                    @if($block->content['duration_seconds'] ?? null)
                                        <span class="text-xs text-gray-400">({{ gmdate('i:s', $block->content['duration_seconds']) }})</span>
                                    @endif
                                </div>
                            @elseif($block->isText())
                                <div class="truncate max-w-xs">
                                    {{ Str::limit($block->getText(), 80) }}
                                </div>
                            @elseif($block->isImage())
                                <div class="truncate max-w-xs">
                                    {{ $block->content['alt_text'] ?? 'Image' }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.content-blocks.edit', $block) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Edit
                            </a>
                            <form action="{{ route('admin.content-blocks.destroy', $block) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this content block?');">
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
                            No content blocks found. <a href="{{ route('admin.lessons.content-blocks.create', $lesson) }}" class="text-indigo-600 hover:text-indigo-900">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
