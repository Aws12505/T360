<template>
  <div class="bg-card rounded-lg border shadow-sm p-6 mb-6">
    <h3 class="text-xl font-semibold mb-4">Additional Performance Metrics</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- VCR Preventable -->
      <div class="p-4 border rounded-lg">
        <h4 class="text-lg font-medium mb-2">VCR Preventable</h4>
        <div class="text-sm text-muted-foreground mb-1">Score</div>
        <div class="text-3xl font-bold text-indigo-600 mb-3">
          {{ formatNumber(performanceData.vcr_preventable) }}
        </div>
        <div class="mt-2">
          <Badge :variant="getRatingVariant(performanceRatings.vcr_preventable)" class="text-xs">
            {{ formatRating(performanceRatings.vcr_preventable) }}
          </Badge>
        </div>
      </div>
      
      <!-- Open BOC -->
      <div class="p-4 border rounded-lg">
        <h4 class="text-lg font-medium mb-2">Open BOC</h4>
        <div class="text-sm text-muted-foreground mb-1">Score</div>
        <div class="text-3xl font-bold text-indigo-600 mb-3">
          {{ formatNumber(performanceData.open_boc) }}
        </div>
        <div class="mt-2">
          <Badge :variant="getRatingVariant(performanceRatings.open_boc)" class="text-xs">
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
  return parseFloat(value).toFixed(2);
};

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
    case 'fantastic':
      return 'success';
    case 'good':
      return 'secondary';
    case 'fair':
      return 'warning';
    case 'poor':
      return 'destructive';
    default:
      return 'outline';
  }
};
</script>