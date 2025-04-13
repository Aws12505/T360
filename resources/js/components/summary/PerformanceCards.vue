<template>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
    <!-- On-Time Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">On-Time Score</h3>
      <div class="text-sm text-muted-foreground mb-1">Total Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">{{ formatPercentage(performanceData.average_on_time) }}</div>
      
      <div class="text-sm mb-2 flex justify-between">
        <span>Delays by reason</span>
        <span>Total</span>
      </div>
      <div class="space-y-2 text-sm max-h-40 overflow-y-auto">
        <div v-for="(delay, index) in topDelays" :key="index" class="flex justify-between">
          <span>{{ delay.code }}</span>
          <span>{{ delay.total_delays }}</span>
        </div>
      </div>
      <div class="mt-3">
        <Badge :variant="getRatingVariant(performanceRatings.average_on_time)" class="text-xs">
          {{ formatRating(performanceRatings.average_on_time) }}
        </Badge>
      </div>
    </div>

    <!-- Acceptance Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">Acceptance Score</h3>
      <div class="text-sm text-muted-foreground mb-1">Total Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">{{ formatPercentage(performanceData.average_acceptance) }}</div>
      
      <div class="text-sm mb-2 flex justify-between">
        <span>Rejections by reason</span>
        <span>Total</span>
      </div>
      <div class="space-y-2 text-sm max-h-40 overflow-y-auto">
        <div v-for="(rejection, index) in topRejections" :key="index" class="flex justify-between">
          <span>{{ rejection.reason_code }}</span>
          <span>{{ rejection.total_rejections }}</span>
        </div>
      </div>
      <div class="mt-3">
        <Badge :variant="getRatingVariant(performanceRatings.average_acceptance)" class="text-xs">
          {{ formatRating(performanceRatings.average_acceptance) }}
        </Badge>
      </div>
    </div>

    <!-- Maintenance Metrics Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">Maintenance Metrics</h3>
      <div class="text-sm text-muted-foreground mb-1">MVtS Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">{{ formatPercentage(performanceData.average_maintenance_variance_to_spend) }}</div>
      
      <div class="text-sm mb-2 space-y-2">
        <div class="flex justify-between">
          <span>Cost per mile</span>
          <span>${{ formatCurrency(maintenanceBreakdowns.cpm) }}</span>
        </div>
        <div class="flex justify-between">
          <span>QS Cost per mile</span>
          <span>${{ formatCurrency(maintenanceBreakdowns.qs_cpm) }}</span>
        </div>
        <div class="flex justify-between">
          <span>QS MVtS</span>
          <span>{{ formatDecimal(maintenanceBreakdowns.qs_MVtS) }}</span>
        </div>
      </div>
      <div class="mt-3">
        <Badge :variant="getRatingVariant(performanceRatings.average_maintenance_variance_to_spend)" class="text-xs">
          {{ formatRating(performanceRatings.average_maintenance_variance_to_spend) }}
        </Badge>
      </div>
    </div>

    <!-- Safety Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">Safety Score</h3>
      <div class="text-sm text-muted-foreground mb-1">Green Zone Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">
        {{ formatDecimal(safetyData.average_driver_score || 'N/A') }}
      </div>
      
      <div class="space-y-2 text-sm">
        <div class="flex justify-between items-center">
          <span>Traffic Light Violation</span>
          <div class="flex items-center gap-2">
            <span>{{ safetyData.traffic_light_violation }}</span>
            <span class="text-xs text-muted-foreground">({{ formatDecimal(safetyData.rates?.traffic_light_violation) }}/1000h)</span>
            <Badge :variant="getRatingVariant(safetyData.ratings?.traffic_light_violation)" class="text-xs">
              {{ formatRating(safetyData.ratings?.traffic_light_violation) }}
            </Badge>
          </div>
        </div>
        <div class="flex justify-between items-center">
          <span>Speeding Violations</span>
          <div class="flex items-center gap-2">
            <span>{{ safetyData.speeding_violations }}</span>
            <span class="text-xs text-muted-foreground">({{ formatDecimal(safetyData.rates?.speeding_violations) }}/1000h)</span>
            <Badge :variant="getRatingVariant(safetyData.ratings?.speeding_violations)" class="text-xs">
              {{ formatRating(safetyData.ratings?.speeding_violations) }}
            </Badge>
          </div>
        </div>
        <div class="flex justify-between items-center">
          <span>Following Distance</span>
          <div class="flex items-center gap-2">
            <span>{{ safetyData.following_distance_hard_brake }}</span>
            <span class="text-xs text-muted-foreground">({{ formatDecimal(safetyData.rates?.following_distance_hard_brake) }}/1000h)</span>
            <Badge :variant="getRatingVariant(safetyData.ratings?.following_distance)" class="text-xs">
              {{ formatRating(safetyData.ratings?.following_distance) }}
            </Badge>
          </div>
        </div>
        <div class="flex justify-between items-center">
          <span>Driver Distraction</span>
          <div class="flex items-center gap-2">
            <span>{{ safetyData.driver_distraction }}</span>
            <span class="text-xs text-muted-foreground">({{ formatDecimal(safetyData.rates?.driver_distraction) }}/1000h)</span>
            <Badge :variant="getRatingVariant(safetyData.ratings?.driver_distraction)" class="text-xs">
              {{ formatRating(safetyData.ratings?.driver_distraction) }}
            </Badge>
          </div>
        </div>
        <div class="flex justify-between items-center">
          <span>Sign Violations</span>
          <div class="flex items-center gap-2">
            <span>{{ safetyData.sign_violations }}</span>
            <span class="text-xs text-muted-foreground">({{ formatDecimal(safetyData.rates?.sign_violations) }}/1000h)</span>
            <Badge :variant="getRatingVariant(safetyData.ratings?.sign_violations)" class="text-xs">
              {{ formatRating(safetyData.ratings?.sign_violations) }}
            </Badge>
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
  if (value === undefined || value === null) return '0.00%';
  // Don't multiply by 100 since values are already percentages
  return `${parseFloat(value).toFixed(2)}%`;
};

// Format decimal for display
const formatDecimal = (value) => {
  
  // Convert string values to numbers
  if (typeof value === 'string') {
    value = parseFloat(value);
  }
  
  if (value === undefined || value === null || isNaN(value)) return '0.00';
  return value.toFixed(2);
};

// Format currency for display
const formatCurrency = (value) => {
  if (value === undefined || value === null || typeof value !== 'number') return '0.00';
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
    default:
      return rating;
  }
};

// Get badge variant based on rating
const getRatingVariant = (rating) => {
  if (!rating) return 'outline';
  
  switch (rating) {
    case 'gold':
      return 'success';
    case 'silver':
      return 'secondary';
    case 'not_eligible':
      return 'destructive';
    default:
      return 'outline';
  }
};
</script>