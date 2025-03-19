<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import Breadcrumb from '@/components/ui/breadcrumb/Breadcrumb.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';

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
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :Breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
