<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link , usePage} from '@inertiajs/vue3';
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

const page = usePage();
const tenantSlug = page.props.tenantSlug as string | null;
const mainNavItems: NavItem[] = [
    {
        title: tenantSlug ? 'Dashboard' : 'Admin Dashboard', 
        href: tenantSlug ? `/${tenantSlug}/dashboard` : '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: tenantSlug ? `/${tenantSlug}/users-roles` :'/admin/users-roles',
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
