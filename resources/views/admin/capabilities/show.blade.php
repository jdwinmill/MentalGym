@extends('admin.layout')

@section('content')
<div class="px-4 sm:px-0">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white flex items-center">
                {{ $capability->name }}
                @if(!$capability->is_active)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        Inactive
                    </span>
                @endif
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $capability->key }}</p>
        </div>
        <div class="mt-3 sm:mt-0">
            <a href="{{ route('admin.capabilities.edit', $capability) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Edit Capability
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Capability Details</h2>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $capability->description ?: 'No description' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $capability->getCategoryLabel() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Value Type</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($capability->value_type) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Default Value</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                        {{ $capability->formatValue($capability->default_value) }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Used by Plans ({{ $capability->plans->count() }})</h2>
            @if($capability->plans->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Not assigned to any plans yet.</p>
            @else
                <ul class="space-y-3">
                    @foreach($capability->plans as $plan)
                        <li class="flex items-center justify-between">
                            <div>
                                <a href="{{ route('admin.plans.show', $plan) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    {{ $plan->name }}
                                </a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $plan->getFormattedPrice() }}/{{ $plan->billing_interval }}</p>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                @if($capability->value_type !== 'boolean')
                                    Value: {{ $capability->formatValue($plan->pivot->value ?? $capability->default_value) }}
                                @else
                                    @if($capability->castValue($plan->pivot->value ?? $capability->default_value))
                                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.capabilities.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">
            &larr; Back to Capabilities
        </a>
    </div>
</div>
@endsection
