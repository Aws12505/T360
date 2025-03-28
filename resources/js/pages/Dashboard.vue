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

    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
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
