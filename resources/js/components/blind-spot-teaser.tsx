import { useEffect, useState } from 'react';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Lock, TrendingUp, TrendingDown, Eye } from 'lucide-react';

interface TeaserData {
    blindSpotCount: number;
    hasImprovements: boolean;
    hasRegressions: boolean;
    totalSessions: number;
}

interface BlindSpotTeaserProps {
    onUpgrade?: () => void;
}

export function BlindSpotTeaser({ onUpgrade }: BlindSpotTeaserProps) {
    const [loading, setLoading] = useState(true);
    const [showTeaser, setShowTeaser] = useState(false);
    const [teaser, setTeaser] = useState<TeaserData | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchTeaserData();
    }, []);

    const fetchTeaserData = async () => {
        try {
            const response = await fetch('/api/blind-spots/teaser');
            const data = await response.json();

            if (data.success) {
                setShowTeaser(data.showTeaser);
                setTeaser(data.teaser);
            } else {
                setError('Failed to load insights');
            }
        } catch {
            setError('Failed to load insights');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <Card className="relative overflow-hidden">
                <CardHeader>
                    <div className="h-5 w-32 animate-pulse rounded bg-muted" />
                    <div className="h-4 w-48 animate-pulse rounded bg-muted" />
                </CardHeader>
                <CardContent>
                    <div className="h-16 animate-pulse rounded bg-muted" />
                </CardContent>
            </Card>
        );
    }

    if (error || !showTeaser || !teaser) {
        return null;
    }

    return (
        <Card className="relative overflow-hidden border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 dark:border-amber-900 dark:from-amber-950/20 dark:to-orange-950/20">
            <div className="absolute right-4 top-4">
                <Badge variant="secondary" className="gap-1">
                    <Lock className="h-3 w-3" />
                    Pro
                </Badge>
            </div>

            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg">
                    <Eye className="h-5 w-5 text-amber-600" />
                    Blind Spots Detected
                </CardTitle>
                <CardDescription>
                    We've analyzed {teaser.totalSessions} training sessions
                </CardDescription>
            </CardHeader>

            <CardContent className="space-y-4">
                <div className="flex items-center gap-6">
                    <div className="text-center">
                        <div className="text-3xl font-bold text-amber-600">
                            {teaser.blindSpotCount}
                        </div>
                        <div className="text-sm text-muted-foreground">
                            {teaser.blindSpotCount === 1 ? 'Blind Spot' : 'Blind Spots'}
                        </div>
                    </div>

                    <div className="flex flex-col gap-2">
                        {teaser.hasImprovements && (
                            <div className="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                <TrendingUp className="h-4 w-4" />
                                <span>Areas improving</span>
                            </div>
                        )}
                        {teaser.hasRegressions && (
                            <div className="flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                                <TrendingDown className="h-4 w-4" />
                                <span>Areas needing attention</span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="space-y-2 rounded-lg border border-dashed border-amber-300 bg-amber-100/50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                    <div className="flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-200">
                        <Lock className="h-4 w-4" />
                        Insights locked
                    </div>
                    <div className="space-y-1">
                        <div className="h-3 w-3/4 rounded bg-amber-200/70 dark:bg-amber-800/50" />
                        <div className="h-3 w-1/2 rounded bg-amber-200/70 dark:bg-amber-800/50" />
                        <div className="h-3 w-2/3 rounded bg-amber-200/70 dark:bg-amber-800/50" />
                    </div>
                </div>
            </CardContent>

            <CardFooter>
                <Button
                    onClick={onUpgrade}
                    className="w-full bg-amber-600 hover:bg-amber-700"
                >
                    Upgrade to Pro to unlock insights
                </Button>
            </CardFooter>
        </Card>
    );
}
