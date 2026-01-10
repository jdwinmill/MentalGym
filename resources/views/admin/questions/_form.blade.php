<div class="space-y-6">
    <div>
        <label for="text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Question <span class="text-red-500">*</span>
        </label>
        <textarea name="text" id="text" rows="4"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  required>{{ old('text', $question->text ?? '') }}</textarea>
    </div>

    <div>
        <label for="principle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Principle
        </label>
        <textarea name="principle" id="principle" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('principle', $question->principle ?? '') }}</textarea>
    </div>

    <div>
        <label for="intent_tag" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Category (Intent Tag)
        </label>
        <input type="text" name="intent_tag" id="intent_tag"
               value="{{ old('intent_tag', $question->intent_tag ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Tags
        </label>
        <div id="tag-input-container" class="mt-1 relative">
            <div class="flex flex-wrap gap-2 p-2 min-h-[42px] rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                <div id="selected-tags" class="flex flex-wrap gap-2"></div>
                <input type="text" id="tag-input"
                       placeholder="Type to add tags..."
                       autocomplete="off"
                       class="flex-1 min-w-[120px] border-0 bg-transparent p-0 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-0 focus:outline-none">
            </div>
            <div id="tag-suggestions" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden max-h-48 overflow-y-auto"></div>
        </div>
        <input type="hidden" name="tags" id="tags-hidden" value="{{ old('tags', isset($question) && $question->tags->count() ? $question->tags->pluck('name')->implode(',') : '') }}">
    </div>

    <div class="flex items-center">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" name="active" id="active" value="1"
               {{ old('active', $question->active ?? true) ? 'checked' : '' }}
               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <label for="active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
            Active
        </label>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const allTags = @json($allTags ?? []);
    const tagInput = document.getElementById('tag-input');
    const tagsHidden = document.getElementById('tags-hidden');
    const selectedTagsContainer = document.getElementById('selected-tags');
    const suggestionsContainer = document.getElementById('tag-suggestions');

    let selectedTags = tagsHidden.value ? tagsHidden.value.split(',').filter(t => t.trim()) : [];

    function renderSelectedTags() {
        selectedTagsContainer.innerHTML = '';
        selectedTags.forEach(tag => {
            const tagEl = document.createElement('span');
            tagEl.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-md text-sm font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200';
            tagEl.innerHTML = `
                ${escapeHtml(tag)}
                <button type="button" class="hover:text-indigo-600 dark:hover:text-indigo-400" data-tag="${escapeHtml(tag)}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;
            tagEl.querySelector('button').addEventListener('click', () => removeTag(tag));
            selectedTagsContainer.appendChild(tagEl);
        });
        tagsHidden.value = selectedTags.join(',');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function addTag(tag) {
        tag = tag.trim().toLowerCase();
        if (tag && !selectedTags.includes(tag)) {
            selectedTags.push(tag);
            renderSelectedTags();
        }
        tagInput.value = '';
        hideSuggestions();
    }

    function removeTag(tag) {
        selectedTags = selectedTags.filter(t => t !== tag);
        renderSelectedTags();
    }

    function showSuggestions(filter) {
        const filtered = allTags.filter(tag =>
            tag.toLowerCase().includes(filter.toLowerCase()) &&
            !selectedTags.includes(tag.toLowerCase())
        );

        if (filtered.length === 0 && filter.trim()) {
            suggestionsContainer.innerHTML = `
                <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                    Press Enter to create "<span class="font-medium text-gray-700 dark:text-gray-200">${escapeHtml(filter)}</span>"
                </div>
            `;
            suggestionsContainer.classList.remove('hidden');
        } else if (filtered.length > 0) {
            suggestionsContainer.innerHTML = filtered.map(tag => `
                <div class="tag-suggestion px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100" data-tag="${escapeHtml(tag)}">
                    ${escapeHtml(tag)}
                </div>
            `).join('');
            suggestionsContainer.classList.remove('hidden');

            suggestionsContainer.querySelectorAll('.tag-suggestion').forEach(el => {
                el.addEventListener('click', () => addTag(el.dataset.tag));
            });
        } else {
            hideSuggestions();
        }
    }

    function hideSuggestions() {
        suggestionsContainer.classList.add('hidden');
    }

    tagInput.addEventListener('input', (e) => {
        const value = e.target.value;
        if (value.length > 0) {
            showSuggestions(value);
        } else {
            hideSuggestions();
        }
    });

    tagInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (tagInput.value.trim()) {
                addTag(tagInput.value);
            }
        } else if (e.key === 'Backspace' && !tagInput.value && selectedTags.length > 0) {
            removeTag(selectedTags[selectedTags.length - 1]);
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    tagInput.addEventListener('blur', () => {
        setTimeout(hideSuggestions, 200);
    });

    tagInput.addEventListener('focus', () => {
        if (tagInput.value.length > 0) {
            showSuggestions(tagInput.value);
        }
    });

    // Initial render
    renderSelectedTags();
});
</script>
