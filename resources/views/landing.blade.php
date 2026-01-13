<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>Thought Reps - Daily Mental Training That Actually Works</title>
    <meta name="description" content="Short, daily practice sessions that train critical thinking, active listening, and mental clarity. Not tips. Real reps that show you where your thinking breaks down.">
    <meta name="keywords" content="mental training, critical thinking, active listening, first principles, cognitive training, brain training, mental fitness">
    <meta name="author" content="Thought Reps">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Thought Reps - Daily Mental Training That Actually Works">
    <meta property="og:description" content="Short, daily practice sessions that train critical thinking, active listening, and mental clarity. Real reps that show you where your thinking breaks down.">
    <meta property="og:site_name" content="Thought Reps">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="Thought Reps - Daily Mental Training That Actually Works">
    <meta name="twitter:description" content="Short, daily practice sessions that train critical thinking, active listening, and mental clarity. Real reps that show you where your thinking breaks down.">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">

    <script>
        (function() {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        html {
            background-color: oklch(1 0 0);
        }
        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-background text-foreground">
    <div x-data="landingPage()" class="min-h-screen">
        <!-- Header / Logo -->
        <header class="px-6 pt-6 md:pt-8 max-w-3xl mx-auto">
            <a href="/" class="inline-block text-xl font-bold tracking-tight text-foreground hover:opacity-80 transition-opacity">
                Thought<span class="text-primary">Reps</span>
            </a>
        </header>

        <!-- Hero Section -->
        <section class="px-6 pt-10 md:pt-14 max-w-3xl mx-auto">
            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4">
                You know you're not as sharp as you could be.
            </h1>

            <div class="space-y-4 text-lg md:text-xl text-muted-foreground leading-relaxed mb-10">
                <p>
                    Most people are <strong class="text-foreground font-semibold">bad at listening</strong>. <strong class="text-foreground font-semibold">Weak at breaking down problems</strong>. <strong class="text-foreground font-semibold">Terrible under pressure</strong>.
                </p>
                <p>
                    You've read the articles. Watched the videos. Still stuck with the same mental gaps when it matters.
                </p>
            </div>

            <!-- Value Prop - Stands Out -->
            <div class="border-l-4 border-primary pl-6 py-2 mb-6">
                <p class="text-xl md:text-2xl font-bold text-foreground mb-2">
                    Thought Reps is different.
                </p>
                <p class="text-lg md:text-xl text-foreground">
                    We're building actual training. Short, daily practice sessions that force you to get better at the mental skills that matter.
                </p>
            </div>

            <p class="text-lg md:text-xl text-muted-foreground leading-relaxed">
                Not tips. Not inspiration. Real reps that show you exactly where your thinking breaks down and how to fix it.
            </p>
        </section>

        <!-- Topic Selection Section -->
        <section class="px-6 py-12 md:py-16 max-w-3xl mx-auto">
            <p class="text-lg text-muted-foreground mb-2">
                5-15 minute daily sessions targeting:
            </p>
            <p class="text-sm text-muted-foreground mb-6">
                <span class="font-medium text-foreground">Tap any that interest you.</span> We'll prioritize what to build based on demand.
            </p>

            <!-- Topic Selection Bubbles -->
            <div class="flex flex-wrap gap-3 md:gap-4">
                <template x-for="topic in topics" :key="topic.id">
                    <button
                        type="button"
                        @click="toggleTopic(topic.id)"
                        :class="[
                            'px-4 py-3 md:px-5 md:py-4 rounded-xl text-left transition-all duration-200 border-2',
                            selectedTopics.includes(topic.id)
                                ? 'bg-primary text-primary-foreground border-primary shadow-lg scale-[1.02]'
                                : 'bg-card border-border hover:border-primary/50 hover:shadow-md'
                        ]"
                    >
                        <span class="text-xl md:text-2xl mr-2" x-text="topic.emoji"></span>
                        <span class="font-semibold" x-text="topic.name"></span>
                        <p
                            class="text-sm mt-1 opacity-80"
                            :class="selectedTopics.includes(topic.id) ? 'text-primary-foreground/80' : 'text-muted-foreground'"
                            x-text="topic.description"
                        ></p>
                    </button>
                </template>
            </div>
        </section>

        <!-- First Email Signup -->
        <section class="px-6 pb-16 md:pb-20 max-w-xl mx-auto">
            <!-- Success State -->
            <div x-show="submitted" x-cloak class="bg-green-500/10 border border-green-500/30 rounded-xl p-6 text-center">
                <div class="text-4xl mb-4">&#10003;</div>
                <h3 class="text-xl font-bold mb-2 text-green-600 dark:text-green-400" x-text="successMessage"></h3>
                <template x-if="successTopics.length > 0">
                    <div>
                        <p class="text-muted-foreground mb-4">You selected:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <template x-for="topic in successTopics" :key="topic">
                                <span class="px-3 py-1 bg-green-500/20 text-green-700 dark:text-green-300 rounded-full text-sm font-medium" x-text="topic"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Form State -->
            <form x-show="!submitted" @submit.prevent="submitForm" class="space-y-4">
                <!-- Selected Topics Display -->
                <div x-show="selectedTopics.length > 0" class="space-y-2">
                    <label class="text-sm font-medium text-muted-foreground">Selected training:</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="topicId in selectedTopics" :key="topicId">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm font-medium">
                                <span x-text="getTopicEmoji(topicId)"></span>
                                <span x-text="getTopicName(topicId)"></span>
                                <button
                                    type="button"
                                    @click="toggleTopic(topicId)"
                                    class="ml-1 hover:bg-primary/20 rounded-full p-0.5 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Email Input -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        type="email"
                        x-model="email"
                        placeholder="your@email.com"
                        required
                        class="flex-1 px-5 py-4 text-lg rounded-xl border-2 border-border bg-card focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                        :class="emailError ? 'border-red-500' : ''"
                    >
                    <button
                        type="submit"
                        :disabled="!isValid || loading"
                        class="px-6 py-4 text-lg font-bold rounded-xl transition-all duration-200 whitespace-nowrap"
                        :class="[
                            isValid && !loading
                                ? 'bg-primary text-primary-foreground hover:opacity-90 shadow-lg hover:shadow-xl cursor-pointer'
                                : 'bg-muted text-muted-foreground cursor-not-allowed'
                        ]"
                    >
                        <span x-show="!loading">Get early access</span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                <p x-show="emailError" x-text="emailError" class="text-red-500 text-sm"></p>
                <p x-show="error" x-text="error" class="text-red-500 text-sm"></p>

                <!-- Small Print -->
                <p class="text-sm text-muted-foreground">
                    No spam. One email when we launch.
                </p>
            </form>
        </section>

        <!-- How It Works Section -->
        <section class="px-6 py-16 md:py-20 bg-secondary/50">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-2xl md:text-3xl font-bold mb-8">How it works:</h2>

                <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-8 mb-10 text-xl md:text-2xl font-semibold">
                    <span class="text-primary">Practice</span>
                    <span class="text-muted-foreground hidden md:inline">&rarr;</span>
                    <span class="text-muted-foreground md:hidden">&darr;</span>
                    <span class="text-primary">Feedback</span>
                    <span class="text-muted-foreground hidden md:inline">&rarr;</span>
                    <span class="text-muted-foreground md:hidden">&darr;</span>
                    <span class="text-primary">Improve</span>
                </div>

                <div class="space-y-4 text-lg text-muted-foreground leading-relaxed">
                    <p>
                        You'll do the exercise. We'll show you exactly where you failed. You'll practice the weak spots until they're not weak anymore.
                    </p>
                    <p>
                        Simple formats. Real improvement in weeks, not years.
                    </p>
                </div>
            </div>
        </section>

        <!-- Bottom CTA (for scrollers) -->
        <section x-show="!submitted" class="px-6 py-16 md:py-20">
            <div class="max-w-xl mx-auto text-center">
                <p class="text-xl md:text-2xl font-bold mb-6">Ready to train?</p>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            type="email"
                            x-model="email"
                            placeholder="your@email.com"
                            required
                            class="flex-1 px-5 py-4 text-lg rounded-xl border-2 border-border bg-card focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                            :class="emailError ? 'border-red-500' : ''"
                        >
                        <button
                            type="submit"
                            :disabled="!isValid || loading"
                            class="px-6 py-4 text-lg font-bold rounded-xl transition-all duration-200 whitespace-nowrap"
                            :class="[
                                isValid && !loading
                                    ? 'bg-primary text-primary-foreground hover:opacity-90 shadow-lg hover:shadow-xl cursor-pointer'
                                    : 'bg-muted text-muted-foreground cursor-not-allowed'
                            ]"
                        >
                            <span x-show="!loading">Get early access</span>
                            <span x-show="loading" class="inline-flex items-center gap-2">
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <p x-show="emailError" x-text="emailError" class="text-red-500 text-sm"></p>
                    <p x-show="error" x-text="error" class="text-red-500 text-sm"></p>
                </form>
                @if($signupCount >= 10)
                <p class="mt-6 text-muted-foreground">
                    Join <span class="font-semibold text-foreground">{{ $signupCount }}+</span> people waiting
                </p>
                @endif
            </div>
        </section>

        <!-- Success State (for scrollers who already signed up) -->
        <section x-show="submitted" x-cloak class="px-6 py-16 md:py-20">
            <div class="max-w-xl mx-auto text-center">
                <div class="text-5xl mb-4">&#10003;</div>
                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="successMessage"></h3>
            </div>
        </section>

        <!-- Footer -->
        <footer class="px-6 py-8 text-center text-sm text-muted-foreground">
            <p>&copy; {{ date('Y') }} Thought Reps. All rights reserved.</p>
        </footer>
    </div>

    <script>
        function landingPage() {
            return {
                topics: [
                    { id: 'critical-thinking', emoji: '🎯', name: 'Critical Thinking', description: 'Break down complex problems without getting lost' },
                    { id: 'active-listening', emoji: '👂', name: 'Active Listening', description: 'Actually hear what people say (not what you assume)' },
                    { id: 'first-principles', emoji: '🧠', name: 'First Principles Thinking', description: 'Cut through BS to what\'s actually true' },
                    { id: 'strategic-thinking', emoji: '💼', name: 'Strategic Thinking', description: 'See patterns and make better decisions' },
                    { id: 'clear-communication', emoji: '🗣️', name: 'Clear Communication', description: 'Say what you mean without confusion' },
                    { id: 'thinking-under-pressure', emoji: '⚡', name: 'Thinking Under Pressure', description: 'Stay sharp when stakes are high' },
                ],
                selectedTopics: [],
                email: '',
                emailError: '',
                error: '',
                loading: false,
                submitted: false,
                successMessage: '',
                successTopics: [],

                get isValid() {
                    return this.email && this.isValidEmail(this.email);
                },

                isValidEmail(email) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                },

                toggleTopic(topicId) {
                    const index = this.selectedTopics.indexOf(topicId);
                    if (index === -1) {
                        this.selectedTopics.push(topicId);
                    } else {
                        this.selectedTopics.splice(index, 1);
                    }
                },

                getTopicName(topicId) {
                    const topic = this.topics.find(t => t.id === topicId);
                    return topic ? topic.name : '';
                },

                getTopicEmoji(topicId) {
                    const topic = this.topics.find(t => t.id === topicId);
                    return topic ? topic.emoji : '';
                },

                getMetadata() {
                    const params = new URLSearchParams(window.location.search);
                    return {
                        referrer: document.referrer || null,
                        utm_source: params.get('utm_source'),
                        utm_medium: params.get('utm_medium'),
                        utm_campaign: params.get('utm_campaign'),
                        utm_term: params.get('utm_term'),
                        utm_content: params.get('utm_content'),
                        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        device_type: window.innerWidth < 768 ? 'mobile' : 'desktop',
                        locale: navigator.language || navigator.userLanguage,
                    };
                },

                async submitForm() {
                    this.emailError = '';
                    this.error = '';

                    if (!this.isValidEmail(this.email)) {
                        this.emailError = 'Please enter a valid email address.';
                        return;
                    }

                    this.loading = true;

                    try {
                        const metadata = this.getMetadata();
                        const response = await fetch('{{ route("early-access.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                email: this.email.toLowerCase(),
                                selected_topics: this.selectedTopics,
                                ...metadata,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.submitted = true;
                            this.successMessage = data.message;
                            this.successTopics = data.selected_topics;
                        } else if (response.status === 422) {
                            const errors = data.errors || {};
                            if (errors.email) {
                                this.emailError = errors.email[0];
                            } else if (errors.selected_topics) {
                                this.error = errors.selected_topics[0];
                            } else {
                                this.error = data.message || 'Something went wrong. Please try again.';
                            }
                        } else {
                            this.error = data.message || 'Something went wrong. Please try again.';
                        }
                    } catch (e) {
                        this.error = 'Network error. Please check your connection and try again.';
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
