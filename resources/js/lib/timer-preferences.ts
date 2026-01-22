const STORAGE_KEY = 'mentalgym_timer_prefs';

interface TimerPreferences {
    hasSeenIntro: boolean;
    pauseStreak: number;
    timerDisabled: boolean;
}

const DEFAULT_PREFS: TimerPreferences = {
    hasSeenIntro: false,
    pauseStreak: 0,
    timerDisabled: false,
};

function getPrefs(): TimerPreferences {
    if (typeof window === 'undefined') return DEFAULT_PREFS;
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (!stored) return DEFAULT_PREFS;
        return { ...DEFAULT_PREFS, ...JSON.parse(stored) };
    } catch {
        return DEFAULT_PREFS;
    }
}

function savePrefs(prefs: TimerPreferences): void {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
    } catch {
        // localStorage might be unavailable
    }
}

export function hasSeenTimerIntro(): boolean {
    return getPrefs().hasSeenIntro;
}

export function markTimerIntroSeen(): void {
    const prefs = getPrefs();
    prefs.hasSeenIntro = true;
    savePrefs(prefs);
}

export function isTimerDisabled(): boolean {
    return getPrefs().timerDisabled;
}

export function setTimerDisabled(disabled: boolean): void {
    const prefs = getPrefs();
    prefs.timerDisabled = disabled;
    if (!disabled) {
        prefs.pauseStreak = 0;
    }
    savePrefs(prefs);
}

export function recordTimerPause(): boolean {
    const prefs = getPrefs();
    prefs.pauseStreak += 1;
    savePrefs(prefs);
    // Return true if we should prompt about disabling
    return prefs.pauseStreak >= 3;
}

export function recordTimerCompletion(): void {
    const prefs = getPrefs();
    prefs.pauseStreak = 0;
    savePrefs(prefs);
}

export function getPauseStreak(): number {
    return getPrefs().pauseStreak;
}
