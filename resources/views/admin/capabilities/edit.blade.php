@extends('admin.layout')

@section('content')
<div class="px-4 sm:px-0">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Capability: {{ $capability->name }}</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Update the capability settings.</p>
        </div>
    </div>

    <form action="{{ route('admin.capabilities.update', $capability) }}" method="POST" class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
        @csrf
        @method('PUT')
        @include('admin.capabilities._form')

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.capabilities.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Capability
            </button>
        </div>
    </form>
</div>
@endsection
