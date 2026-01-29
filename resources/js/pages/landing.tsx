import { PublicHeader } from '@/components/public-header';
import { Head, Link } from '@inertiajs/react';
import { MessageCircle, Brain, Target, Users, Zap, Eye } from 'lucide-react';

export default function Landing() {
    const currentYear = new Date().getFullYear();

    const practiceModes = [
        {
            name: 'Difficult Conversations',
            tagline: 'Navigate high-stakes discussions with confidence',
            icon: MessageCircle,
        },
        {
            name: 'Systems Thinking',
            tagline: 'See the whole system, not just the parts',
            icon: Brain,
        },
        {
            name: 'Think Straight',
            tagline: 'Stop outsmarting yourself',
            icon: Target,
        },
        {
            name: 'Read the Room',
            tagline: 'Adapt your message to your audience',
            icon: Users,
        },
        {
            name: 'Game Theory',
            tagline: 'See the game before you play it',
            icon: Zap,
        },
        {
            name: 'What Would You Say?',
            tagline: 'Practice the conversations you usually fumble',
            icon: Eye,
        },
    ];

    return (
        <>
            <Head>
                <title>SharpStack | Daily Mental Training</title>
                <meta
                    name="description"
                    content="Daily practice sessions that actually make you better at the skills that count: how you think, speak, read a room. Not tips. Real practice that shows you where you stumble."
                />
                <meta
                    name="keywords"
                    content="speaking with confidence, handling conflict, thinking under pressure, active listening, problem solving, reading the room, communication skills, mental training"
                />
                <meta name="author" content="SharpStack" />
                <meta name="robots" content="index, follow" />
                <meta property="og:type" content="website" />
                <meta property="og:title" content="SharpStack | Daily Mental Training" />
                <meta
                    property="og:description"
                    content="Daily practice sessions that actually make you better at the skills that count: how you think, speak, read a room. Not tips. Real practice."
                />
                <meta property="og:site_name" content="SharpStack" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content="SharpStack | Daily Mental Training" />
                <meta
                    name="twitter:description"
                    content="Daily practice sessions that actually make you better at the skills that count: how you think, speak, read a room. Not tips. Real practice."
                />
            </Head>

            <div className="flex min-h-svh flex-col bg-background">
                <PublicHeader />

                <main className="flex-1">
                    {/* Hero Section */}
                    <section className="px-6 pt-16 md:pt-24 pb-16 max-w-4xl mx-auto">
                        <h1 className="text-3xl md:text-5xl font-bold leading-tight mb-6">
                            You're leaving wins on the table.
                        </h1>

                        <div className="space-y-4 text-lg md:text-xl text-muted-foreground leading-relaxed mb-12">
                            <p>In the interview. The negotiation. The conversation that mattered.</p>
                            <p>
                                You knew you could have listened better. Thought more clearly. Read
                                the room faster.
                            </p>
                            <p>
                                You've collected the tips. You know what good thinking looks like.
                                But when pressure hits, the same gaps appear.
                            </p>
                        </div>

                        {/* Value Prop */}
                        <div className="border-l-4 border-primary pl-6 py-2 mb-8">
                            <p className="text-xl md:text-2xl font-bold text-foreground mb-2">
                                SharpStack builds the skills tips can't teach.
                            </p>
                            <p className="text-lg md:text-xl text-foreground mb-3">
                                Daily practice sessions that systematically sharpen how you think,
                                communicate, and handle the conversations you usually fumble.
                            </p>
                            <p className="text-base md:text-lg text-muted-foreground">
                                Not tips. Not frameworks. Actual training: you practice, get
                                diagnostic feedback on exactly where you're weak, then drill those
                                gaps until they close.
                            </p>
                        </div>

                        <p className="text-lg md:text-xl text-foreground font-medium leading-relaxed mb-10">
                            5-15 minutes daily. Measurable improvement in weeks.
                        </p>

                        {/* CTA Buttons */}
                        <div className="flex flex-col sm:flex-row gap-4">
                            <Link
                                href="/register"
                                className="px-8 py-4 bg-primary text-primary-foreground rounded-xl text-lg font-bold hover:opacity-90 transition-opacity text-center shadow-lg"
                            >
                                Start Training Free
                            </Link>
                            <Link
                                href="/login"
                                className="px-8 py-4 border-2 border-border text-foreground rounded-xl text-lg font-semibold hover:border-primary transition-colors text-center"
                            >
                                Log In
                            </Link>
                        </div>
                        <p className="text-sm text-muted-foreground mt-4">
                            5 free sessions daily. No credit card required.
                        </p>
                    </section>

                    {/* How It Works */}
                    <section className="px-6 py-16 md:py-24 bg-secondary/30 border-y border-border">
                        <div className="max-w-4xl mx-auto">
                            <h2 className="text-2xl md:text-3xl font-bold mb-4">
                                How it works
                            </h2>
                            <p className="text-lg text-muted-foreground mb-12">
                                Each session follows the same loop. Simple, but effective.
                            </p>

                            <div className="grid md:grid-cols-3 gap-8 md:gap-12">
                                <div>
                                    <div className="text-4xl font-bold text-primary/20 mb-2">1</div>
                                    <h3 className="text-lg font-semibold mb-2">You get a scenario</h3>
                                    <p className="text-muted-foreground">
                                        Tailored to your role and experience level. A difficult
                                        conversation, a systems problem, a moment that requires
                                        clear thinking.
                                    </p>
                                </div>
                                <div>
                                    <div className="text-4xl font-bold text-primary/20 mb-2">2</div>
                                    <h3 className="text-lg font-semibold mb-2">You respond</h3>
                                    <p className="text-muted-foreground">
                                        As you would in real life. No multiple choice. No hints.
                                        Just you, figuring out what to say or how to think through it.
                                    </p>
                                </div>
                                <div>
                                    <div className="text-4xl font-bold text-primary/20 mb-2">3</div>
                                    <h3 className="text-lg font-semibold mb-2">You get feedback</h3>
                                    <p className="text-muted-foreground">
                                        Specific. Quotes your words back. Shows what landed, what
                                        didn't, and the patterns you might not see yourself.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* Practice Modes */}
                    <section className="px-6 py-16 md:py-24">
                        <div className="max-w-4xl mx-auto">
                            <h2 className="text-2xl md:text-3xl font-bold mb-4">
                                What you'll practice
                            </h2>
                            <p className="text-lg text-muted-foreground mb-12">
                                Focused modes targeting the skills that matter when stakes are high.
                                New modes added regularly.
                            </p>

                            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {practiceModes.map((mode) => (
                                    <div
                                        key={mode.name}
                                        className="p-5 rounded-xl border border-border bg-card hover:border-primary/50 transition-colors"
                                    >
                                        <mode.icon className="h-6 w-6 text-muted-foreground mb-3" />
                                        <h3 className="font-semibold mb-1">{mode.name}</h3>
                                        <p className="text-sm text-muted-foreground">
                                            {mode.tagline}
                                        </p>
                                    </div>
                                ))}
                                <div className="p-5 rounded-xl border border-dashed border-border bg-transparent flex flex-col items-center justify-center text-center">
                                    <span className="text-2xl mb-2">+</span>
                                    <h3 className="font-semibold mb-1 text-muted-foreground">More coming</h3>
                                    <p className="text-sm text-muted-foreground">
                                        New modes added regularly
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* Blind Spots */}
                    <section className="px-6 py-16 md:py-24 bg-secondary/30 border-y border-border">
                        <div className="max-w-4xl mx-auto">
                            <div className="max-w-2xl">
                                <h2 className="text-2xl md:text-3xl font-bold mb-4">
                                    Your blind spots, surfaced
                                </h2>
                                <p className="text-lg text-muted-foreground mb-6">
                                    Everyone has patterns they don't see. Maybe you over-explain
                                    when nervous. Maybe you avoid the hard part of the conversation.
                                    Maybe you solve for the symptom instead of the system.
                                </p>
                                <p className="text-lg text-foreground mb-6">
                                    SharpStack tracks where you consistently stumble, across sessions
                                    and modes, then surfaces the patterns that are actually holding
                                    you back.
                                </p>
                                <p className="text-muted-foreground">
                                    Not generic advice. Your specific gaps, based on how you actually
                                    perform under pressure.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* Pricing Teaser */}
                    <section className="px-6 py-16 md:py-24">
                        <div className="max-w-4xl mx-auto text-center">
                            <h2 className="text-2xl md:text-3xl font-bold mb-4">
                                Start free. Upgrade when you're ready.
                            </h2>
                            <p className="text-lg text-muted-foreground mb-8 max-w-2xl mx-auto">
                                Free accounts get 5 exchanges per day. Enough to see if this works
                                for you. Pro unlocks 20 daily responses, Pro-only practice modes,
                                full blind spot analysis, and weekly progress reports.
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                <Link
                                    href="/register"
                                    className="px-8 py-4 bg-primary text-primary-foreground rounded-xl text-lg font-bold hover:opacity-90 transition-opacity text-center shadow-lg"
                                >
                                    Start Training Free
                                </Link>
                                <Link
                                    href="/pricing"
                                    className="px-8 py-4 border-2 border-border text-foreground rounded-xl text-lg font-semibold hover:border-primary transition-colors text-center"
                                >
                                    See Pricing
                                </Link>
                            </div>
                        </div>
                    </section>
                </main>

                {/* Footer */}
                <footer className="px-6 py-12 bg-secondary/50 border-t border-border">
                    <div className="max-w-4xl mx-auto">
                        {/* Links */}
                        <div className="flex flex-wrap justify-center gap-6 mb-6">
                            <Link
                                href="/playbook"
                                className="text-sm font-medium text-foreground hover:text-primary transition-colors"
                            >
                                Playbook
                            </Link>
                            <Link
                                href="/pricing"
                                className="text-sm font-medium text-foreground hover:text-primary transition-colors"
                            >
                                Pricing
                            </Link>
                            <a
                                href="mailto:hello@sharpstack.io"
                                className="text-sm font-medium text-foreground hover:text-primary transition-colors"
                            >
                                Contact
                            </a>
                        </div>

                        {/* Copyright */}
                        <p className="text-center text-sm text-muted-foreground">
                            &copy; {currentYear} SharpStack - a product of Outpost AI Labs LLC. All
                            rights reserved.
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}
