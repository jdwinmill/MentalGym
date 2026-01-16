<div class="space-y-6">
    <!-- Skill Context (Display Only) -->
    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <p class="text-sm text-gray-500 dark:text-gray-400">Adding to:</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $track->name }} &rarr; Level {{ $skillLevel->level_number }}: {{ $skillLevel->name }}
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Title <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" id="title"
                   value="{{ old('title', $lesson->title ?? '') }}"
                   required
                   maxlength="100"
                   placeholder="e.g. Active Listening: Grocery Store Conflict"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Give this lesson a descriptive name</p>
            @error('title')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="lesson_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Lesson Number <span class="text-red-500">*</span>
            </label>
            <input type="number" name="lesson_number" id="lesson_number"
                   value="{{ old('lesson_number', $lesson->lesson_number ?? $nextLessonNumber ?? 1) }}"
                   required
                   min="1"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('lesson_number')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="estimated_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Duration (minutes) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="estimated_duration_minutes" id="estimated_duration_minutes"
                   value="{{ old('estimated_duration_minutes', $lesson->estimated_duration_minutes ?? 5) }}"
                   required
                   min="1"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Estimated completion time</p>
            @error('estimated_duration_minutes')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center pt-6">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $lesson->is_active ?? false) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                Active (published)
            </label>
        </div>
    </div>

    <!-- Learning Objectives with Alpine.js -->
    <div x-data="objectivesManager()" class="space-y-3">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Learning Objectives <span class="text-red-500">*</span>
        </label>
        <p class="text-xs text-gray-500 dark:text-gray-400">What will users learn in this lesson? (1-5 objectives)</p>

        <template x-for="(objective, index) in objectives" :key="index">
            <div class="flex gap-2">
                <input
                    type="text"
                    x-model="objectives[index]"
                    :name="'learning_objectives[' + index + ']'"
                    placeholder="e.g. Identify speaker's main factual claim"
                    maxlength="255"
                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                <button
                    type="button"
                    @click="removeObjective(index)"
                    x-show="objectives.length > 1"
                    class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Remove
                </button>
            </div>
        </template>

        <button
            type="button"
            @click="addObjective()"
            x-show="objectives.length < 5"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 border border-indigo-300 dark:border-indigo-600 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900/20"
        >
            + Add Objective
        </button>

        @error('learning_objectives')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @error('learning_objectives.*')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
function objectivesManager() {
    return {
        objectives: @json(old('learning_objectives', $lesson->learning_objectives ?? [''])),

        addObjective() {
            if (this.objectives.length < 5) {
                this.objectives.push('');
            }
        },

        removeObjective(index) {
            if (this.objectives.length > 1) {
                this.objectives.splice(index, 1);
            }
        }
    }
}
</script>
