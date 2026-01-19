<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $track->name ?? '') }}"
                   required
                   maxlength="100"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Slug <span class="text-red-500">*</span>
            </label>
            <input type="text" name="slug" id="slug"
                   value="{{ old('slug', $track->slug ?? '') }}"
                   required
                   maxlength="100"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">URL-friendly identifier. Auto-generates from name if empty.</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="pitch" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Pitch (Tagline) <span class="text-red-500">*</span>
        </label>
        <input type="text" name="pitch" id="pitch"
               value="{{ old('pitch', $track->pitch ?? '') }}"
               required
               maxlength="255"
               placeholder="The compelling one-liner for this track"
               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('pitch')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Description <span class="text-red-500">*</span>
        </label>
        <textarea name="description" id="description" rows="4"
                  required
                  placeholder="Detailed explanation of what this track teaches"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $track->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="duration_weeks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Duration (weeks) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="duration_weeks" id="duration_weeks"
                   value="{{ old('duration_weeks', $track->duration_weeks ?? 4) }}"
                   required
                   min="1"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('duration_weeks')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sessions_per_week" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Sessions per Week <span class="text-red-500">*</span>
            </label>
            <input type="number" name="sessions_per_week" id="sessions_per_week"
                   value="{{ old('sessions_per_week', $track->sessions_per_week ?? 5) }}"
                   required
                   min="1"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('sessions_per_week')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="session_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Session Duration (min) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="session_duration_minutes" id="session_duration_minutes"
                   value="{{ old('session_duration_minutes', $track->session_duration_minutes ?? 10) }}"
                   required
                   min="1"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('session_duration_minutes')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Sort Order
            </label>
            <input type="number" name="sort_order" id="sort_order"
                   value="{{ old('sort_order', $track->sort_order ?? $nextSortOrder ?? 1) }}"
                   min="0"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('sort_order')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center pt-6">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $track->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                Active
            </label>
        </div>
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
