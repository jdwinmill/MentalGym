<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key</label>
            <input type="text" name="key" id="key" value="{{ old('key', $capability->key ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   required pattern="[a-z0-9_-]+" placeholder="e.g., ai_analytics">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lowercase letters, numbers, underscores, hyphens only</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $capability->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   required placeholder="e.g., AI Analytics">
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea name="description" id="description" rows="2"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Describe what this capability enables...">{{ old('description', $capability->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
            <select name="category" id="category"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">-- Select Category --</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ old('category', $capability->category ?? '') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="value_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value Type</label>
            <select name="value_type" id="value_type"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @foreach($valueTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('value_type', $capability->value_type ?? 'boolean') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="default_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Value</label>
            <input type="text" name="default_value" id="default_value" value="{{ old('default_value', $capability->default_value ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="e.g., true, 100, or JSON">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $capability->sort_order ?? $nextSortOrder ?? 1) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   min="0">
        </div>

        <div class="flex items-center pt-6">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                   {{ old('is_active', $capability->is_active ?? true) ? 'checked' : '' }}>
            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Active</label>
        </div>
    </div>
</div>
