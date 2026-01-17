<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'SharpStack') }}</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-secondary dark:bg-background min-h-screen">
    <nav class="bg-card dark:bg-card shadow-sm border-b border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="text-xl font-bold text-foreground">
                            SharpStack
                        </a>
                        <span class="ml-2 px-2 py-1 text-xs font-semibold bg-primary text-primary-foreground rounded">Admin</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('admin.tracks.index') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                                  {{ request()->routeIs('admin.tracks.*') || request()->routeIs('admin.skill-levels.*') || request()->routeIs('admin.lessons.*') || request()->routeIs('admin.content-blocks.*') || request()->routeIs('admin.lesson-questions.*') || request()->routeIs('admin.answer-options.*')
                                     ? 'border-primary text-foreground'
                                     : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground' }}">
                            Tracks
                        </a>
                        <a href="{{ route('admin.plans.index') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                                  {{ request()->routeIs('admin.plans.*')
                                     ? 'border-primary text-foreground'
                                     : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground' }}">
                            Plans
                        </a>
                        <a href="{{ route('admin.capabilities.index') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                                  {{ request()->routeIs('admin.capabilities.*')
                                     ? 'border-primary text-foreground'
                                     : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground' }}">
                            Capabilities
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-muted-foreground text-sm mr-4">
                        {{ auth()->user()->name }}
                    </span>
                    <a href="{{ route('dashboard') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                        Back to App
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
