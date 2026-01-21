import AuthLayout from '@/layouts/auth-layout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { CheckCircle } from 'lucide-react';

interface Props {
    type: string;
    typeName: string;
}

export default function Unsubscribed({ typeName }: Props) {
    return (
        <AuthLayout
            title="Unsubscribed"
            description="You've been unsubscribed from this email type."
        >
            <Head title="Unsubscribed" />

            <div className="space-y-6 text-center">
                <div className="flex justify-center">
                    <CheckCircle className="h-12 w-12 text-green-500" />
                </div>

                <div className="space-y-2">
                    <h2 className="text-lg font-semibold">
                        Successfully Unsubscribed
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        You will no longer receive {typeName} emails.
                    </p>
                </div>

                <p className="text-sm text-muted-foreground">
                    You can manage all your email preferences in your account settings.
                </p>

                <Button asChild>
                    <Link href="/dashboard">
                        Return to Dashboard
                    </Link>
                </Button>
            </div>
        </AuthLayout>
    );
}
