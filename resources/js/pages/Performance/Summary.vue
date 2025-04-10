<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head title="Performance Summary Dashboard" />
    
    <div class="container mx-auto p-6">
      <div class="flex items-center justify-between mb-6">
        <!-- Performance Summary Dashboard -->
        <h1 class="text-2xl font-bold"></h1>
        <Badge variant="outline" class="text-sm">
          <Icon name="calendar" class="mr-1 h-4 w-4" />
          Last updated: {{ formatDate(new Date()) }}
        </Badge>
      </div>
      
      <!-- Dashboard Header -->
      <DashboardHeader />
      
      <!-- Time Period Tabs -->
      <TimePeriodTabs />
      
      <!-- Performance Cards -->
      <PerformanceCards />
      
      <!-- Tabs Header -->
      <TabsHeader @tab-change="handleTabChange" />
      
      <!-- Tab Content -->
      <div>
        <OnTimeContent v-if="activeTab === 'on-time'" />
        <AcceptanceContent v-if="activeTab === 'acceptance'" />
        <SafetyContent v-if="activeTab === 'safety'" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Icon from '@/components/Icon.vue';
import { Badge } from '@/components/ui/badge';

// Import tab components
import TabsHeader from '@/components/summary/TabsHeader.vue';
import OnTimeContent from '@/components/summary/OnTimeContent.vue';
import AcceptanceContent from '@/components/summary/AcceptanceContent.vue';
import SafetyContent from '@/components/summary/SafetyContent.vue';

// Import new dashboard components
import DashboardHeader from '@/components/summary/DashboardHeader.vue';
import TimePeriodTabs from '@/components/summary/TimePeriodTabs.vue';
import PerformanceCards from '@/components/summary/PerformanceCards.vue';

// Props
const props = defineProps({
  tenantSlug: String,
});

// Active tab state
const activeTab = ref('on-time');

// Handle tab change
const handleTabChange = (tabId: string) => {
  activeTab.value = tabId;
};

// Breadcrumbs for navigation
const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
  {
    title: 'Performance Summary',
    href: '#',
  },
];

// Helper: Get date range display for each summary period
const getDateRangeDisplay = (range) => {
  if (props.summaries?.[range]?.date_range) {
    // Fix timezone issue by ensuring dates are parsed correctly
    const startDate = new Date(props.summaries[range].date_range.start + 'T12:00:00');
    const endDate = new Date(props.summaries[range].date_range.end + 'T12:00:00');
    return `${formatDateShort(startDate)} - ${formatDateShort(endDate)}`;
  }
  
  // Fallback to calculated dates if not provided from backend
  const today = new Date()
  
  switch(range) {
    case 'yesterday': {
      const yesterday = new Date(today)
      yesterday.setDate(yesterday.getDate() - 1)
      return formatDateShort(yesterday)
    }
    case 'current_week': {
      const startDate = new Date(today)
      startDate.setDate(today.getDate() - today.getDay()) // Start of week (Sunday)
      const endDate = new Date(startDate)
      endDate.setDate(startDate.getDate() + 6) // End of week (Saturday)
      return `${formatDateShort(startDate)} - ${formatDateShort(endDate)}`
    }
    case 'rolling_6_weeks': {
      const endDate = new Date(today)
      endDate.setDate(today.getDate() - today.getDay() + 6) // Current week's Saturday
      const startDate = new Date(endDate)
      startDate.setDate(endDate.getDate() - (6 * 7) + 1) // 6 weeks before, starting Sunday
      return `${formatDateShort(startDate)} - ${formatDateShort(endDate)}`
    }
    case 'quarterly': {
      const endDate = new Date(today)
      const startDate = new Date(today)
      startDate.setMonth(startDate.getMonth() - 3)
      return `${formatDateShort(startDate)} - ${formatDateShort(endDate)}`
    }
    default:
      return ''
  }
}

// Helper: Format date for short display (MM/DD/YYYY)
const formatDateShort = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'numeric',
    day: 'numeric',
    year: 'numeric'
  }).format(date)
}

// Helper: Format date for display
const formatDate = (date: Date) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};
</script>