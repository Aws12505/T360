<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const props = defineProps({
  tenantSlug: {
    type: String,
    default: null,
  },
  permissions: {type: Array,default: []}
});
const { tenantSlug } = props;

// Update breadcrumbs to use our new routes.
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: tenantSlug ? 'Dashboard' : 'Admin Dashboard', 
        href: tenantSlug ? route('dashboard', { tenantSlug }) : route('admin.dashboard'), 
    },
];
</script>

<template>
    <Head :title="breadcrumbs[0].title" />

    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="props.permissions">
        <div class="w-full md:max-w-xl lg:max-w-2xl xl:max-w-4xl m-auto pt-2  space-y-2 md:space-y-4 lg:space-y-6">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
