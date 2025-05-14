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
      <BottomDrivers 
        :title="'Bottom 5 Drivers'" 
        :drivers="bottomDrivers" 
        :total-rejections="totalRejections" 
        :rejection-type="rejectionType"
        class="lg:col-span-1" 
      />
      <LineChart v-if="props.currentDateFilter!='Yesterday'" :title="'Acceptance Score'" :chartData="lineChartData" :averageAcceptance="averageAcceptance" class="lg:col-span-3" />
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
  averageAcceptance: {
    type: Number,
    default: null
  },
  currentDateFilter: {
    type: String,
    default: null
  },
  currentFilters: {
    type: Object,
    default: () => ({})
  }
});

// Computed properties to use either provided data or default data
const metrics = computed(() => {
  if (props.metricsData && props.metricsData.by_category) {
    // Transform the categories data into the format expected by TotalLateStops component
    return [
      { title: 'Total Rejected Stops', value: props.metricsData.totalRejections },
      { title: 'Rejected +6 Hours Before Start Time', value: props.metricsData.moreThan6Count},
      { title: 'Rejected 0-6 Hours Before Start Time', value: props.metricsData.within6Count},
      { title: 'Rejected After Start Time', value: props.metricsData.afterStartCount},
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

// Add a computed property for totalRejections
const totalRejections = computed(() => {
  return props.metricsData?.totalRejections || 0;
});

// Add a computed property for rejectionType
const rejectionType = computed(() => {
  return props.currentFilters?.rejectionType || null;
});
</script>