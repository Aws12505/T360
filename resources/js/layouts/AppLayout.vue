<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
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
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
        <slot />
    </AppLayout>
</template>
