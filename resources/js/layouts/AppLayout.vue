<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    tenantSlug?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    tenantSlug: null, 
});
</script>

<template>
    <AppLayout :breadcrumbs="props.breadcrumbs" :tenantSlug="props.tenantSlug">
        <div class="w-full mx-auto px-1 sm:px-2 md:px-3 lg:px-4 max-w-7xl">
            <slot />
        </div>
    </AppLayout>
</template>
