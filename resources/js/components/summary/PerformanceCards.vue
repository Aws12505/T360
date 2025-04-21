<template>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
    <!-- On-Time Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-base font-semibold mb-1">On-Time</h3>
      <div class="text-xs text-muted-foreground mb-1">Total Score</div>
      <div class="flex items-center justify-between gap-3 mb-4">
        <div class="text-4xl font-bold" :class="getScoreColorClass(performanceRatings.average_on_time)">
          {{ formatPercentage(performanceData.average_on_time) }}
        </div>
        <Badge :variant="getRatingVariant(performanceRatings.average_on_time)" class="text-xs">
          {{ formatRating(performanceRatings.average_on_time) }}
        </Badge>
      </div>

      <div class="text-sm mb-2 flex justify-between">
        <span class="font-semibold">Delays by Reason</span>
        <span class="font-semibold">Total</span>
      </div>
      <div class="space-y-2 text-sm max-h-40 overflow-y-auto">
        <div v-for="(delay, index) in topDelays" :key="index" class="flex justify-between">
          <span>{{ delay.code }}</span>
          <span>{{ delay.total_delays }}</span>
        </div>
      </div>
    </div>

    <!-- Acceptance Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-base font-semibold mb-1">Acceptance</h3>
      <div class="text-xs text-muted-foreground mb-1">Total Score</div>
      <div class="flex items-center justify-between gap-3 mb-4">
        <div class="text-4xl font-bold" :class="getScoreColorClass(performanceRatings.average_acceptance)">
          {{ formatPercentage(performanceData.average_acceptance) }}
        </div>
        <Badge :variant="getRatingVariant(performanceRatings.average_acceptance)" class="text-xs">
          {{ formatRating(performanceRatings.average_acceptance) }}
        </Badge>
      </div>

      <div class="text-sm mb-2 flex justify-between">
        <span class="font-semibold">Rejections by Reason</span>
        <span class="font-semibold">Total</span>
      </div>
      <div class="space-y-2 text-sm max-h-40 overflow-y-auto">
        <div v-for="(rejection, index) in topRejections" :key="index" class="flex justify-between">
          <span>{{ rejection.reason_code }}</span>
          <span>{{ rejection.total_rejections }}</span>
        </div>
      </div>
    </div>

    <!-- Maintenance Metrics Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-base font-semibold mb-1">Maintenance</h3>
      <div class="text-xs text-muted-foreground mb-1">MVtS Score</div>
      <div class="flex items-center justify-between gap-3 mb-4">
        <div class="text-4xl font-bold"
          :class="getScoreColorClass(performanceRatings.average_maintenance_variance_to_spend)">
          {{ formatPercentage(performanceData.average_maintenance_variance_to_spend) }}
        </div>
        <Badge :variant="getRatingVariant(performanceRatings.average_maintenance_variance_to_spend)" class="text-xs">
          {{ formatRating(performanceRatings.average_maintenance_variance_to_spend) }}
        </Badge>
      </div>

      <div class="text-sm mb-2 space-y-2">
        <div class="flex justify-between">
          <span>Cost per mile</span>
          <span>${{ formatCurrency(maintenanceBreakdowns.cpm) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Num of WOs</span>
          <span>{{ maintenanceBreakdowns.total_repair_orders }}</span>
        </div>
        <div class="flex justify-between">
          <span>Current Costs</span>
          <span>${{ formatCurrency(maintenanceBreakdowns.total_invoice_amount) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Missing Invoices</span>
          <span>{{ maintenanceBreakdowns.missing_invoices_count || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- Safety Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-base font-semibold mb-1">Netradyne Alerts Bonus Criteria</h3>
      <div class="text-xs text-muted-foreground mb-1">Green Zone Score</div>
      <div class="flex items-center justify-between gap-3 mb-4">
        <div class="text-4xl font-bold text-indigo-600">
          {{ formatDecimal(safetyData.average_driver_score || 'N/A') }}
        </div>
        <Badge :variant="getSafetyBadgeVariant(overallSafetyRating)" class="text-xs">
          {{ formatRating(overallSafetyRating) }}
        </Badge>
      </div>

      <!-- Column headers -->
      <div class="flex justify-between items-center text-sm font-semibold mb-2">
        <span>Alert Type</span>
        <div class="flex items-center gap-2">
          <span class="w-12 text-right">Total</span>
          <span class="w-16 text-right">P1KH</span>
        </div>
      </div>

      <div class="space-y-2 text-sm">
        <!-- Distracted Driving (formerly Driver Distraction) -->
        <div class="flex justify-between items-center">
          <span>Distracted Driving</span>
          <div class="flex items-center gap-2">
            <span class="w-12 text-right">{{ formatDecimal(safetyData.driver_distraction) }}</span>
            <span class="w-16 text-right">{{ formatDecimal(safetyData.rates?.driver_distraction) }}</span>
          </div>
        </div>

        <!-- Speeding (formerly Speeding Violations) -->
        <div class="flex justify-between items-center">
          <span>Speeding</span>
          <div class="flex items-center gap-2">
            <span class="w-12 text-right">{{ formatDecimal(safetyData.speeding_violations) }}</span>
            <span class="w-16 text-right">{{ formatDecimal(safetyData.rates?.speeding_violations) }}</span>
          </div>
        </div>

        <!-- Sign Violation (formerly Sign Violations) -->
        <div class="flex justify-between items-center">
          <span>Sign Violation</span>
          <div class="flex items-center gap-2">
            <span class="w-12 text-right">{{ formatDecimal(safetyData.sign_violations) }}</span>
            <span class="w-16 text-right">{{ formatDecimal(safetyData.rates?.sign_violations) }}</span>
          </div>
        </div>

        <!-- Traffic Light Violation (unchanged name) -->
        <div class="flex justify-between items-center">
          <span>Traffic Light Violation</span>
          <div class="flex items-center gap-2">
            <span class="w-12 text-right">{{ formatDecimal(safetyData.traffic_light_violation) }}</span>
            <span class="w-16 text-right">{{ formatDecimal(safetyData.rates?.traffic_light_violation) }}</span>
          </div>
        </div>

        <!-- Following Distance (unchanged name) -->
        <div class="flex justify-between items-center">
          <span>Following Distance</span>
          <div class="flex items-center gap-2">
            <span class="w-12 text-right">{{ formatDecimal(safetyData.following_distance) }}</span>
            <span class="w-16 text-right">{{ formatDecimal(safetyData.rates?.following_distance) }}</span>
          </div>
        </div>
      </div>

      <div class="text-sm text-muted-foreground mt-3">
        <div>Total Hours Analyzed: {{ formatDecimal(safetyData.total_hours || 0) }}</div>
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
    .sort((a, b) => b.total_delays - a.total_delays)
    .slice(0, 3);
});

// Get top 3 rejections by count
const topRejections = computed(() => {
  return [...props.rejectionBreakdowns]
    .sort((a, b) => b.total_rejections - a.total_rejections)
    .slice(0, 3);
});

// Format percentage for display
const formatPercentage = (value) => {
  if (value === undefined || value === null) return '0%';
  // Don't multiply by 100 since values are already percentages
  return `${Math.round(parseFloat(value))}%`;
};

// Format decimal for display
const formatDecimal = (value) => {

  // Convert string values to numbers
  if (typeof value === 'string') {
    value = parseFloat(value);
  }

  if (value === undefined || value === null || isNaN(value)) return '0';
  return Math.round(value).toString();
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
      return 'Fantastic+';
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

// Get custom badge class for safety ratings
const getSafetyBadgeClass = (rating) => {
  if (!rating) return 'bg-transparent border border-gray-300 text-gray-500';

  switch (rating) {
    case 'gold':
      return 'bg-transparent border border-amber-400 text-amber-600';
    case 'silver':
      return 'bg-transparent border border-gray-400 text-gray-600';
    case 'not_eligible':
      return 'bg-transparent border border-red-400 text-red-600';
    default:
      return 'bg-transparent border border-gray-300 text-gray-500';
  }
};

// Get badge variant based on safety rating
const getSafetyBadgeVariant = (rating) => {
  if (!rating) return 'outline';

  if (rating === 'Gold Tier') return 'gold';
  if (rating === 'Silver Tier') return 'silver';
  if (rating === 'Not Eligible') return 'not-eligible';

  return 'outline';
};

// Calculate overall safety rating based on safety metrics
const overallSafetyRating = computed(() => {
  if (!props.safetyData?.ratings) return null;

  const ratings = props.safetyData.ratings;
  const ratingValues = {
    'gold': 3,
    'silver': 2,
    'not_eligible': 1,
  };

  // Get all safety ratings as an array
  const safetyRatingKeys = [
    'traffic_light_violation',
    'speeding_violations',
    'following_distance',
    'driver_distraction',
    'sign_violations'
  ];

  // Find the best rating
  let bestRating = 'gold';
  for (const key of safetyRatingKeys) {
    if (ratings[key] && ratingValues[ratings[key]] < ratingValues[bestRating]) {
      bestRating = ratings[key];
    }
  }

  switch (bestRating) {
    case 'gold':
      return 'Gold Tier';
    case 'silver':
      return 'Silver Tier';
    case 'not_eligible':
      return 'Not Eligible';
    default:
      return 'Not Available';
  }
});

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

// Get safety score color class based on rating
const getSafetyScoreColorClass = (rating) => {
  if (!rating) return 'text-gray-600';

  if (rating === 'Gold Tier') return 'text-amber-600';
  if (rating === 'Silver Tier') return 'text-slate-600';
  if (rating === 'Not Eligible') return 'text-red-600';

  return 'text-indigo-600';
};
</script>
