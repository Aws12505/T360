<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();

// Create a computed property for the current path
const currentPath = computed(() => {
    return window.location.pathname;
});

// More robust URL comparison function
const isActive = (itemHref: string) => {
    // Extract path from href (remove protocol, domain, etc.)
    const hrefPath = itemHref.replace(/^(https?:\/\/)?[^\/]+(\/|$)/, '/');
    
    // Compare paths
    return currentPath.value === hrefPath || 
           currentPath.value.startsWith(hrefPath + '/') ||
           (hrefPath !== '/' && currentPath.value.startsWith(hrefPath));
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <!-- Debug output -->
                <!-- <div class="text-xs">{{ currentPath }} vs {{ item.href }} = {{ isActive(item.href) }}</div> -->
                <SidebarMenuButton 
                    as-child
                    :is-active="isActive(item.href)"
                    :tooltip="item.title"
                    :class="{ 'bg-sidebar-accent font-medium text-sidebar-accent-foreground': isActive(item.href) }"
                >
                    <Link :href="item.href" class="flex w-full items-center gap-2">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
