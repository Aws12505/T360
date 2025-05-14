<template>
  <div class="bg-background rounded-lg border shadow-sm p-4">
    <h3 class="text-base font-semibold mb-4">{{ title }}</h3>
    
    <!-- Display message when no rejections -->
    <div v-if="totalRejections === '0'" class="text-center text-lg py-4 text-violet-400 font-medium">
      {{ noRejectionsMessage }}
    </div>
     
    <!-- Display drivers list when there are rejections -->
    <template v-else>
      <!-- Column headers -->
      <div class="flex justify-between items-center mb-2">
        <span class="text-base mb-2 font-medium">Driver Name</span>
        <span class="text-base mb-2 font-medium">Total Penalty</span>
      </div>
      <div class="space-y-3">
        <div v-for="(driver, index) in drivers" :key="index" class="flex justify-between items-center">
          <span class="text-sm">{{ driver.driver_name }}</span>
          <span class="text-sm font-medium" :style="{ color: getColor(driver.total_penalty) }">{{ driver.total_penalty }}</span>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Bottom 5 Drivers'
  },
  drivers: {
    type: Array,
    default: () => []
  },
  totalRejections: {
    type: Number,
    default: 0
  },
  rejectionType: {
    type: String,
    default: ''
  }
});

// Computed property for the no rejections message based on rejectionType
const noRejectionsMessage = computed(() => {
  if (props.rejectionType === 'load') {
    return "No load rejections recorded. You're on track for Fantastic+ !";
  } else if (props.rejectionType === 'block') {
    return "No block rejections recorded. You're on track for Fantastic+ !";
  } else {
    return "No rejections recorded. You're on track for Fantastic+ !";
  }
});

// Function to get color based on total_penalty value
const getColor = (penalty) => {
  const colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981'];
  const sortedPenalties = [...props.drivers]
    .sort((a, b) => b.total_penalty - a.total_penalty)
    .map(driver => driver.total_penalty);
  
  // Find the rank of the current penalty (accounting for ties)
  const rank = sortedPenalties.indexOf(penalty);
  
  return colors[rank] || '#6b7280';
};
</script>