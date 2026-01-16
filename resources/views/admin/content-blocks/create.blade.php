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
            <li class="text-gray-900 dark:text-white font-medium">New Content Block</li>
        </ol>
    </nav>

    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Add Content Block</h1>
        <a href="{{ route('admin.lessons.content-blocks.index', $lesson) }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to Content
        </a>
    </div>

    <div x-data="contentBlockForm()" class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form action="{{ route('admin.lessons.content-blocks.store', $lesson) }}" method="POST" class="p-6">
            @csrf

            <!-- Lesson Context -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Adding to:</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $lesson->title }}
                </p>
            </div>

            <!-- Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Content Type <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <button type="button"
                            @click="selectType('audio')"
                            :class="blockType === 'audio' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-700'"
                            class="flex flex-col items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-2xl mb-1">Audio</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">MP3, WAV</span>
                    </button>
                    <button type="button"
                            @click="selectType('video')"
                            :class="blockType === 'video' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-700'"
                            class="flex flex-col items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-2xl mb-1">Video</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">MP4, WebM</span>
                    </button>
                    <button type="button"
                            @click="selectType('principle_text')"
                            :class="blockType === 'principle_text' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-700'"
                            class="flex flex-col items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-2xl mb-1">Principle</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Key concept</span>
                    </button>
                    <button type="button"
                            @click="selectType('instruction_text')"
                            :class="blockType === 'instruction_text' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-700'"
                            class="flex flex-col items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-2xl mb-1">Instruction</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Directions</span>
                    </button>
                    <button type="button"
                            @click="selectType('image')"
                            :class="blockType === 'image' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-700'"
                            class="flex flex-col items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <span class="text-2xl mb-1">Image</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">JPG, PNG</span>
                    </button>
                </div>
                <input type="hidden" name="block_type" x-model="blockType">
                @error('block_type')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type-Specific Forms -->
            <div x-show="blockType" x-cloak class="space-y-6">

                <!-- Audio Form -->
                <template x-if="blockType === 'audio'">
                    <div class="space-y-4">
                        <div>
                            <label for="audio_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Audio URL <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="audio_url" id="audio_url"
                                   value="{{ old('audio_url') }}"
                                   placeholder="audio/filename.mp3"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Path to the audio file (e.g., audio/filename.mp3)</p>
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
                                       value="{{ old('title') }}"
                                       maxlength="255"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="duration_seconds" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Duration (seconds)
                                <input type="number" name="duration_seconds" id="duration_seconds"
                                       value="{{ old('duration_seconds') }}"
                                       min="1"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="transcript" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Transcript
                            </label>
                            <textarea name="transcript" id="transcript" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('transcript') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Full text transcript for accessibility</p>
                        </div>
                    </div>
                </template>

                <!-- Video Form -->
                <template x-if="blockType === 'video'">
                    <div class="space-y-4">
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Video URL <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="url" id="url"
                                   value="{{ old('url') }}"
                                   placeholder="https://storage.example.com/video/file.mp4"
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
                                       value="{{ old('duration_seconds') }}"
                                       min="1"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="thumbnail_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Thumbnail URL
                                </label>
                                <input type="text" name="thumbnail_url" id="thumbnail_url"
                                       value="{{ old('thumbnail_url') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <input type="text" name="description" id="description"
                                   value="{{ old('description') }}"
                                   maxlength="255"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </template>

                <!-- Text Forms (principle_text & instruction_text) -->
                <template x-if="blockType === 'principle_text' || blockType === 'instruction_text'">
                    <div class="space-y-4">
                        <div>
                            <label for="text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Content <span class="text-red-500">*</span>
                            </label>
                            <textarea name="text" id="text" rows="8"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">{{ old('text') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Supports Markdown formatting</p>
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
                                <option value="markdown" {{ old('format') === 'markdown' ? 'selected' : '' }}>Markdown</option>
                                <option value="plain" {{ old('format') === 'plain' ? 'selected' : '' }}>Plain Text</option>
                                <option value="html" {{ old('format') === 'html' ? 'selected' : '' }}>HTML</option>
                            </select>
                        </div>
                    </div>
                </template>

                <!-- Image Form -->
                <template x-if="blockType === 'image'">
                    <div class="space-y-4">
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Image URL <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="url" id="url"
                                   value="{{ old('url') }}"
                                   placeholder="https://storage.example.com/images/file.jpg"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('url')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="alt_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Alt Text <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="alt_text" id="alt_text"
                                   value="{{ old('alt_text') }}"
                                   maxlength="255"
                                   placeholder="Describe the image for screen readers"
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
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('caption') }}</textarea>
                        </div>
                    </div>
                </template>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sort Order
                    </label>
                    <input type="number" name="sort_order" id="sort_order"
                           value="{{ old('sort_order', $nextSortOrder) }}"
                           min="1"
                           class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Content Block
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function contentBlockForm() {
    return {
        blockType: '{{ old('block_type', '') }}',

        selectType(type) {
            this.blockType = type;
        }
    }
}
</script>
@endsection
