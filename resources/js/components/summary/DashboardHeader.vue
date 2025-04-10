<template>
  <div class="mb-6">
    <h2 class="text-2xl font-bold" :class="scoreColorClass">{{ operationalExcellenceScore }}</h2>
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