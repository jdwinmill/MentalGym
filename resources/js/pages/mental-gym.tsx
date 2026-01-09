import { useState, useEffect } from 'react';

interface Question {
    id: number;
    text: string;
    principle: string | null;
    intent_tag: string;
}

type AppState = 'loading' | 'principle' | 'question' | 'rating' | 'complete' | 'done';

export default function MentalGym() {
    const [state, setState] = useState<AppState>('loading');
    const [question, setQuestion] = useState<Question | null>(null);
    const [sessionId, setSessionId] = useState<string>('');
    const [responseText, setResponseText] = useState('');
    const [rating, setRating] = useState<number | null>(null);
    const [feedbackText, setFeedbackText] = useState('');
    const [hoveredStar, setHoveredStar] = useState<number | null>(null);

    useEffect(() => {
        loadQuestion();
    }, []);

    const loadQuestion = async () => {
        setState('loading');
        setResponseText('');
        setRating(null);
        setFeedbackText('');

        try {
            const storedSessionId = localStorage.getItem('mental_gym_session_id') || generateSessionId();
            const response = await fetch('/api/question/random', {
                headers: {
                    'X-Session-Id': storedSessionId,
                },
                credentials: 'include',
            });
            const data = await response.json();
            setQuestion(data.question);
            setSessionId(data.session_id);
            localStorage.setItem('mental_gym_session_id', data.session_id);
            setState(data.question.principle ? 'principle' : 'question');
        } catch (error) {
            console.error('Error loading question:', error);
        }
    };

    const generateSessionId = () => {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    };

    const handleReadyForQuestion = () => {
        setState('question');
    };

    const handleSubmitResponse = () => {
        if (responseText.trim()) {
            setState('rating');
        }
    };

    const handleFinish = async () => {
        if (!question) return;

        try {
            await fetch('/api/response', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    question_id: question.id,
                    response_text: responseText,
                    rating: rating,
                    feedback_text: feedbackText,
                    anonymous_session_id: sessionId,
                }),
            });
            setState('complete');
        } catch (error) {
            console.error('Error submitting response:', error);
        }
    };

    const StarRating = () => (
        <div className="flex gap-2 justify-center">
            {[1, 2, 3, 4, 5].map((star) => (
                <button
                    key={star}
                    type="button"
                    onClick={() => setRating(star)}
                    onMouseEnter={() => setHoveredStar(star)}
                    onMouseLeave={() => setHoveredStar(null)}
                    className="transition-transform hover:scale-110"
                >
                    <svg
                        className={`w-12 h-12 transition-colors ${
                            (hoveredStar !== null ? star <= hoveredStar : rating !== null && star <= rating)
                                ? 'fill-amber-400 text-amber-400'
                                : 'fill-none text-gray-300'
                        }`}
                        stroke="currentColor"
                        strokeWidth="2"
                        viewBox="0 0 24 24"
                    >
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                </button>
            ))}
        </div>
    );

    if (state === 'loading') {
        return (
            <div className="min-h-screen bg-neutral-50 flex items-center justify-center p-4">
                <div className="text-neutral-600">Loading...</div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-neutral-50 flex flex-col">
            <header className="py-12 px-4 text-center">
                <h1 className="text-4xl font-semibold text-neutral-900 tracking-tight">Mental Gym</h1>
                <p className="mt-2 text-neutral-600">One question. 2–5 minutes. No account.</p>
            </header>

            <main className="flex-1 flex items-center justify-center p-4 pb-16">
                <div className="w-full max-w-2xl">
                    {state === 'principle' && (
                        <div className="bg-white rounded-2xl shadow-sm border border-neutral-200 p-8 md:p-12 transition-all">
                            <div className="mb-6">
                                <span className="inline-block px-3 py-1 text-sm font-medium text-neutral-500 bg-neutral-100 rounded-full">
                                    First, a principle
                                </span>
                            </div>
                            <p className="text-xl md:text-2xl text-neutral-700 leading-relaxed mb-8">
                                {question?.principle}
                            </p>
                            <button
                                onClick={handleReadyForQuestion}
                                className="w-full py-4 bg-neutral-900 text-white rounded-xl font-medium hover:bg-neutral-800 transition-all hover:shadow-md"
                            >
                                I'm ready
                            </button>
                        </div>
                    )}

                    {state === 'question' && (
                        <div className="bg-white rounded-2xl shadow-sm border border-neutral-200 p-8 md:p-12 transition-all">
                            {question?.principle && (
                                <div className="mb-8 pb-6 border-b border-neutral-100">
                                    <p className="text-base text-neutral-500 leading-relaxed">
                                        {question.principle}
                                    </p>
                                </div>
                            )}
                            <p className="text-2xl md:text-3xl text-neutral-800 leading-relaxed mb-8 font-medium">
                                {question?.text}
                            </p>
                            <textarea
                                value={responseText}
                                onChange={(e) => setResponseText(e.target.value)}
                                placeholder="Write honestly. No one sees your name."
                                className="w-full h-48 md:h-64 p-4 text-lg border border-neutral-300 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-neutral-400 focus:border-transparent transition-all"
                            />
                            <button
                                onClick={handleSubmitResponse}
                                disabled={!responseText.trim()}
                                className="mt-6 w-full py-4 bg-neutral-900 text-white rounded-xl font-medium hover:bg-neutral-800 disabled:bg-neutral-300 disabled:cursor-not-allowed transition-all hover:shadow-md"
                            >
                                Submit
                            </button>
                        </div>
                    )}

                    {state === 'rating' && (
                        <div className="bg-white rounded-2xl shadow-sm border border-neutral-200 p-8 md:p-12 transition-all">
                            <p className="text-xl text-neutral-800 mb-6 text-center">
                                Did this question help you think differently?
                            </p>
                            <StarRating />
                            <textarea
                                value={feedbackText}
                                onChange={(e) => setFeedbackText(e.target.value)}
                                placeholder="One sentence is enough."
                                className="mt-8 w-full h-32 p-4 text-base border border-neutral-300 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-neutral-400 focus:border-transparent transition-all"
                            />
                            <button
                                onClick={handleFinish}
                                className="mt-6 w-full py-4 bg-neutral-900 text-white rounded-xl font-medium hover:bg-neutral-800 transition-all hover:shadow-md"
                            >
                                Finish
                            </button>
                        </div>
                    )}

                    {state === 'complete' && (
                        <div className="bg-white rounded-2xl shadow-sm border border-neutral-200 p-8 md:p-12 text-center transition-all">
                            <p className="text-2xl text-neutral-800 mb-8">
                                Thanks for doing the work. That's one rep.
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                <button
                                    onClick={loadQuestion}
                                    className="px-8 py-4 bg-neutral-900 text-white rounded-xl font-medium hover:bg-neutral-800 transition-all hover:shadow-md"
                                >
                                    Do another
                                </button>
                                <button
                                    onClick={() => setState('done')}
                                    className="px-8 py-4 bg-white text-neutral-900 border border-neutral-300 rounded-xl font-medium hover:bg-neutral-50 transition-all"
                                >
                                    I'm done
                                </button>
                            </div>
                        </div>
                    )}

                    {state === 'done' && (
                        <div className="bg-white rounded-2xl shadow-sm border border-neutral-200 p-8 md:p-12 text-center transition-all">
                            <p className="text-3xl text-neutral-800 mb-4 font-medium">
                                Thanks for your time.
                            </p>
                            <p className="text-lg text-neutral-600 mb-8">
                                Small reflections add up. See you next time.
                            </p>
                            <button
                                onClick={loadQuestion}
                                className="px-8 py-4 bg-neutral-900 text-white rounded-xl font-medium hover:bg-neutral-800 transition-all hover:shadow-md"
                            >
                                Start again
                            </button>
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}
