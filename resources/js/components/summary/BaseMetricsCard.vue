<template>
  <div class="h-full flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base sm:text-lg font-semibold">Base Metrics</h3>
      <span class="text-xs text-muted-foreground">T6W</span>
    </div>

    <!-- Metrics -->
    <div class="flex flex-col gap-4 flex-1">

      <!-- VCR -->
      <div
        class="p-4 rounded-lg border bg-background/40 hover:bg-accent hover:border-border hover:shadow-sm transition-all duration-200 flex items-center justify-between flex-1 min-h-[90px]">
        <div class="space-y-1">
          <h4 class="text-base sm:text-lg font-semibold leading-tight">VCR-P</h4>
          <div class="text-sm text-muted-foreground leading-none">Count</div>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-2xl sm:text-3xl font-semibold leading-none"
            :class="getScoreColorClass(performanceRatings.vcr_preventable)">
            {{ formatNumberVCR(performanceData.vcr_preventable) }}
          </div>

          <Badge :variant="getRatingVariant(performanceRatings.vcr_preventable)" class="text-xs self-center">
            {{ formatRating(performanceRatings.vcr_preventable) }}
          </Badge>
        </div>
      </div>

      <!-- VMCR -->
      <div
        class="p-4 rounded-lg border bg-background/40 hover:bg-accent hover:border-border hover:shadow-sm transition-all duration-200 flex items-center justify-between flex-1 min-h-[90px]">
        <div class="space-y-1">
          <h4 class="text-base sm:text-lg font-semibold leading-tight">VMCR-P</h4>
          <div class="text-sm text-muted-foreground leading-none">Count</div>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-2xl sm:text-3xl font-semibold leading-none"
            :class="getScoreColorClass(performanceRatings.vmcr_p)">
            {{ formatNumber(performanceData.vmcr_p) }}
          </div>

          <Badge :variant="getRatingVariant(performanceRatings.vmcr_p)" class="text-xs self-center">
            {{ formatRating(performanceRatings.vmcr_p) }}
          </Badge>
        </div>
      </div>

      <!-- BOC -->
      <div
        class="p-4 rounded-lg border bg-background/40 hover:bg-accent hover:border-border hover:shadow-sm transition-all duration-200 flex items-center justify-between flex-1 min-h-[90px]">
        <div class="space-y-1">
          <h4 class="text-base sm:text-lg font-semibold leading-tight">Open BOC</h4>
          <div class="text-sm text-muted-foreground leading-none">Count</div>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-2xl sm:text-3xl font-semibold leading-none"
            :class="getScoreColorClass(performanceRatings.open_boc)">
            {{ formatNumber(performanceData.open_boc) }}
          </div>

          <Badge :variant="getRatingVariant(performanceRatings.open_boc)" class="text-xs self-center">
            {{ formatRating(performanceRatings.open_boc) }}
          </Badge>
        </div>
      </div>

    </div>

  </div>
</template>
<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

const props = defineProps({
  performanceData: {
    type: Object,
    default: () => ({})
  },
  performanceRatings: {
    type: Object,
    default: () => ({})
  }
});

// Format number for display
const formatNumber = (value) => {
  if (value === undefined || value === null) return '0';
  return Math.round(parseFloat(value)).toString();
};

const formatNumberVCR = (value: string | number | null | undefined): string => {
  if (value === undefined || value === null) {
    return '0'
  }

  const num = parseFloat(value as any)
  if (isNaN(num)) {
    return '0'
  }

  // Round to exactly 2 decimal places, then drop any trailing ".0", ".00", or "x0"
  return num
    .toFixed(2)        // e.g. "3.00", "3.40", "3.46"
    .replace(/\.?0+$/, '')  // strip off ".00" → "", ".40" → ".4", leave "46"
}
// Format rating for display
const formatRating = (rating) => {
  if (!rating) return 'Not Available';

  switch (rating) {
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
      return rating.charAt(0).toUpperCase() + rating.slice(1).replace(/_/g, ' ');
  }
};

// Get badge variant based on rating
const getRatingVariant = (rating) => {
  if (!rating) return 'outline';

  switch (rating) {
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