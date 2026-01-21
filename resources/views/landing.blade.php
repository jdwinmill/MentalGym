<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>SharpStack | Daily Mental Training</title>
    <meta name="description" content="Daily practice sessions that actually make you better at the skills that count: how you think, speak, read a room. Not tips. Real practice that shows you where you stumble.">
    <meta name="keywords" content="speaking with confidence, handling conflict, thinking under pressure, active listening, problem solving, reading the room, communication skills, mental training">
    <meta name="author" content="SharpStack">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="SharpStack | Daily Mental Training">
    <meta property="og:description" content="Daily practice sessions that actually make you better at the skills that count: how you think, speak, read a room. Not tips. Real practice.">
    <meta property="og:site_name" content="SharpStack">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="SharpStack | Daily Mental Training">
    <meta name="twitter:description" content="Daily practice sessions that actually make you better at the skills that count: how you think, speak, read a room. Not tips. Real practice.">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">

    <script>
        (function() {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <link rel="icon" href="/favicon.svg?v={{ time() }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v={{ time() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css'])

    <style>
        /* SharpStack Landing Page Colors: Black + Orange + Cream */
        :root {
            --background: #faf9f6;
            --foreground: #000000;
            --card: #faf9f6;
            --card-foreground: #000000;
            --primary: #ff6b35;
            --primary-foreground: #ffffff;
            --secondary: #f0ede8;
            --secondary-foreground: #000000;
            --muted: #f0ede8;
            --muted-foreground: #555555;
            --border: #e5e2dc;
            --input: #e5e2dc;
            --ring: #ff6b35;
        }
        .dark {
            --background: #0a0a0a;
            --foreground: #faf9f6;
            --card: #0a0a0a;
            --card-foreground: #faf9f6;
            --primary: #ff6b35;
            --primary-foreground: #000000;
            --secondary: #1a1a1a;
            --secondary-foreground: #faf9f6;
            --muted: #1a1a1a;
            --muted-foreground: #999999;
            --border: #2a2a2a;
            --input: #2a2a2a;
            --ring: #ff6b35;
        }
        html {
            background-color: #faf9f6;
        }
        html.dark {
            background-color: #0a0a0a;
        }
    </style>

</head>
<body class="font-sans antialiased bg-background text-foreground">
    <div class="min-h-screen">
        <!-- Header / Logo -->
        <header class="px-6 pt-6 md:pt-8 max-w-4xl mx-auto flex items-center justify-between">
            <a href="/" class="inline-block text-2xl md:text-3xl tracking-tight hover:opacity-80 transition-opacity">
                <span class="font-bold text-foreground">SharpStack</span>
                <span class="text-muted-foreground font-normal mx-2">|</span>
                <span class="text-muted-foreground font-medium text-lg md:text-xl">Daily Mental Training</span>
            </a>
            <nav class="flex items-center gap-4">
                <a href="{{ route('pricing') }}" class="text-foreground hover:text-primary transition-colors font-medium">
                    Pricing
                </a>
                <a href="{{ route('login') }}" class="text-foreground hover:text-primary transition-colors font-medium">
                    Log In
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg font-semibold hover:opacity-90 transition-opacity">
                    Sign Up
                </a>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="px-6 pt-16 md:pt-24 pb-16 max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-6">
                You're leaving wins on the table.
            </h1>

            <div class="space-y-4 text-lg md:text-xl text-muted-foreground leading-relaxed mb-12">
                <p>
                    In the interview. The negotiation. The conversation that mattered.
                </p>
                <p>
                    You knew you could have listened better. Thought more clearly. Read the room faster.
                </p>
                <p>
                    You've collected the tips. You know what good thinking looks like. But when pressure hits, the same gaps appear.
                </p>
            </div>

            <!-- Value Prop - Stands Out -->
            <div class="border-l-4 border-primary pl-6 py-2 mb-8">
                <p class="text-xl md:text-2xl font-bold text-foreground mb-2">
                    SharpStack builds the skills tips can't teach.
                </p>
                <p class="text-lg md:text-xl text-foreground mb-3">
                    Daily practice sessions that systematically improve how you think, listen, and operate under pressure.
                </p>
                <p class="text-base md:text-lg text-muted-foreground">
                    Not tips. Not frameworks. Actual training: you practice, get diagnostic feedback on exactly where you're weak, then drill those gaps until they close.
                </p>
            </div>

            <p class="text-lg md:text-xl text-foreground font-medium leading-relaxed mb-10">
                5-15 minutes daily. Measurable improvement in weeks.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('register') }}" class="px-8 py-4 bg-primary text-primary-foreground rounded-xl text-lg font-bold hover:opacity-90 transition-opacity text-center shadow-lg">
                    Get Started
                </a>
                <a href="{{ route('login') }}" class="px-8 py-4 border-2 border-border text-foreground rounded-xl text-lg font-semibold hover:border-primary transition-colors text-center">
                    Log In
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="px-6 py-12 text-center text-sm text-muted-foreground">
            <p class="mb-4">
                <span class="text-foreground font-medium">Questions? Ideas? Just want to say hi?</span>
                <br class="sm:hidden">
                <a href="mailto:hello@sharpstack.io" class="text-primary hover:underline font-medium ml-0 sm:ml-2">hello@sharpstack.io</a>
            </p>
            <p>&copy; {{ date('Y') }} SharpStack - a product of Outpost AI Labs LLC. All rights reserved.</p>
        </footer>
    </div>

</body>
</html>
