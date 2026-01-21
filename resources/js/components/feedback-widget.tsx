import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { MessageCircle, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

type FeedbackType = 'bug' | 'idea' | 'other';

export function FeedbackWidget() {
    const [isOpen, setIsOpen] = useState(false);
    const [type, setType] = useState<FeedbackType>('idea');
    const [title, setTitle] = useState('');
    const [body, setBody] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showSuccess, setShowSuccess] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const panelRef = useRef<HTMLDivElement>(null);

    const maxTitleLength = 100;
    const maxBodyLength = 1000;

    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (panelRef.current && !panelRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        }

        function handleEscape(event: KeyboardEvent) {
            if (event.key === 'Escape') {
                setIsOpen(false);
            }
        }

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
            document.addEventListener('keydown', handleEscape);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('keydown', handleEscape);
        };
    }, [isOpen]);

    function resetForm() {
        setType('idea');
        setTitle('');
        setBody('');
        setError(null);
    }

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        if (!body.trim()) {
            setError('Please tell us what\'s on your mind.');
            return;
        }

        setIsSubmitting(true);
        setError(null);

        try {
            const response = await fetch('/api/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    type,
                    title: title.trim() || null,
                    body: body.trim(),
                    url: window.location.pathname,
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to submit feedback');
            }

            setShowSuccess(true);
            resetForm();

            setTimeout(() => {
                setShowSuccess(false);
                setIsOpen(false);
            }, 1500);
        } catch {
            setError('Something went wrong. Please try again.');
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <div className="fixed bottom-6 right-6 z-40" ref={panelRef}>
            {/* Feedback Panel */}
            {isOpen && (
                <div
                    className={cn(
                        'absolute bottom-14 right-0 w-80 bg-background border border-border rounded-lg shadow-lg',
                        'animate-in fade-in-0 slide-in-from-bottom-2 duration-150'
                    )}
                >
                    {showSuccess ? (
                        <div className="p-6 text-center">
                            <p className="text-foreground font-medium">Thanks! We got it.</p>
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit}>
                            <div className="flex items-center justify-between p-4 border-b border-border">
                                <h3 className="font-medium text-foreground">Share Feedback</h3>
                                <button
                                    type="button"
                                    onClick={() => setIsOpen(false)}
                                    className="text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>

                            <div className="p-4 space-y-4">
                                {/* Type Selector */}
                                <div className="flex gap-2">
                                    {(['idea', 'bug', 'other'] as FeedbackType[]).map((t) => (
                                        <button
                                            key={t}
                                            type="button"
                                            onClick={() => setType(t)}
                                            className={cn(
                                                'px-3 py-1.5 text-sm rounded-full border transition-colors',
                                                type === t
                                                    ? 'bg-primary text-primary-foreground border-primary'
                                                    : 'bg-background text-muted-foreground border-border hover:border-foreground/30'
                                            )}
                                        >
                                            {t === 'idea' ? 'Idea' : t === 'bug' ? 'Bug' : 'Other'}
                                        </button>
                                    ))}
                                </div>

                                {/* Title (optional) */}
                                <div>
                                    <input
                                        type="text"
                                        placeholder="Brief summary (optional)"
                                        value={title}
                                        onChange={(e) => setTitle(e.target.value.slice(0, maxTitleLength))}
                                        className="w-full px-3 py-2 text-sm bg-background border border-input rounded-md placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                                    />
                                </div>

                                {/* Body (required) */}
                                <div>
                                    <textarea
                                        placeholder="What's on your mind?"
                                        value={body}
                                        onChange={(e) => setBody(e.target.value.slice(0, maxBodyLength))}
                                        rows={4}
                                        className="w-full px-3 py-2 text-sm bg-background border border-input rounded-md placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"
                                    />
                                    {body.length > maxBodyLength - 100 && (
                                        <p className="text-xs text-muted-foreground mt-1 text-right">
                                            {maxBodyLength - body.length} characters remaining
                                        </p>
                                    )}
                                </div>

                                {/* Error */}
                                {error && (
                                    <p className="text-sm text-destructive">{error}</p>
                                )}
                            </div>

                            <div className="p-4 border-t border-border">
                                <Button
                                    type="submit"
                                    size="sm"
                                    className="w-full"
                                    disabled={isSubmitting || !body.trim()}
                                >
                                    {isSubmitting ? 'Sending...' : 'Submit'}
                                </Button>
                            </div>
                        </form>
                    )}
                </div>
            )}

            {/* Feedback Bubble Button */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className={cn(
                    'w-11 h-11 rounded-full flex items-center justify-center',
                    'bg-secondary text-secondary-foreground shadow-md',
                    'hover:scale-105 transition-transform',
                    'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
                    isOpen && 'bg-primary text-primary-foreground'
                )}
                aria-label="Share Feedback"
            >
                <MessageCircle className="h-5 w-5" />
            </button>
        </div>
    );
}
