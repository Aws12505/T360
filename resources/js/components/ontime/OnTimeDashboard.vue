<template>
  <div class="space-y-6">
    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <TotalLateStops 
        v-for="(metric, index) in metrics" 
        :key="index" 
        :title="metric.title" 
        :value="metric.value" 
      />
    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <BottomDrivers :title="'Bottom 5 Drivers'" :drivers="bottomDrivers" class="lg:col-span-1" />
      <LineChart :title="'On-Time Score'" :chart-data="lineChartData" class="lg:col-span-3" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import TotalLateStops from './TotalLateStops.vue';
import BottomDrivers from './BottomDrivers.vue';
import LineChart from './LineChart.vue';

// Props to receive data from parent component
const props = defineProps({
  metricsData: {
    type: Object,
    default: () => ({})
  },
  driversData: {
    type: Array,
    default: () => []
  },
  chartData: {
    type: Object,
    default: () => ({})
  }
});




</script>