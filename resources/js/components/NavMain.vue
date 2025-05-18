<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon, { type IconName } from '@/components/Icon.vue';

defineProps<{
  items: NavItem<IconName>[];
}>();

const page = usePage<SharedData>();

// Compute current path
const currentPath = computed(() => window.location.pathname);

// More robust URL comparison
const isActive = (itemHref: string) => {
  const hrefPath = itemHref.replace(/^(https?:\/\/)?[^\/]+(\/|$)/, '/');
  return (
    currentPath.value === hrefPath ||
    currentPath.value.startsWith(hrefPath + '/') ||
    (hrefPath !== '/' && currentPath.value.startsWith(hrefPath))
  );
};
</script>

<template>
  <SidebarGroup class="px-1 py-0">
    <SidebarGroupLabel class="text-[color:hsl(var(--fixing-sidebar-foreground))] !important">Platform</SidebarGroupLabel>
    <SidebarMenu>
      <SidebarMenuItem
        v-for="item in items"
        :key="item.title"
      >
        <SidebarMenuButton
          as-child
          :is-active="isActive(item.href)"
          :tooltip="item.title"
          :class="{ 'bg-sidebar-accent font-medium ': isActive(item.href) }"
        >
          <Link :href="item.href" class="flex w-full items-center gap-2">
            <Icon :name="item.icon" class="h-5 w-5 text-[color:hsl(var(--fixing-sidebar-foreground))] !important" />
            <span class="text-[color:hsl(var(--fixing-sidebar-foreground))] !important">{{ item.title }}</span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>
