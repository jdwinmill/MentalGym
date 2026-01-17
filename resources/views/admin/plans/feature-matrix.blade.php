@extends('admin.layout')

@section('content')
<div class="px-4 sm:px-0">
    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Feature Matrix</h1>
        <a href="{{ route('admin.plans.index') }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to Plans
        </a>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow overflow-x-auto sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-700">
                        Capability
                    </th>
                    @foreach($plans as $plan)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ $plan->name }}
                            <div class="text-xs font-normal normal-case text-gray-400">{{ $plan->getFormattedPrice() }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($capabilities as $category => $categoryCapabilities)
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <td colspan="{{ $plans->count() + 1 }}" class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ ucfirst($category ?? 'General') }}
                        </td>
                    </tr>
                    @foreach($categoryCapabilities as $capability)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 sticky left-0 bg-white dark:bg-gray-800">
                                <div class="font-medium">{{ $capability->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $capability->description }}</div>
                            </td>
                            @foreach($plans as $plan)
                                @php
                                    $planCapability = $plan->capabilities->firstWhere('id', $capability->id);
                                    $hasCapability = $planCapability !== null;
                                    $value = $hasCapability ? ($planCapability->pivot->value ?? $capability->default_value) : null;
                                @endphp
                                <td class="px-4 py-3 text-center">
                                    @if($hasCapability)
                                        @if($capability->value_type === 'boolean')
                                            @if($capability->castValue($value))
                                                <svg class="h-5 w-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        @else
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $capability->formatValue($value) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
