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
      <LineChart :title="'On-Time Score'" :chartData="lineChartData" :averageOntime="averageOntime" class="lg:col-span-3" />
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
  },
  averageOntime: {
    type: Number,
    default: null
  }
});

// Computed properties to use either provided data or default data
const metrics = computed(() => {
  if (props.metricsData && props.metricsData.by_category) {
    // Transform the categories data into the format expected by TotalLateStops component
    return [
      { title: 'Delays 1-120 Minutes', value: props.metricsData.category_1_120_count || '0' },
      { title: 'Delays 121-600 Minutes', value: props.metricsData.category_121_600_count || '0' },
      { title: 'Delays 601+ Minutes', value: props.metricsData.category_601_plus_count || '0' },
      { title: 'Origin Delays 1-120 Min', value: props.metricsData.category_1_120_origin_count || '0' },
      { title: 'Destination Delays 1-120 Min', value: props.metricsData.category_1_120_destination_count || '0' },
      { title: 'Origin Delays 121-600 Min', value: props.metricsData.category_121_600_origin_count || '0' },
      { title: 'Destination Delays 121-600 Min', value: props.metricsData.category_121_600_destination_count || '0' },
      { title: 'Origin Delays 601+ Min', value: props.metricsData.category_601_plus_origin_count || '0' },
      { title: 'Destination Delays 601+ Min', value: props.metricsData.category_601_plus_destination_count || '0' },
      { title: 'Total Origin Delays', value: props.metricsData.total_origin_delays || '0' },
      { title: 'Total Destination Delays', value: props.metricsData.total_destination_delays || '0' },
      { title: 'Total Origin Penalty', value: props.metricsData.total_origin_penalty || '0' },
      { title: 'Total Destination Penalty', value: props.metricsData.total_destination_penalty || '0' },
      { title: 'Total Delays', value: props.metricsData.total_delays || '0' }
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