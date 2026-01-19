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
            <li><a href="{{ route('admin.lessons.content-blocks.index', $lesson) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $lesson->title }}</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 dark:text-white font-medium">Edit Content Block</li>
        </ol>
    </nav>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Content Block</h1>
        <a href="{{ route('admin.lessons.content-blocks.index', $lesson) }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to Content
        </a>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form action="{{ route('admin.content-blocks.update', $contentBlock) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Context -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Editing:</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $lesson->title }}
                </p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2
                    @switch($contentBlock->block_type)
                        @case('audio') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 @break
                        @case('video') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @break
                        @case('principle_text') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break
                        @case('instruction_text') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @break
                        @case('image') bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200 @break
                    @endswitch
                ">
                    {{ ucwords(str_replace('_', ' ', $contentBlock->block_type)) }}
                </span>
            </div>

            <div class="space-y-6">
                @if($contentBlock->block_type === 'audio')
                    <!-- Audio Form -->
                    <div>
                        <label for="audio_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Audio URL <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="audio_url" id="audio_url"
                               value="{{ old('audio_url', $contentBlock->content['audio_url'] ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('audio_url')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Title
                            </label>
                            <input type="text" name="title" id="title"
                                   value="{{ old('title', $contentBlock->content['title'] ?? '') }}"
                                   maxlength="255"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="duration_seconds" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Duration (seconds)
                            </label>
                            <input type="number" name="duration_seconds" id="duration_seconds"
                                   value="{{ old('duration_seconds', $contentBlock->content['duration_seconds'] ?? '') }}"
                                   min="1"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="transcript" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Transcript
                        </label>
                        <textarea name="transcript" id="transcript" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('transcript', $contentBlock->content['transcript'] ?? '') }}</textarea>
                    </div>

                @elseif($contentBlock->block_type === 'video')
                    <!-- Video Form -->
                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Video URL <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="url" id="url"
                               value="{{ old('url', $contentBlock->content['url'] ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('url')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="duration_seconds" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Duration (seconds)
                            </label>
                            <input type="number" name="duration_seconds" id="duration_seconds"
                                   value="{{ old('duration_seconds', $contentBlock->content['duration_seconds'] ?? '') }}"
                                   min="1"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="thumbnail_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Thumbnail URL
                            </label>
                            <input type="text" name="thumbnail_url" id="thumbnail_url"
                                   value="{{ old('thumbnail_url', $contentBlock->content['thumbnail_url'] ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <input type="text" name="description" id="description"
                               value="{{ old('description', $contentBlock->content['description'] ?? '') }}"
                               maxlength="255"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                @elseif($contentBlock->block_type === 'principle_text' || $contentBlock->block_type === 'instruction_text')
                    <!-- Text Form -->
                    <div>
                        <label for="text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <textarea name="text" id="text" rows="8"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">{{ old('text', $contentBlock->content['text'] ?? '') }}</textarea>
                        @error('text')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Format
                        </label>
                        <select name="format" id="format"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="markdown" {{ old('format', $contentBlock->content['format'] ?? '') === 'markdown' ? 'selected' : '' }}>Markdown</option>
                            <option value="plain" {{ old('format', $contentBlock->content['format'] ?? '') === 'plain' ? 'selected' : '' }}>Plain Text</option>
                            <option value="html" {{ old('format', $contentBlock->content['format'] ?? '') === 'html' ? 'selected' : '' }}>HTML</option>
                        </select>
                    </div>

                @elseif($contentBlock->block_type === 'image')
                    <!-- Image Form -->
                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Image URL <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="url" id="url"
                               value="{{ old('url', $contentBlock->content['url'] ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('url')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    @if($contentBlock->content['url'] ?? null)
                        <div class="mt-2">
                            <img src="{{ $contentBlock->content['url'] }}" alt="Preview" class="max-w-xs rounded border border-gray-300 dark:border-gray-600">
                        </div>
                    @endif
                    <div>
                        <label for="alt_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alt Text <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="alt_text" id="alt_text"
                               value="{{ old('alt_text', $contentBlock->content['alt_text'] ?? '') }}"
                               maxlength="255"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('alt_text')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="caption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Caption
                        </label>
                        <textarea name="caption" id="caption" rows="2"
                                  maxlength="500"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('caption', $contentBlock->content['caption'] ?? '') }}</textarea>
                    </div>
                @endif

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sort Order
                    </label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="{{ old('sort_order', $contentBlock->sort_order) }}"
                           min="1"
                           class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Content Block
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
