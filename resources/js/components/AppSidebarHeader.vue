<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage<SharedData>();
const user = page.props.auth.user;
const tenant = user?.tenant;
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex w-full items-center justify-between">
            <div class="flex items-center gap-3">
                <SidebarTrigger class="-ml-1 hover:bg-sidebar-accent/20 rounded-md transition-colors" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
            
            <div class="hidden md:block text-center">
                <h2 class="text-base md:text-lg font-semibold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent px-4 py-1.5 border-b-2 border-primary/30">Your partner to Fantastic +</h2>
            </div>
            
            <div v-if="tenant" class="flex items-center gap-3 bg-sidebar-accent/10 px-3 py-1.5 rounded-md">
                <img 
                    v-if="tenant.image_path" 
                    :src="`/storage/${tenant.image_path}`" 
                    class="h-8 w-8 object-contain rounded-sm" 
                    alt="Tenant logo" 
                />
                <span class="font-medium text-foreground">{{ tenant.name }}</span>
            </div>
        </div>
    </header>
</template>
