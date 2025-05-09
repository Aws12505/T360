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
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    tenantSlug: null, 
});

const mainNavItems = computed<NavItem[]>(() => [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
    icon: 'layoutGrid',
  },
  {
    title: 'User Management',
    href: props.tenantSlug
      ? route('users.roles.index', { tenantSlug: props.tenantSlug })
      : route('admin.users.roles.index'),
    icon: 'userCog',
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
  {
    title: 'Performance',
    href: props.tenantSlug
      ? route('performance.index', { tenantSlug: props.tenantSlug })
      : route('performance.index.admin'),
    icon: 'barChart',
  },
  {
    title: 'Safety',
    href: props.tenantSlug
      ? route('safety.index', { tenantSlug: props.tenantSlug })
      : route('safety.index.admin'),
    icon: 'shieldCheck',
  },
  {
    title: 'Acceptance',
    href: props.tenantSlug
      ? route('acceptance.index', { tenantSlug: props.tenantSlug })
      : route('acceptance.index.admin'),
    icon: 'checkCircle',
  },
  {
    title: 'On-Time',
    href: props.tenantSlug
      ? route('ontime.index', { tenantSlug: props.tenantSlug })
      : route('ontime.index.admin'),
    icon: 'clock',
  },
  {
    title: 'Trucks',
    href: props.tenantSlug
      ? route('truck.index', { tenantSlug: props.tenantSlug })
      : route('truck.index.admin'),
    icon: 'truck',
  },
  {
    title: 'Drivers',
    href: props.tenantSlug
      ? route('driver.index', { tenantSlug: props.tenantSlug })
      : route('driver.index.admin'),
    icon: 'users',
  },
  {
    title: 'Repair Orders',
    href: props.tenantSlug
      ? route('repair_orders.index', { tenantSlug: props.tenantSlug })
      : route('repair_orders.index.admin'),
    icon: 'clipboardList',
  },
  {
    title: 'Miles Driven',
    href: props.tenantSlug
      ? route('miles_driven.index', { tenantSlug: props.tenantSlug })
      : route('miles_driven.index.admin'),
    icon: 'lineChart',
  },
  ...(props.tenantSlug
    ? [
        {
          title: 'Safety Coaching Thresholds',
          href: route('sms-coaching.edit', { tenantSlug: props.tenantSlug }),
          icon: 'shieldAlert',
        },
      ]
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

        <SidebarFooter class="border-t border-sidebar-border/30 pt-2">
             <NavFooter :items="footerNavItems" class="mb-2" />  
            <NavUser :tenantSlug="props.tenantSlug" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
