import * as React from "react"
import { cn } from "@/lib/utils"

interface HighlightedTextareaProps extends React.ComponentProps<"textarea"> {
    /** List of valid context field names (without the {{ }} wrapper) */
    validContextFields?: string[];
    /** Always highlight {{level}} as a valid placeholder */
    includeLevelPlaceholder?: boolean;
}

function HighlightedTextarea({
    className,
    validContextFields = [],
    includeLevelPlaceholder = true,
    value,
    onChange,
    ...props
}: HighlightedTextareaProps) {
    const textareaRef = React.useRef<HTMLTextAreaElement>(null);
    const backdropRef = React.useRef<HTMLDivElement>(null);

    // Build set of valid context placeholders (not including level)
    const validPlaceholders = React.useMemo(() => {
        return new Set(validContextFields);
    }, [validContextFields]);

    // Sync scroll between textarea and backdrop
    const handleScroll = () => {
        if (backdropRef.current && textareaRef.current) {
            backdropRef.current.scrollTop = textareaRef.current.scrollTop;
            backdropRef.current.scrollLeft = textareaRef.current.scrollLeft;
        }
    };

    // Generate highlighted HTML content
    const getHighlightedContent = React.useCallback((text: string) => {
        if (!text) return '';

        // Match {{anything}} patterns
        const parts = text.split(/(\{\{[^}]*\}\})/g);

        return parts.map((part, index) => {
            const match = part.match(/^\{\{([^}]*)\}\}$/);
            if (match) {
                const fieldName = match[1];
                const isContextField = validPlaceholders.has(fieldName);
                const isLevelPlaceholder = includeLevelPlaceholder && fieldName === 'level';

                let bgClass = "bg-amber-200 dark:bg-amber-700/70"; // unrecognized
                if (isContextField) {
                    bgClass = "bg-emerald-200 dark:bg-emerald-700/70"; // matched context
                } else if (isLevelPlaceholder) {
                    bgClass = "bg-sky-200 dark:bg-sky-700/70"; // level placeholder
                }

                return (
                    <mark
                        key={index}
                        className={cn("rounded-sm text-transparent", bgClass)}
                    >
                        {part}
                    </mark>
                );
            }
            // Return text with preserved whitespace
            return <React.Fragment key={index}>{part}</React.Fragment>;
        });
    }, [validPlaceholders, includeLevelPlaceholder]);

    const textValue = typeof value === 'string' ? value : '';

    return (
        <div className="relative">
            {/* Backdrop with highlighting - positioned behind textarea */}
            <div
                ref={backdropRef}
                aria-hidden="true"
                className={cn(
                    "absolute inset-0 overflow-auto pointer-events-none",
                    "border border-transparent rounded-md px-3 py-2",
                    "text-base md:text-sm whitespace-pre-wrap break-words",
                    "text-transparent", // Text is transparent, only highlights show
                    className
                )}
            >
                {getHighlightedContent(textValue)}
                {/* Add trailing newline to ensure backdrop matches textarea height */}
                {'\n'}
            </div>

            {/* Actual textarea - transparent background to show highlights */}
            <textarea
                ref={textareaRef}
                data-slot="textarea"
                value={value}
                onChange={onChange}
                onScroll={handleScroll}
                className={cn(
                    "relative z-10 bg-transparent",
                    "border-input placeholder:text-muted-foreground flex min-h-[80px] w-full rounded-md border px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
                    "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
                    "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
                    className
                )}
                {...props}
            />
        </div>
    );
}

export { HighlightedTextarea }
