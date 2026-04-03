<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="props.permissions">

    <Head title="Performance Summary Dashboard" />

    <div
      class="w-full max-w-[1400px] mx-auto px-3 sm:px-4 lg:px-6 pt-4 sm:pt-5 lg:pt-6 space-y-6 lg:space-y-8 pb-6 sm:pb-8 lg:pb-10">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">
            Performance Summary
          </h1>
          <p class="text-sm text-muted-foreground">
            Operational overview and key metrics
          </p>
        </div>

        <Badge variant="outline" class="text-xs sm:text-sm whitespace-nowrap">
          <Icon name="calendar" class="mr-1 h-4 w-4" />
          {{ formatLastUpdated }}
        </Badge>
      </div>

      <!-- Dashboard Header -->
      <DashboardHeader :operationalExcellenceScore="operationalExcellenceScore" />

      <!-- Tabs -->
      <TimePeriodTabs @tab-change="handleTimePeriodChange" :dateRangeText="currentDateRangeText"
        :weekNumber="dateRange?.weekNumber" :startWeekNumber="dateRange?.startWeekNumber"
        :endWeekNumber="dateRange?.endWeekNumber" :year="dateRange?.year" :activeTabId="currentDateFilter" />

      <!-- Performance Cards -->
      <PerformanceCards v-if="summaries" :performanceData="summaries.performance?.data || {}"
        :performanceRatings="summaries.performance?.ratings || {}" :safetyData="summaries.safety || {}"
        :delayBreakdowns="delayBreakdowns?.by_reason || []" :rejectionBreakdowns="rejectionBreakdowns?.by_reason || []"
        :maintenanceBreakdowns="maintenanceBreakdowns || {}" :milesDriven="milesDriven" />

      <!-- Base + Safety Combined Section -->
      <!-- Operational Metrics -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg sm:text-xl font-semibold tracking-tight">
            Operational Metrics
          </h2>
        </div>

        <div class="bg-card rounded-xl border shadow-sm p-4 sm:p-5 lg:p-6">

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 lg:divide-x">

            <!-- Base Metrics -->
            <div class="lg:pr-6">
              <BaseMetricsCard :performanceData="summaries.performance?.data || {}"
                :performanceRatings="summaries.performance?.ratings || {}" />
            </div>

            <!-- Safety Metrics -->
            <div class="lg:pl-6">
              <SafetyMetricsCard :safetyData="summaries.safety || {}" />
            </div>

          </div>

        </div>
      </div>

      <!-- Miles Driven -->
      <div class="bg-card rounded-xl border shadow-sm px-4 py-4 sm:py-5 transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-muted-foreground">Miles Driven</p>
            <p class="text-2xl sm:text-3xl font-bold">
              {{ formatNumber(milesDriven) }}
            </p>
          </div>

          <div class="text-indigo-500 text-sm font-medium">
            Total Distance
          </div>
        </div>
      </div>

    </div>

    <!-- Dialog (unchanged UI, slightly spaced) -->
    <Dialog v-model:open="showCustomDialog">
      <DialogContent class="space-y-4">
        <DialogHeader>
          <DialogTitle>Select Custom Date Range</DialogTitle>
          <DialogDescription>
            Choose a start and end date for your report.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4">
          <div>
            <Label>Start Date</Label>
            <Input type="date" v-model="customStartDate" />
          </div>

          <div>
            <Label>End Date</Label>
            <Input type="date" v-model="customEndDate" />
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showCustomDialog = false">
            Cancel
          </Button>
          <Button @click="applyCustomRange">
            Apply
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Icon from '@/components/Icon.vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';


// Import new dashboard components
import DashboardHeader from '@/components/summary/DashboardHeader.vue';
import TimePeriodTabs from '@/components/summary/TimePeriodTabs.vue';
import PerformanceCards from '@/components/summary/PerformanceCards.vue';
import BaseMetricsCard from '@/components/summary/BaseMetricsCard.vue';
import SafetyMetricsCard from '@/components/summary/SafetyMetricsCard.vue';

// Props
const props = defineProps({
  tenantSlug: String,
  summaries: Object,
  delayBreakdowns: Object,
  rejectionBreakdowns: Object,
  maintenanceBreakdowns: Object,
  dateFilter: String,
  dateRange: Object,
  milesDriven: Number,
  permissions: Array,
});

