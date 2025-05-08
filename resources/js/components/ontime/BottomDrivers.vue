<template>
  <div class="bg-background rounded-lg border shadow-sm p-4">
    <h3 class="text-base font-semibold mb-4">{{ title }}</h3>
    <div class="space-y-3">
      <div v-for="(driver, index) in drivers" :key="index" class="flex justify-between items-center">
        <span class="text-sm">{{ driver.driver_name }}</span>
        <span class="text-sm font-medium" :style="{ color: getColor(index) }">{{ driver.total_penalty }}</span>
      </div>
    </div>
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
  }
});

// Function to get color based on index (position)
const getColor = (index) => {
  const colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981'];
  return colors[index] || '#6b7280';
};
</script>