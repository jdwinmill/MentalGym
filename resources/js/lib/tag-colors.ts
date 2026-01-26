/**
 * Category-based tag color system
 *
 * Colors are automatically assigned based on tag category:
 * - skill: Blues/Indigos (cognitive/technical skills)
 * - context: Greens/Teals (situational/environmental)
 * - duration: Ambers/Oranges (time-related)
 * - role: Pinks/Roses (people/relationship-related)
 *
 * Each category has multiple shades to provide variety within a mode card.
 */

// Color definitions with Tailwind-compatible classes for light/dark mode
const CATEGORY_COLORS: Record<string, { bg: string; text: string; darkBg: string; darkText: string }[]> = {
    skill: [
        { bg: 'bg-blue-100', text: 'text-blue-700', darkBg: 'dark:bg-blue-900/30', darkText: 'dark:text-blue-400' },
        { bg: 'bg-indigo-100', text: 'text-indigo-700', darkBg: 'dark:bg-indigo-900/30', darkText: 'dark:text-indigo-400' },
        { bg: 'bg-violet-100', text: 'text-violet-700', darkBg: 'dark:bg-violet-900/30', darkText: 'dark:text-violet-400' },
    ],
    context: [
        { bg: 'bg-emerald-100', text: 'text-emerald-700', darkBg: 'dark:bg-emerald-900/30', darkText: 'dark:text-emerald-400' },
        { bg: 'bg-teal-100', text: 'text-teal-700', darkBg: 'dark:bg-teal-900/30', darkText: 'dark:text-teal-400' },
        { bg: 'bg-green-100', text: 'text-green-700', darkBg: 'dark:bg-green-900/30', darkText: 'dark:text-green-400' },
    ],
    duration: [
        { bg: 'bg-amber-100', text: 'text-amber-700', darkBg: 'dark:bg-amber-900/30', darkText: 'dark:text-amber-400' },
        { bg: 'bg-orange-100', text: 'text-orange-700', darkBg: 'dark:bg-orange-900/30', darkText: 'dark:text-orange-400' },
        { bg: 'bg-yellow-100', text: 'text-yellow-700', darkBg: 'dark:bg-yellow-900/30', darkText: 'dark:text-yellow-400' },
    ],
    role: [
        { bg: 'bg-pink-100', text: 'text-pink-700', darkBg: 'dark:bg-pink-900/30', darkText: 'dark:text-pink-400' },
        { bg: 'bg-rose-100', text: 'text-rose-700', darkBg: 'dark:bg-rose-900/30', darkText: 'dark:text-rose-400' },
        { bg: 'bg-fuchsia-100', text: 'text-fuchsia-700', darkBg: 'dark:bg-fuchsia-900/30', darkText: 'dark:text-fuchsia-400' },
    ],
};

// Default fallback for unknown categories
const DEFAULT_COLOR = { bg: 'bg-neutral-100', text: 'text-neutral-700', darkBg: 'dark:bg-neutral-800', darkText: 'dark:text-neutral-300' };

/**
 * Get the color classes for a tag based on its category and index.
 * The index provides variety within the same category.
 */
export function getTagColorClasses(category: string, index: number = 0): string {
    const categoryColors = CATEGORY_COLORS[category] || [DEFAULT_COLOR];
    const color = categoryColors[index % categoryColors.length];
    return `${color.bg} ${color.text} ${color.darkBg} ${color.darkText}`;
}

/**
 * Maximum number of tags to display per practice mode card
 */
export const MAX_VISIBLE_TAGS = 4;

/**
 * Get visible tags limited to MAX_VISIBLE_TAGS, prioritized by category order:
 * skill -> context -> role -> duration
 */
export function getVisibleTags<T extends { category: string }>(tags: T[]): T[] {
    const priorityOrder = ['skill', 'context', 'role', 'duration'];

    const sorted = [...tags].sort((a, b) => {
        const aPriority = priorityOrder.indexOf(a.category);
        const bPriority = priorityOrder.indexOf(b.category);
        return (aPriority === -1 ? 999 : aPriority) - (bPriority === -1 ? 999 : bPriority);
    });

    return sorted.slice(0, MAX_VISIBLE_TAGS);
}
