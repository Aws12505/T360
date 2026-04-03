<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">

    <!-- Acceptance -->
    <div class="bg-card rounded-xl border shadow-sm p-5 transition-all hover:shadow-md">
      <h3 class="text-base font-semibold mb-1">Acceptance</h3>
      <div class="text-xs text-muted-foreground mb-2">Total Score</div>

      <div class="flex items-center justify-between mb-4">
        <div class="text-3xl sm:text-4xl font-bold leading-none"
          :class="getScoreColorClass(performanceRatings.average_acceptance)">
          {{ formatPercentage(performanceData.average_acceptance, 1) }}
        </div>

        <Badge :variant="getRatingVariant(performanceRatings.average_acceptance)" class="text-xs">
          {{ formatRating(performanceRatings.average_acceptance) }}
        </Badge>
      </div>

      <div class="text-sm mb-2 flex justify-between font-semibold">
        <span>Rejections</span>
        <span>Total</span>
      </div>

      <div class="space-y-2 text-sm max-h-44 overflow-y-auto pr-1">
        <div v-for="(r, i) in topRejections" :key="i" class="flex justify-between">
          <span>{{ formatReasons(r.reason) }}</span>
          <span>{{ r.total_rejections }}</span>
        </div>
      </div>
    </div>

    <!-- On Time -->
    <div class="bg-card rounded-xl border shadow-sm p-5 transition-all hover:shadow-md">
      <h3 class="text-base font-semibold mb-1">On-Time</h3>
      <div class="text-xs text-muted-foreground mb-2">Total Score</div>

      <div class="flex items-center justify-between mb-4">
        <div class="text-3xl sm:text-4xl font-bold leading-none"
          :class="getScoreColorClass(performanceRatings.average_on_time)">
          {{ formatPercentage(performanceData.average_on_time, 1) }}
        </div>

        <Badge :variant="getRatingVariant(performanceRatings.average_on_time)" class="text-xs">
          {{ formatRating(performanceRatings.average_on_time) }}
        </Badge>
      </div>

      <div class="text-sm mb-2 flex justify-between font-semibold">
        <span>Delays</span>
        <span>Total</span>
      </div>

      <div class="space-y-2 text-sm max-h-44 overflow-y-auto pr-1">
        <div v-for="(d, i) in topDelays" :key="i" class="flex justify-between">
          <span>{{ formatReasons(d.reason) }}</span>
          <span>{{ d.total_delays }}</span>
        </div>
      </div>
    </div>

    <!-- Maintenance -->
    <div class="bg-card rounded-xl border shadow-sm p-5 transition-all hover:shadow-md">
      <h3 class="text-base font-semibold mb-1">Maintenance</h3>
      <div class="text-xs text-muted-foreground mb-2">MVtS Score</div>

      <div class="flex items-center justify-between mb-4">
        <div class="text-3xl sm:text-4xl font-bold leading-none"
          :class="getScoreColorClass(performanceRatings.average_maintenance_variance_to_spend)">
          {{ formatPercentage(performanceData.average_maintenance_variance_to_spend, 1) }}
        </div>

        <Badge :variant="getRatingVariant(performanceRatings.average_maintenance_variance_to_spend)" class="text-xs">
          {{ formatRating(performanceRatings.average_maintenance_variance_to_spend) }}
        </Badge>
      </div>

      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span>Cost / Mile</span>
          <span>${{ formatCurrency(maintenanceBreakdowns.qs_cpm) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Work Orders</span>
          <span>{{ maintenanceBreakdowns.total_repair_orders }}</span>
        </div>
        <div class="flex justify-between">
          <span>Total Cost</span>
          <span>${{ formatCurrency(maintenanceBreakdowns.total_invoice_amount) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Missing</span>
          <span>{{ maintenanceBreakdowns.missing_invoices_count || 0 }}</span>
        </div>
      </div>
    </div>

  </div>
</template>
<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
  performanceData: {
    type: Object,
    default: () => ({})
  },
  performanceRatings: {
    type: Object,
    default: () => ({})
  },
  safetyData: {
    type: Object,
    default: () => ({})
  },
  delayBreakdowns: {
    type: Array,
    default: () => []
  },
  rejectionBreakdowns: {
    type: Array,
    default: () => []
  },
  maintenanceBreakdowns: {
    type: Object,
    default: () => ({})
  }
});

// Get top 3 delays by count
const topDelays = computed(() => {
  return [...props.delayBreakdowns]
    .map(d => ({
      reason: d.reason ?? d.code,
      total_delays: d.total_delays ?? 0
    }))
    .sort((a, b) => b.total_delays - a.total_delays)
    .slice(0, 3);
});

const topRejections = computed(() => {
  return [...props.rejectionBreakdowns]
    .map(r => ({
      reason: r.rejection_reason ?? r.reason_code,
      total_rejections: r.total_rejections ?? 0
    }))
    .sort((a, b) => b.total_rejections - a.total_rejections)
    .slice(0, 3);
});

function formatReasons(val) {
  if (!val) return "—";

  return val
    .replace(/_/g, " ")      // replace underscores with spaces
    .toLowerCase()
    .trim()
    .replace(/(^\p{L})|(\s+\p{L})/gu, (m) => m.toUpperCase()); // capitalize words
}
// Format percentage for display
const formatPercentage = (value, decimals = 0) => {
  if (value === undefined || value === null || value === '') return '0%';

  const num = Number(value);
  if (!Number.isFinite(num)) return '0%';

  return `${num.toFixed(decimals)}%`;
};

// Format currency for display
const formatCurrency = (value) => {
  // Convert string values to numbers
  if (typeof value === 'string') {
    value = parseFloat(value);
  }

  if (value === undefined || value === null || isNaN(value)) return '0.00';
  return value.toFixed(2);
};
// Format rating for display
const formatRating = (rating) => {
  if (!rating) return 'Not Available';

  switch (rating) {
    case 'gold':
      return 'Gold Tier';
    case 'silver':
      return 'Silver Tier';
    case 'not_eligible':
      return 'Not Eligible';
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
      return rating;
  }
};
// Get badge variant based on rating
const getRatingVariant = (rating) => {
  if (!rating) return 'outline';

  switch (rating) {
    case 'gold':
      return 'gold';
    case 'silver':
      return 'silver';
    case 'not_eligible':
      return 'not-eligible';
    case 'fantastic_plus':
      return 'fantastic-plus';
    case 'fantastic':
      return 'fantastic';
    case 'good':
      return 'good';
    case 'fair':
      return 'fair';
    case 'poor':
      return 'poor';
    default:
      return 'outline';
  }
};


// Get score color class based on rating
const getScoreColorClass = (rating) => {
  if (!rating) return 'text-gray-600';

  switch (rating) {
    case 'gold':
      return 'text-amber-600';
    case 'silver':
      return 'text-slate-600';
    case 'not_eligible':
      return 'text-red-600';
    case 'fantastic_plus':
      return 'text-green-600';
    case 'fantastic':
      return 'text-emerald-600';
    case 'good':
      return 'text-blue-600';
    case 'fair':
      return 'text-amber-600';
    case 'poor':
      return 'text-red-600';
    default:
      return 'text-indigo-600';
  }
};

</script>