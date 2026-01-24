import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { Sparkles, Coffee } from 'lucide-react';

interface LimitReachedDialogProps {
    isOpen: boolean;
    plan: string;
    onClose: () => void;
}

export function LimitReachedDialog({ isOpen, plan, onClose }: LimitReachedDialogProps) {
    const isFree = plan === 'free';

    return (
        <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                        {isFree ? (
                            <Sparkles className="h-6 w-6 text-primary" />
                        ) : (
                            <Coffee className="h-6 w-6 text-primary" />
                        )}
                    </div>
                    <DialogTitle className="text-center">
                        {isFree ? "You're on a roll!" : "Nice work today!"}
                    </DialogTitle>
                    <DialogDescription className="text-center">
                        {isFree ? (
                            <>
                                You've hit your daily limit on the free plan.
                                Upgrade to Pro for 10x more practice each day and unlock all features.
                            </>
                        ) : (
                            <>
                                You've completed your daily practice. Take some time to reflect on what you learned today.
                                The best growth happens when you give your mind time to process.
                            </>
                        )}
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter className="flex-col gap-2 sm:flex-col">
                    {isFree ? (
                        <>
                            <Button asChild className="w-full">
                                <Link href="/pricing">
                                    <Sparkles className="mr-2 h-4 w-4" />
                                    Upgrade to Pro
                                </Link>
                            </Button>
                            <Button variant="ghost" onClick={onClose} className="w-full">
                                Maybe later
                            </Button>
                        </>
                    ) : (
                        <>
                            <Button asChild className="w-full">
                                <Link href="/playbook">
                                    Read the Playbook
                                </Link>
                            </Button>
                            <Button variant="ghost" onClick={onClose} className="w-full">
                                Back to Practice
                            </Button>
                        </>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
