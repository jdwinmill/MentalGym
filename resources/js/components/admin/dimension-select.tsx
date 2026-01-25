import { useState, useEffect, useRef } from 'react';
import { X, ChevronDown, Search } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface Dimension {
    key: string;
    label: string;
    category: string;
}

interface Props {
    value: string[];
    onChange: (value: string[]) => void;
}

const categoryLabels: Record<string, string> = {
    communication: 'Communication',
    reasoning: 'Reasoning',
    resilience: 'Resilience',
    influence: 'Influence',
    self_awareness: 'Self-Awareness',
};

const categoryOrder = ['communication', 'reasoning', 'resilience', 'influence', 'self_awareness'];

export default function DimensionSelect({ value, onChange }: Props) {
    const [dimensions, setDimensions] = useState<Dimension[]>([]);
    const [isOpen, setIsOpen] = useState(false);
    const [search, setSearch] = useState('');
    const [loading, setLoading] = useState(true);
    const containerRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        fetch('/api/skill-dimensions')
            .then(res => res.json())
            .then(data => {
                setDimensions(data);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const toggleDimension = (key: string) => {
        if (value.includes(key)) {
            onChange(value.filter(k => k !== key));
        } else {
            onChange([...value, key]);
        }
    };

    const removeDimension = (key: string, e: React.MouseEvent) => {
        e.stopPropagation();
        onChange(value.filter(k => k !== key));
    };

    const filteredDimensions = dimensions.filter(dim =>
        dim.label.toLowerCase().includes(search.toLowerCase()) ||
        dim.key.toLowerCase().includes(search.toLowerCase())
    );

    const groupedDimensions = categoryOrder.reduce((acc, category) => {
        const dims = filteredDimensions.filter(d => d.category === category);
        if (dims.length > 0) {
            acc[category] = dims;
        }
        return acc;
    }, {} as Record<string, Dimension[]>);

    const selectedDimensions = dimensions.filter(d => value.includes(d.key));

    return (
        <div ref={containerRef} className="relative">
            <div
                className={cn(
                    "min-h-[40px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm cursor-pointer",
                    "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]",
                    isOpen && "border-ring ring-ring/50 ring-[3px]"
                )}
                onClick={() => {
                    setIsOpen(true);
                    inputRef.current?.focus();
                }}
            >
                <div className="flex flex-wrap gap-1.5 items-center">
                    {selectedDimensions.map(dim => (
                        <Badge
                            key={dim.key}
                            variant="secondary"
                            className="text-xs flex items-center gap-1"
                        >
                            {dim.label}
                            <X
                                className="h-3 w-3 cursor-pointer hover:text-red-500"
                                onClick={(e) => removeDimension(dim.key, e)}
                            />
                        </Badge>
                    ))}
                    <div className="flex-1 min-w-[120px] flex items-center">
                        <Search className="h-3 w-3 text-muted-foreground mr-1" />
                        <input
                            ref={inputRef}
                            type="text"
                            className="flex-1 bg-transparent outline-none text-sm placeholder:text-muted-foreground"
                            placeholder={selectedDimensions.length === 0 ? "Search dimensions..." : ""}
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onFocus={() => setIsOpen(true)}
                        />
                    </div>
                    <ChevronDown className={cn(
                        "h-4 w-4 text-muted-foreground transition-transform",
                        isOpen && "rotate-180"
                    )} />
                </div>
            </div>

            {isOpen && (
                <div className="absolute z-50 w-full mt-1 rounded-md border bg-popover shadow-lg max-h-[300px] overflow-auto">
                    {loading ? (
                        <div className="p-3 text-sm text-muted-foreground">Loading...</div>
                    ) : Object.keys(groupedDimensions).length === 0 ? (
                        <div className="p-3 text-sm text-muted-foreground">No dimensions found</div>
                    ) : (
                        categoryOrder.map(category => {
                            const dims = groupedDimensions[category];
                            if (!dims) return null;

                            return (
                                <div key={category}>
                                    <div className="px-3 py-1.5 text-xs font-semibold text-muted-foreground bg-muted/50 sticky top-0">
                                        {categoryLabels[category]}
                                    </div>
                                    {dims.map(dim => (
                                        <div
                                            key={dim.key}
                                            className={cn(
                                                "px-3 py-2 text-sm cursor-pointer hover:bg-accent",
                                                value.includes(dim.key) && "bg-accent"
                                            )}
                                            onClick={() => toggleDimension(dim.key)}
                                        >
                                            <div className="flex items-center justify-between">
                                                <span>{dim.label}</span>
                                                {value.includes(dim.key) && (
                                                    <span className="text-xs text-primary">Selected</span>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            );
                        })
                    )}
                </div>
            )}
        </div>
    );
}