// Active tab state
const currentDateFilter = ref(props.dateFilter || 't6w');

// Outstanding invoices filter state
const minInvoiceAmount = ref(null);
const outstandingDate = ref(null);
const customStartDate = ref<string | null>(null);
const customEndDate = ref<string | null>(null);
const showCustomDialog = ref(false);

const applyCustomRange = () => {
  if (!customStartDate.value || !customEndDate.value) return;

  currentDateFilter.value = 'custom';
  showCustomDialog.value = false;

  router.visit(route('dashboard', {
    tenantSlug: props.tenantSlug,
    dateFilter: 'custom',
    startDate: customStartDate.value,
    endDate: customEndDate.value,
    minInvoiceAmount: minInvoiceAmount.value || null,
    outstandingDate: outstandingDate.value || null
  }), {
    only: ['summaries', 'delayBreakdowns', 'rejectionBreakdowns', 'maintenanceBreakdowns', 'dateFilter', 'dateRange', 'milesDriven']
  });
};

// Compute the current date range text based on the selected filter
const currentDateRangeText = computed(() => {
  if (currentDateFilter.value === 'custom') {
    if (customStartDate.value && customEndDate.value) {
      return `${customStartDate.value} - ${customEndDate.value}`;
    }
  }

  const filterMap = {
    'yesterday': 'yesterday',
    'current-week': 'current_week',
    't6w': 'rolling_6_weeks',
    'quarterly': 'quarterly'
  };

  const mappedFilter = filterMap[currentDateFilter.value] || currentDateFilter.value;
  return getDateRangeDisplay(mappedFilter);
});


// Handle time period tab change
const handleTimePeriodChange = (tabId: string) => {
  if (tabId === 'custom') {
    showCustomDialog.value = true;
    return;
  }

  currentDateFilter.value = tabId;

  router.visit(route('dashboard', {
    tenantSlug: props.tenantSlug,
    dateFilter: tabId,
    minInvoiceAmount: minInvoiceAmount.value || null,
    outstandingDate: outstandingDate.value || null
  }), {
    only: ['summaries', 'delayBreakdowns', 'rejectionBreakdowns', 'maintenanceBreakdowns', 'dateFilter', 'dateRange', 'milesDriven']
  });
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
    href: route('dashboard', { tenantSlug: props.tenantSlug }),
  },
];

// Compute the formatted last updated date from the summaries data
const formatLastUpdated = computed(() => {
  // Check if we have a last_updated timestamp from the backend
  if (props.summaries?.performance?.last_updated) {
    return formatDate(new Date(props.summaries.performance.last_updated));
  }
  // Fallback to current date if no timestamp is available
  return formatDate(new Date());
});

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

  switch (range) {
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
// Format number with commas for thousands
const formatNumber = (value) => {
  if (value === undefined || value === null) return '0';
  return new Intl.NumberFormat().format(Math.round(value));
};
// Compute operational excellence score based on performance ratings
const operationalExcellenceScore = computed(() => {
  if (!props.summaries?.performance?.ratings) return 'Not Available';

  const ratings = props.summaries.performance.ratings;
  const ratingValues = {
    'fantastic_plus': 5,
    'fantastic': 4,
    'good': 3,
    'fair': 2,
    'poor': 1
  };

  // Get all ratings as an array
  const ratingKeys = [
    'average_acceptance',
    'average_on_time',
    'average_maintenance_variance_to_spend',
    'open_boc',
    'vcr_preventable',
    'vmcr_p'
  ];

  // Find the best rating
  let bestRating = 'fantastic_plus';
  for (const key of ratingKeys) {
    if (ratings[key] && ratingValues[ratings[key]] < ratingValues[bestRating]) {
      bestRating = ratings[key];
    }
  }

  // Map the rating to a display value
  switch (bestRating) {
    case 'fantastic_plus':
      return 'Fantastic +';
    case 'fantastic':
      return 'Fantastic';
    case 'good':
      return 'Good';
    case 'fair':
      return 'Fair';
    case 'poor':
      return 'Poor';
    default:
      return 'Not Available';
  }
});
</script>