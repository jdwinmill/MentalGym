import { useAppearance } from '@/hooks/use-appearance';
import { Moon, Sun } from 'lucide-react';

export function DarkModeToggle() {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';

    const toggle = () => {
        updateAppearance(isDark ? 'light' : 'dark');
    };

    return (
        <button
            onClick={toggle}
            className="relative h-8 w-14 rounded-full bg-neutral-200 p-1 transition-colors duration-200 dark:bg-neutral-700"
            aria-label={`Switch to ${isDark ? 'light' : 'dark'} mode`}
        >
            <div
                className={`flex h-6 w-6 items-center justify-center rounded-full bg-white shadow-sm transition-transform duration-200 dark:bg-neutral-900 ${
                    isDark ? 'translate-x-6' : 'translate-x-0'
                }`}
            >
                {isDark ? (
                    <Moon className="h-3.5 w-3.5 text-neutral-300" />
                ) : (
                    <Sun className="h-3.5 w-3.5 text-amber-500" />
                )}
            </div>
        </button>
    );
}
