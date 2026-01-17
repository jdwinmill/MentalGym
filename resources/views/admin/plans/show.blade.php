@extends('admin.layout')

@section('content')
<div class="px-4 sm:px-0">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white flex items-center">
                {{ $plan->name }}
                @if($plan->is_featured)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        Featured
                    </span>
                @endif
                @if(!$plan->is_active)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        Inactive
                    </span>
                @endif
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $plan->tagline }}</p>
        </div>
        <div class="mt-3 sm:mt-0">
            <a href="{{ route('admin.plans.edit', $plan) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Edit Plan
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Plan Details</h2>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Key</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $plan->key }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $plan->description ?: 'No description' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pricing</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $plan->getFormattedPrice() }}/{{ $plan->billing_interval }}
                        @if($plan->yearly_price)
                            <br><span class="text-gray-500">or {{ $plan->getFormattedYearlyPrice() }}/year (save ${{ number_format($plan->getYearlySavings(), 2) }})</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscribers</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $plan->users->count() }} user(s)</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Capabilities ({{ $plan->capabilities->count() }})</h2>
            @if($plan->capabilities->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No capabilities assigned.</p>
            @else
                <ul class="space-y-3">
                    @foreach($plan->capabilities->groupBy('category') as $category => $capabilities)
                        <li>
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ ucfirst($category ?? 'General') }}
                            </span>
                            <ul class="mt-1 space-y-1">
                                @foreach($capabilities as $capability)
                                    <li class="flex items-center text-sm text-gray-900 dark:text-gray-100">
                                        <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $capability->name }}
                                        @if($capability->value_type !== 'boolean')
                                            <span class="ml-2 text-gray-500 dark:text-gray-400">
                                                ({{ $capability->formatValue($capability->pivot->value ?? $capability->default_value) }})
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @if($plan->users->isNotEmpty())
        <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Subscribers</h2>
            </div>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($plan->users->take(10) as $user)
                    <li class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->subscription_status }}
                        </div>
                    </li>
                @endforeach
            </ul>
            @if($plan->users->count() > 10)
                <div class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                    And {{ $plan->users->count() - 10 }} more...
                </div>
            @endif
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('admin.plans.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">
            &larr; Back to Plans
        </a>
    </div>
</div>
@endsection
