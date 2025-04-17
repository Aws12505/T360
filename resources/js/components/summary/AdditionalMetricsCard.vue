<template>
  <div class="bg-card rounded-lg border shadow-sm p-6 mb-6">
    <h3 class="text-xl font-semibold mb-4">Additional Performance Metrics</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- VCR Preventable -->
      <div class="p-4 border rounded-lg">
        <h4 class="text-lg font-medium mb-2">VCR-P Preventable</h4>
        <div class="text-sm text-muted-foreground mb-1">Score</div>
        <div class="flex items-center justify-between gap-3 mb-3">
          <div class="text-3xl font-bold" :class="getScoreColorClass(performanceRatings.vcr_preventable)">
            {{ formatNumber(performanceData.vcr_preventable) }}
          </div>
          <Badge :variant="getRatingVariant(performanceRatings.vcr_preventable)" class="text-xs">
            {{ formatRating(performanceRatings.vcr_preventable) }}
          </Badge>
        </div>
      </div>
      
      <!-- Open BOC -->
      <div class="p-4 border rounded-lg">
        <h4 class="text-lg font-medium mb-2">Open BOC</h4>
        <div class="text-sm text-muted-foreground mb-1">Score</div>
        <div class="flex items-center justify-between gap-3 mb-3">
          <div class="text-3xl font-bold" :class="getScoreColorClass(performanceRatings.open_boc)">
            {{ formatNumber(performanceData.open_boc) }}
          </div>
          <Badge :variant="getRatingVariant(performanceRatings.open_boc)" class="text-xs">
            {{ formatRating(performanceRatings.open_boc) }}
          </Badge>
        </div>
      </div>
      
      <!-- VMCR-P -->
      <div class="p-4 border rounded-lg">
        <h4 class="text-lg font-medium mb-2">VMCR-P</h4>
        <div class="text-sm text-muted-foreground mb-1">Score</div>
        <div class="flex items-center justify-between gap-3 mb-3">
          <div class="text-3xl font-bold" :class="getScoreColorClass(performanceRatings.vmcr_p)">
            {{ formatNumber(performanceData.vmcr_p) }}
          </div>
          <Badge :variant="getRatingVariant(performanceRatings.vmcr_p)" class="text-xs">
            {{ formatRating(performanceRatings.vmcr_p) }}
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

// Format rating for display
const formatRating = (rating) => {
  if (!rating) return 'Not Available';
  
  switch (rating) {
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