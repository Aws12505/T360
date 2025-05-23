<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    tenantSlug?: string | null;
    permissions: string[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    tenantSlug: null, 
    permissions: [],
});
</script>

<template>
    <AppLayout :breadcrumbs="props.breadcrumbs" :tenantSlug="props.tenantSlug" :permissions="props.permissions">
            <slot />
    </AppLayout>
</template>
