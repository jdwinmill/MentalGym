import { PublicHeader } from '@/components/public-header';
import { Head, Link } from '@inertiajs/react';

export default function Landing() {
    const currentYear = new Date().getFullYear();

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

                {/* Hero Section */}
                <main className="flex-1">
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
                                Daily practice sessions that systematically improve how you think,
                                listen, and operate under pressure.
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
                                Get Started
                            </Link>
                            <Link
                                href="/login"
                                className="px-8 py-4 border-2 border-border text-foreground rounded-xl text-lg font-semibold hover:border-primary transition-colors text-center"
                            >
                                Log In
                            </Link>
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
