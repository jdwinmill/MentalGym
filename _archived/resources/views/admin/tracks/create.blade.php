@extends('admin.layout')

@section('content')
<div class="px-4 sm:px-0">
    <div class="sm:flex sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Track</h1>
        <a href="{{ route('admin.tracks.index') }}"
           class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            Back to List
        </a>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <form action="{{ route('admin.tracks.store') }}" method="POST" class="p-6">
            @csrf

            @include('admin.tracks._form', ['track' => null])

            <div class="mt-6 flex justify-end gap-3">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Track
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
