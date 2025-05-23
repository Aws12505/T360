<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, defineProps } from 'vue';

const page = usePage();
const tenantSlug = page.props.tenantSlug as string | null;
const isSuperAdmin = page.props.SuperAdmin as boolean;
const props= defineProps<{
    permissions: Array<{
        id: number;
        name: string;
        guard_name: string;
        created_at: string;
        updated_at: string;
        pivot: object;
    }>;
}>();
const permissionsNames = computed(() => props.permissions?.map((permission) => permission.name) || []);
// Only show tenant settings if user has a tenant and is not a SuperAdmin
const showTenantSettings = tenantSlug && !isSuperAdmin;

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
    // Add tenant settings only for tenant users (not SuperAdmin)
    ...(showTenantSettings && permissionsNames.value.includes('tenant-settings.view') ? [{
        title: 'Company',
        href: `/${tenantSlug}/settings/tenant`,
    }] : []),
    ...(permissionsNames.value.includes('users.view') || permissionsNames.value.includes('roles.view') ? [
    {
        title: 'User Management',
        href: tenantSlug? route('users.roles.index', { tenantSlug: tenantSlug }) : route('admin.users.roles.index')
    }] : []),
    ...(showTenantSettings && permissionsNames.value.includes('sms-coaching.view')? [{
        title: 'Safety Coaching Thresholds',
        href: route('sms-coaching.edit', { tenantSlug: tenantSlug }),
    }] : []),
];

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
const isActive = (href: string) => {
  // exact match OR anything deeper under this URL
  return currentPath === href || currentPath.startsWith(href + '/');
};
</script>

<template>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col space-y-6 md:flex-row md:space-y-0 md:space-x-4 lg:space-x-6 mt-6">
            <!-- Sidebar navigation - responsive width -->
            <aside class="w-full md:w-56 lg:w-64 shrink-0">
                <div class="sticky top-6">
                    <nav class="flex flex-col space-y-1">
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="item.href"
                            variant="ghost"
                            :class="['w-full justify-start', { 'bg-muted': isActive(item.href) }]"
                            as-child
                        >
                            <Link :href="item.href">
                                {{ item.title }}
                            </Link>
                        </Button>
                    </nav>
                </div>
            </aside>

            <Separator class="my-4 md:hidden" />

            <!-- Main content area - centered with responsive width -->
            <div class="flex-1 w-full flex justify-center">
                <section class="w-full max-w-3xl space-y-6">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
