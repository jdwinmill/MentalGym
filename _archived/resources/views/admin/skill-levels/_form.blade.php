<div class="space-y-6">
    <!-- Track Info (Display Only) -->
    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <p class="text-sm text-gray-500 dark:text-gray-400">Adding to track:</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $track->name }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $skillLevel->name ?? '') }}"
                   required
                   maxlength="100"
                   placeholder="e.g. Facts: Listen for Concrete Information"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">What skill does this level teach?</p>
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Slug <span class="text-red-500">*</span>
            </label>
            <input type="text" name="slug" id="slug"
                   value="{{ old('slug', $skillLevel->slug ?? '') }}"
                   required
                   maxlength="100"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">URL-friendly identifier. Auto-generates from name.</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="level_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Level Number <span class="text-red-500">*</span>
            </label>
            <input type="number" name="level_number" id="level_number"
                   value="{{ old('level_number', $skillLevel->level_number ?? $nextLevelNumber ?? 1) }}"
                   required
                   min="1"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @if(!empty($existingLevels))
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Existing levels: {{ implode(', ', $existingLevels) }}
                </p>
            @endif
            @error('level_number')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="pass_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Pass Threshold <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 flex items-center gap-2">
                <input type="number" name="pass_threshold" id="pass_threshold"
                       value="{{ old('pass_threshold', $skillLevel->pass_threshold ?? 0.80) }}"
                       required
                       min="0"
                       max="1"
                       step="0.01"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Enter as decimal (0.80 = 80%). Score needed to pass this level.
            </p>
            @error('pass_threshold')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Description <span class="text-red-500">*</span>
        </label>
        <textarea name="description" id="description" rows="4"
                  required
                  placeholder="What this skill level teaches in detail. What users will be able to do after completing it."
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $skillLevel->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    let slugManuallyEdited = slugInput.value !== '';

    slugInput.addEventListener('input', function() {
        slugManuallyEdited = true;
    });

    nameInput.addEventListener('blur', function() {
        if (!slugManuallyEdited || slugInput.value === '') {
            const slug = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            slugInput.value = slug;
        }
    });
});
</script>
