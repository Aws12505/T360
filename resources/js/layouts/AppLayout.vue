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
        <div class="w-full mx-auto px-4 sm:px-6 md:px-8 lg:px-10 max-w-7xl">
            <slot />
        </div>
    </AppLayout>
</template>
