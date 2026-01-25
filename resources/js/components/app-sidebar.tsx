import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { CreditCard, Users, Dumbbell, Tags, Activity, Eye, MessageCircle, Lightbulb, BookOpen, LogIn, Ruler } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Practice',
        href: '/practice-modes',
        icon: Dumbbell,
    },
    {
        title: 'Blind Spots',
        href: '/blind-spots',
        icon: Eye,
        badge: 'Pro',
    },
    {
        title: 'Playbook',
        href: '/playbook',
        icon: BookOpen,
    },
];

const guestNavItems: NavItem[] = [
    {
        title: 'Playbook',
        href: '/playbook',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const isLoggedIn = !!auth.user;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={isLoggedIn ? "/practice-modes" : "/playbook"} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={isLoggedIn ? mainNavItems : guestNavItems} />

                {auth.isAdmin && (
                    <SidebarGroup className="px-2 py-0">
                        <SidebarGroupLabel>Admin</SidebarGroupLabel>
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Practice Modes' }}
                                >
                                    <a href="/admin/practice-modes">
                                        <Dumbbell />
                                        <span>Practice Modes</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Tags' }}
                                >
                                    <a href="/admin/tags">
                                        <Tags />
                                        <span>Tags</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Principles' }}
                                >
                                    <a href="/admin/principles">
                                        <Lightbulb />
                                        <span>Principles</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Insights' }}
                                >
                                    <a href="/admin/insights">
                                        <BookOpen />
                                        <span>Insights</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Skill Dimensions' }}
                                >
                                    <a href="/admin/skill-dimensions">
                                        <Ruler />
                                        <span>Skill Dimensions</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Users' }}
                                >
                                    <a href="/admin/users">
                                        <Users />
                                        <span>Users</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Plans' }}
                                >
                                    <a href="/admin/plans">
                                        <CreditCard />
                                        <span>Plans</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'API Metrics' }}
                                >
                                    <a href="/admin/api-metrics">
                                        <Activity />
                                        <span>API Metrics</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton
                                    asChild
                                    tooltip={{ children: 'Feedback' }}
                                >
                                    <a href="/admin/feedback">
                                        <MessageCircle />
                                        <span>Feedback</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroup>
                )}
            </SidebarContent>

            <SidebarFooter>
                {isLoggedIn ? (
                    <NavUser />
                ) : (
                    <div className="flex flex-col gap-2 p-2">
                        <Button asChild variant="default" className="w-full">
                            <Link href="/register">Sign Up Free</Link>
                        </Button>
                        <Button asChild variant="ghost" className="w-full">
                            <Link href="/login">
                                <LogIn className="w-4 h-4 mr-2" />
                                Log In
                            </Link>
                        </Button>
                    </div>
                )}
            </SidebarFooter>
        </Sidebar>
    );
}
