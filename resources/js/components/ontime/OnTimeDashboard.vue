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

// Dummy data for metrics
const defaultMetrics = [
  { title: 'Total Late Stops', value: 1050 },
  { title: '1-20 Minutes Late', value: 1050 },
  { title: '121 - 600 Minutes Late', value: 1050 },
  { title: '+601 Minutes Late', value: 1050 }
];

// Dummy data for bottom drivers
const defaultBottomDrivers = [
  { name: 'Kain', value: 5 },
  { name: 'Ronny', value: 4 },
  { name: 'Damen', value: 3 },
  { name: 'Leo', value: 2 },
  { name: 'Shawn', value: 1 }
];

// Dummy data for line chart
const defaultLineChartData = {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Late Stops',
      data: [65, 59, 80, 81, 56, 55],
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.3
    }
  ]
};

// Computed properties to use either provided data or default data
const metrics = computed(() => props.metricsData.items || defaultMetrics);
const bottomDrivers = computed(() => props.driversData.length ? props.driversData : defaultBottomDrivers);
const lineChartData = computed(() => Object.keys(props.chartData).length ? props.chartData : defaultLineChartData);
</script>