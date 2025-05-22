<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

interface Props {
    tenantSlug?: string;
    permissions?: any[];
}

const props = defineProps<Props>();

// Make breadcrumbItems reactive with computed property
const breadcrumbItems = computed(() => [
    {
        title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
        href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'),
    },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems" :tenantSlug="props.tenantSlug">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
