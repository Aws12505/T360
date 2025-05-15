<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, SharedData } from '@/types';
import { usePage, Link } from '@inertiajs/vue3';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage<SharedData>();
const user = page.props.auth.user;
const tenant = user?.tenant;
</script>

<template>
    <header
        class="flex w-full h-26 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-26 md:px-4"
    >
        <div class="flex w-full flex-col justify-between h-full py-3 relative">
            <div class="flex flex-col md:flex-row items-center justify-between w-full relative">
                <div class="invisible flex items-center gap-3 z-10">
                    <SidebarTrigger class="-ml-1 hover:bg-sidebar-accent/20 rounded-md transition-colors" />
                </div>
                
                <div class="absolute left-1/2 transform -translate-x-1/2 text-center">
                    <h2 class="text-base md:text-lg font-semibold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent px-4 py-1.5 border-b-2 border-primary/30 whitespace-nowrap inline-block">Your partner to Fantastic +</h2>
                </div>
                
                <div v-if="tenant" class="flex mt-3 md:mt-0 items-center gap-2 bg-gradient-to-r from-sidebar-accent/20 to-sidebar-primary/10 px-2 py-1 rounded-md border border-sidebar-border/30 shadow-sm z-10">
                    <Link :href="route('tenant.settings.edit', tenant.slug)" class="flex items-center gap-2 hover:opacity-90 transition-opacity">
                        <div class="h-8 w-8 flex items-center justify-center bg-white rounded-full shadow-sm border border-sidebar-border/50">
                            <img 
                                v-if="tenant.image_path" 
                                :src="`/storage/${tenant.image_path}`" 
                                class="h-6 w-6 object-contain" 
                                alt="Tenant logo" 
                            />
                            <span v-else class="text-sm font-bold text-primary">{{ tenant.name.charAt(0) }}</span>
                        </div>
                        <span class="font-medium text-foreground text-sm">{{ tenant.name }}</span>
                    </Link>
                </div>
            </div>
            
            <div class="flex items-center gap-3 mt-3">
                <SidebarTrigger class="-ml-1 hover:bg-sidebar-accent/20 rounded-md transition-colors" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
        </div>
    </header>
</template>
