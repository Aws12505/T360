<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import Icon from '@/components/Icon.vue';
import { Link } from '@inertiajs/vue3';

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton 
                        class="text-neutral-000 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100 transition-colors duration-200 hover:bg-sidebar-accent rounded-md" 
                        as-child
                    >
                        <Link :href="item.href" rel="noopener noreferrer" class="flex items-center gap-2 px-2 py-1.5">
                            <Icon :name="item.icon" class="h-5 w-5 text-[color:hsl(var(--fixing-sidebar-foreground))] !important" />
                            <span class="font-medium text-[color:hsl(var(--fixing-sidebar-foreground))] !important">{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
