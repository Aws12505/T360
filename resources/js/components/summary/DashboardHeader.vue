<template>
  <div class="bg-card border rounded-xl p-5 sm:p-6 flex items-center justify-between transition-all hover:shadow-md">
    <div>
      <p class="text-sm text-muted-foreground mb-1">
        Operational Excellence
      </p>

      <h2 class="text-3xl sm:text-4xl font-bold tracking-tight"
        :class="[scoreColorClass, 'transition-colors duration-300']">
        {{ operationalExcellenceScore }}
      </h2>
    </div>

    <div class="hidden sm:block text-xs text-muted-foreground">
      Overall Rating
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  operationalExcellenceScore: {
    type: String,
    default: 'Not Available'
  }
});

// Compute the color class based on the score
const scoreColorClass = computed(() => {
  switch (props.operationalExcellenceScore) {
    case 'Fantastic +':
      return 'text-green-600';
    case 'Fantastic':
      return 'text-emerald-600';
    case 'Good':
      return 'text-blue-600';
    case 'Fair':
      return 'text-amber-600';
    case 'Poor':
      return 'text-red-600';
    default:
      return 'text-gray-600';
  }
});
</script>