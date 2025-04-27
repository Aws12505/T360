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
      <DashboardHeader :operationalExcellenceScore="operationalExcellenceScore" />
      
      <!-- Alert for Canceled QS Invoices -->
      <div v-if="hasCanceledQSInvoices" class="mb-6">
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 p-4 rounded-md shadow-sm">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-800 flex items-center justify-center">
                <Icon name="triangleAlert" class="h-6 w-6 text-red-600 dark:text-red-300" />
              </div>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-red-800 dark:text-red-300">Attention Required</h3>
              <div class="mt-1 text-sm text-red-700 dark:text-red-200">
                {{ maintenanceBreakdowns?.canceled_qs_invoices?.length || 0 }} invoices found with canceled WO status but still on QS. These need immediate attention.
              </div>
              <div class="mt-2">
                <Button @click="showCanceledQSInvoicesDialog = true" variant="destructive" size="sm">
                  View Details
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Time Period Tabs -->
      <TimePeriodTabs 
        @tab-change="handleTimePeriodChange" 
        :dateRangeText="currentDateRangeText"
        :weekNumber="dateRange?.weekNumber"
        :startWeekNumber="dateRange?.startWeekNumber"
        :endWeekNumber="dateRange?.endWeekNumber"
        :year="dateRange?.year"
        :activeTabId="currentDateFilter"
      />
      
      <!-- Performance Cards -->
      <PerformanceCards 
        v-if="summaries"
        :performanceData="summaries.performance?.data || {}"
        :performanceRatings="summaries.performance?.ratings || {}"
        :safetyData="summaries.safety || {}"
        :delayBreakdowns="delayBreakdowns?.by_code || []"
        :rejectionBreakdowns="rejectionBreakdowns?.by_reason || []"
        :maintenanceBreakdowns="maintenanceBreakdowns || {}"
      />
      
      <!-- Additional Metrics Card -->
      <AdditionalMetricsCard 
        v-if="summaries"
        :performanceData="summaries.performance?.data || {}"
        :performanceRatings="summaries.performance?.ratings || {}"
      />
      
      <!-- Tabs Header -->
      <TabsHeader @tab-change="handleTabChange" />
      
      <!-- Tab Content -->
      <div>
        <OnTimeContent 
          v-if="activeTab === 'on-time'" 
          :delayBreakdownsByDriver="delayBreakdowns?.by_driver || []"
          :delayBreakdownsByCode="delayBreakdowns?.by_code || []"
          :tenantSlug="tenantSlug"
        />
        <AcceptanceContent 
          v-if="activeTab === 'acceptance'" 
          :rejectionBreakdownsByDriver="rejectionBreakdowns?.by_driver || []"
          :rejectionBreakdownsByReason="rejectionBreakdowns?.by_reason || []"
          :tenantSlug="tenantSlug"
        />
        <SafetyContent 
          v-if="activeTab === 'safety'" 
          :safetyData="summaries?.safety || {}"
          :tenantSlug="tenantSlug"
        />
        <MaintenanceContent 
          v-if="activeTab === 'maintenance'" 
          :maintenanceData="maintenanceBreakdowns || {}"
          :tenantSlug="tenantSlug"
          :showOutstandingInvoicesSection="showOutstandingInvoicesSection"
          :initialMinInvoiceAmount="minInvoiceAmount"
          :initialOutstandingDate="outstandingDate"
          @filter-applied="handleOutstandingInvoicesFilter"
        />
      </div>
    </div>
    
    <!-- Dialog for Canceled QS Invoices -->
    <Dialog v-model:open="showCanceledQSInvoicesDialog">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <Icon name="alert-triangle" class="h-5 w-5 text-red-600" />
            Canceled Invoices on QS
          </DialogTitle>
          <DialogDescription>
            These invoices have a canceled WO status but are still marked as on QS. Please review and take appropriate action.
          </DialogDescription>
        </DialogHeader>
        
        <div class="max-h-[60vh] overflow-y-auto">
          <Table v-if="maintenanceBreakdowns?.canceled_qs_invoices?.length">
            <TableHeader>
              <TableRow>
                <TableHead>RO Number</TableHead>
                <TableHead>Vendor</TableHead>
                <TableHead class="text-right">Amount</TableHead>
                <TableHead>Week</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="invoice in maintenanceBreakdowns.canceled_qs_invoices" :key="invoice.ro_number" class="hover:bg-red-50 dark:hover:bg-red-900/30">
                <TableCell class="font-medium">
                  <div class="flex items-center gap-2">
                    <Icon name="alert-triangle" class="h-4 w-4 text-red-600 dark:text-red-400" />
                    {{ invoice.ro_number }}
                  </div>
                </TableCell>
                <TableCell>{{ invoice.vendor_name }}</TableCell>
                <TableCell class="text-right">${{ formatCurrency(invoice.invoice_amount) }}</TableCell>
                <TableCell>W{{ invoice.week_number }}/{{ invoice.year }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <div v-else class="text-center py-8 text-muted-foreground">
            No canceled QS invoices found.
          </div>
        </div>
        
        <DialogFooter>
          <Button @click="showCanceledQSInvoicesDialog = false" variant="outline">Close</Button>
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
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table';

// Import tab components
import TabsHeader from '@/components/summary/TabsHeader.vue';
import OnTimeContent from '@/components/summary/OnTimeContent.vue';
import AcceptanceContent from '@/components/summary/AcceptanceContent.vue';
import SafetyContent from '@/components/summary/SafetyContent.vue';
import MaintenanceContent from '@/components/summary/MaintenanceContent.vue';

// Import new dashboard components
import DashboardHeader from '@/components/summary/DashboardHeader.vue';
import TimePeriodTabs from '@/components/summary/TimePeriodTabs.vue';
import PerformanceCards from '@/components/summary/PerformanceCards.vue';
import AdditionalMetricsCard from '@/components/summary/AdditionalMetricsCard.vue';

// Props
const props = defineProps({
  tenantSlug: String,
  summaries: Object,
  delayBreakdowns: Object,
  rejectionBreakdowns: Object,
  maintenanceBreakdowns: Object,
  dateFilter: String,
  dateRange: Object
});

// Active tab state
const activeTab = ref('on-time');
const currentDateFilter = ref(props.dateFilter || 'yesterday');

// Outstanding invoices filter state
const minInvoiceAmount = ref(null);
const outstandingDate = ref(null);
const showOutstandingInvoicesSection = ref(true);

// Canceled QS invoices dialog state
const showCanceledQSInvoicesDialog = ref(false);

// Check if there are any canceled QS invoices that need attention
const hasCanceledQSInvoices = computed(() => {
  return props.maintenanceBreakdowns?.canceled_qs_invoices?.length > 0;
});

// Initialize the outstanding date to null (removing the 30 days ago default)
onMounted(() => {
  // No default date initialization
});

// Format date for input field (YYYY-MM-DD)
const formatDateForInput = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Format currency for display
const formatCurrency = (value) => {
  if (value === undefined || value === null) return '0.00';
  return Number(value).toFixed(2);
};

// Apply outstanding invoices filter (updated to handle event from MaintenanceContent)
const handleOutstandingInvoicesFilter = (filterData) => {
  minInvoiceAmount.value = filterData.minInvoiceAmount;
  outstandingDate.value = filterData.outstandingDate;
  
  router.visit(route('dashboard', {
    tenantSlug: props.tenantSlug,
    dateFilter: currentDateFilter.value,
    minInvoiceAmount: filterData.minInvoiceAmount || null,
    outstandingDate: filterData.outstandingDate || null
  }), {
    preserveState: true,
    preserveScroll: true,
    only: ['maintenanceBreakdowns']
  });
};

// Compute the current date range text based on the selected filter
const currentDateRangeText = computed(() => {
  const filterMap = {
    'yesterday': 'yesterday',
    'current-week': 'current_week',
    't6w': 'rolling_6_weeks',
    'quarterly': 'quarterly'
  };
  
  const mappedFilter = filterMap[currentDateFilter.value] || currentDateFilter.value;
  return getDateRangeDisplay(mappedFilter);
});

// Handle tab change
const handleTabChange = (tabId: string) => {
  activeTab.value = tabId;
};

// Handle time period tab change
const handleTimePeriodChange = (tabId: string) => {
  currentDateFilter.value = tabId;
  
  // Reload the page with the new date filter
  router.visit(route('dashboard', { 
    tenantSlug: props.tenantSlug,
    dateFilter: tabId,
    minInvoiceAmount: minInvoiceAmount.value || null,
    outstandingDate: outstandingDate.value || null
  }), {
    preserveState: true,
    preserveScroll: true,
    only: ['summaries', 'delayBreakdowns', 'rejectionBreakdowns', 'maintenanceBreakdowns', 'dateFilter', 'dateRange']
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