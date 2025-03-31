<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, UserCog } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    tenantSlug?: string | null;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    tenantSlug: null, 
});

// Get the tenantSlug from page props.
const page = usePage();
const tenantSlug = page.props.tenantSlug as string | null;

// Update navigation items to use route() helper.
const mainNavItems: NavItem[] = [
    {
        title: tenantSlug ? 'Dashboard' : 'Admin Dashboard', 
        href: tenantSlug ? route('dashboard', { tenantSlug }) : route('admin.dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: tenantSlug ? route('users.roles.index', { tenantSlug }) : route('admin.users.roles.index'),
        icon: UserCog,
    },
    {
        title: 'Metrics Management',
        // Metrics management is only available to Admin
        href: route('performance-metrics.edit'),
        icon: UserCog,
    },
    {
        title: 'Performance',
        href: tenantSlug ? route('performance.index', { tenantSlug }) : route('performance.index.admin'),
        icon: UserCog,
    },
    {
        title: 'Safety',
        href: tenantSlug ? route('safety.index', { tenantSlug }) : route('safety.index.admin'),
        icon: UserCog,
    },
    {
        title: 'Acceptance',
        href: tenantSlug ? route('acceptance.index', { tenantSlug }) : route('acceptance.index.admin'),
        icon: UserCog,
    },
    {
        title: 'On-Time',
        href: tenantSlug ? route('ontime.index', { tenantSlug }) : route('ontime.index.admin'),
        icon: UserCog,
    },
    {
        title: 'Trucks',
        href: tenantSlug ? route('truck.index', { tenantSlug }) : route('truck.index.admin'),
        icon: UserCog,
    }
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <!-- Updated link using route() helper -->
                        <Link :href="tenantSlug ? route('dashboard', { tenantSlug }) : route('admin.dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser :tenantSlug="tenantSlug" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
