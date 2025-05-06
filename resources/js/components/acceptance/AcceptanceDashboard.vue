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
      <LineChart :title="'Acceptance Score'" :chart-data="lineChartData" class="lg:col-span-3" />
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

// Computed properties to use either provided data or default data
const metrics = computed(() => {
  if (props.metricsData && props.metricsData.by_category) {
    // Transform the categories data into the format expected by TotalLateStops component
    return [
      { title: 'Rejected Loads More Than 6 Hours', value: props.metricsData.category_more_than_6_load_count || '0' },
      { title: 'Rejected Blocks More Than 6 Hours', value: props.metricsData.category_more_than_6_block_count || '0' },
      { title: 'Rejected Loads Within 6 Hours', value: props.metricsData.category_within_6_load_count || '0' },
      { title: 'Rejected Blocks Within 6 Hours', value: props.metricsData.category_within_6_block_count || '0' },
      { title: 'Rejected Loads After Start', value: props.metricsData.category_after_start_load_count || '0' },
      { title: 'Rejected Blocks After Start', value: props.metricsData.category_after_start_block_count || '0' },
      { title: 'Total Load Rejections', value: props.metricsData.total_load_rejections || '0' },
      { title: 'Total Block Rejections', value: props.metricsData.total_block_rejections || '0' },
      { title: 'Total Load Penalty', value: props.metricsData.total_load_penalty || '0' },
      { title: 'Total Block Penalty', value: props.metricsData.total_block_penalty || '0' }
    ];
  }
  return [];
});
const bottomDrivers = computed(() => {
  if (props.driversData && props.driversData.length) {
    return props.driversData;
  }
  return [];
});
const lineChartData = computed(() => {
  if (props.chartData && Object.keys(props.chartData).length) {
    return props.chartData;
  }
  return {};
});
</script>