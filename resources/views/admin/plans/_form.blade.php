<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key</label>
            <input type="text" name="key" id="key" value="{{ old('key', $plan->key ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   required pattern="[a-z0-9_-]+" placeholder="e.g., premium_monthly">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lowercase letters, numbers, underscores, hyphens only</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $plan->name ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   required placeholder="e.g., Premium">
        </div>
    </div>

    <div>
        <label for="tagline" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tagline</label>
        <input type="text" name="tagline" id="tagline" value="{{ old('tagline', $plan->tagline ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               placeholder="e.g., Unlock your full potential">
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea name="description" id="description" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Full description of the plan...">{{ old('description', $plan->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Price ($)</label>
            <input type="number" name="price" id="price" value="{{ old('price', $plan->price ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   required min="0" step="0.01">
        </div>

        <div>
            <label for="yearly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Yearly Price ($)</label>
            <input type="number" name="yearly_price" id="yearly_price" value="{{ old('yearly_price', $plan->yearly_price ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   min="0" step="0.01">
        </div>

        <div>
            <label for="billing_interval" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Interval</label>
            <select name="billing_interval" id="billing_interval"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="monthly" {{ old('billing_interval', $plan->billing_interval ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ old('billing_interval', $plan->billing_interval ?? '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                <option value="lifetime" {{ old('billing_interval', $plan->billing_interval ?? '') === 'lifetime' ? 'selected' : '' }}>Lifetime</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $plan->sort_order ?? $nextSortOrder ?? 1) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   min="0">
        </div>

        <div class="flex items-center pt-6">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                   {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Active</label>
        </div>

        <div class="flex items-center pt-6">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                   {{ old('is_featured', $plan->is_featured ?? false) ? 'checked' : '' }}>
            <label for="is_featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Featured</label>
        </div>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Capabilities</h3>

        @foreach($capabilities as $category => $categoryCapabilities)
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">
                    {{ ucfirst($category ?? 'General') }}
                </h4>
                <div class="space-y-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    @foreach($categoryCapabilities as $index => $capability)
                        @php
                            $hasCapability = isset($planCapabilities) && $planCapabilities->has($capability->id);
                            $currentValue = $hasCapability ? $planCapabilities->get($capability->id) : null;
                        @endphp
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="cap_{{ $capability->id }}"
                                       class="capability-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                       data-capability-id="{{ $capability->id }}"
                                       {{ $hasCapability || old("capabilities.{$capability->id}.id") ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 flex-1">
                                <label for="cap_{{ $capability->id }}" class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $capability->name }}
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $capability->description }}</p>

                                <input type="hidden"
                                       name="capabilities[{{ $capability->id }}][id]"
                                       id="cap_id_{{ $capability->id }}"
                                       value="{{ $hasCapability || old("capabilities.{$capability->id}.id") ? $capability->id : '' }}">

                                @if($capability->value_type !== 'boolean')
                                    <div class="mt-2">
                                        <label class="text-xs text-gray-500 dark:text-gray-400">
                                            Value ({{ $capability->value_type }}):
                                        </label>
                                        <input type="{{ $capability->value_type === 'integer' ? 'number' : 'text' }}"
                                               name="capabilities[{{ $capability->id }}][value]"
                                               id="cap_value_{{ $capability->id }}"
                                               value="{{ old("capabilities.{$capability->id}.value", $currentValue ?? $capability->default_value) }}"
                                               class="mt-1 block w-full sm:w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                               placeholder="{{ $capability->default_value }}">
                                    </div>
                                @else
                                    <input type="hidden"
                                           name="capabilities[{{ $capability->id }}][value]"
                                           id="cap_value_{{ $capability->id }}"
                                           value="{{ old("capabilities.{$capability->id}.value", $currentValue ?? '1') }}">
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.capability-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const capId = this.dataset.capabilityId;
            const hiddenInput = document.getElementById('cap_id_' + capId);
            if (this.checked) {
                hiddenInput.value = capId;
            } else {
                hiddenInput.value = '';
            }
        });
    });
});
</script>
