<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    tenantSlug?: string | null;
    permissions: string[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    tenantSlug: null, 
    permissions: [],
});
const permissionNames = computed(() =>
      props.permissions.map(p => p.name)
    );
const mainNavItems = computed<NavItem[]>(() => [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
    icon: 'layoutGrid',
  },
  // Only show this for global Admin
  ...(!props.tenantSlug
    ? [
        {
          title: 'Metrics Management',
          href: route('performance-metrics.edit'),
          icon: 'gauge',
        },
      ]
    : []),
  // Only show Performance if user has performance.view permission
  ...(permissionNames.value.includes('performance.view')
    ? [
        {
          title: 'Performance',
          href: props.tenantSlug
            ? route('performance.index', { tenantSlug: props.tenantSlug })
            : route('performance.index.admin'),
          icon: 'barChart',
        },
      ]
    : []),
    ...(permissionNames.value.includes('acceptance.view')
    ? [
    {
    title: 'Acceptance',
    href: props.tenantSlug
      ? route('acceptance.index', { tenantSlug: props.tenantSlug })
      : route('acceptance.index.admin'),
    icon: 'checkCircle',
  },]
  : []),
  ...(permissionNames.value.includes('delays.view')
  ? [
  {
    title: 'On-Time',
    href: props.tenantSlug
      ? route('ontime.index', { tenantSlug: props.tenantSlug })
      : route('ontime.index.admin'),
    icon: 'clock',
  },]
  : []),
  ...(permissionNames.value.includes('safety-data.view')
  ? [
  {
    title: 'Safety',
    href: props.tenantSlug
      ? route('safety.index', { tenantSlug: props.tenantSlug })
      : route('safety.index.admin'),
    icon: 'shieldCheck',
  },]
  : []),
  ...(permissionNames.value.includes('trucks.view') || 
      permissionNames.value.includes('repair-orders.view') || 
      permissionNames.value.includes('miles-driven.view')
  ? [
  {
    title: 'Asset Maintenance',
    href: props.tenantSlug
      ? route('repair_orders.index', { tenantSlug: props.tenantSlug })
      : route('repair_orders.index.admin'),
    icon: 'wrench',
  },]
  : []),
  ...(permissionNames.value.includes('drivers.view')
  ? [
  {
    title: 'Drivers',
    href: props.tenantSlug
      ? route('driver.index', { tenantSlug: props.tenantSlug })
      : route('driver.index.admin'),
    icon: 'users',
  },]
  : []),

]);

const footerNavItems: NavItem[] = [
  {
    title: 'Support',
    href: props.tenantSlug
      ? route('support.index', { tenantSlug: props.tenantSlug })
      : route('support.index.admin'),
    icon: 'helpCircle',
  },
  {
    title: 'Feedback',
    href: props.tenantSlug
      ? route('support.feedback.index', { tenantSlug: props.tenantSlug })
      : route('support.feedback.index.admin'),
    icon: 'feedback',
  },
  {
    title: 'Settings',
    href: route('profile.edit'),
    icon: 'cog',
  },
];

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <!-- Updated link using route() helper -->
                        <Link :href="props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border/30 pt-2 ">
             <NavFooter :items="footerNavItems" class="mb-2 " />  
            <NavUser :tenantSlug="props.tenantSlug" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
