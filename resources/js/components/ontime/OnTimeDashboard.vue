<template>
    <div class="space-y-2 md:space-y-4 lg:space-y-6">
        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 gap-2 md:gap-4 lg:gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <TotalLateStops v-for="(metric, index) in metrics" :key="index" :title="metric.title" :value="metric.value" />
        </div>

        <!-- Bottom Section -->
        <div class="grid grid-cols-1 gap-2 md:gap-4 lg:gap-6 lg:grid-cols-4">
            <BottomDrivers
                :title="'Bottom 5 Drivers'"
                :drivers="bottomDrivers"
                :delayType="delayType"
                :totalDelays="props.metricsData.totalDelays"
                :class="{'lg:col-span-1': hasEnoughChartData, 'lg:col-span-4': !hasEnoughChartData}"
            />
            <LineChart 
                v-if="props.currentDateFilter!='Yesterday' && hasEnoughChartData" 
                :title="'On-Time Score'" 
                :chartData="lineChartData" 
                :averageOntime="averageOntime" 
                class="lg:col-span-3" 
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import BottomDrivers from './BottomDrivers.vue';
import LineChart from './LineChart.vue';
import TotalLateStops from './TotalLateStops.vue';

// Props to receive data from parent component
const props = defineProps({
    metricsData: {
        type: Object,
        default: () => ({}),
    },
    driversData: {
        type: Array,
        default: () => [],
    },
    chartData: {
        type: Object,
        default: () => ({}),
    },
    averageOntime: {
        type: Number,
        default: null,
    },
    currentDateFilter: {
        type: String,
        default: null
    },
    delayType: {
        type: String,
        default: 'all',
    },
});

// Computed properties to use either provided data or default data
const metrics = computed(() => {
    if (props.metricsData && props.metricsData.by_category) {
        // Transform the categories data into the format expected by TotalLateStops component
        return [
            { title: 'Total Delayed Stops', value: props.metricsData.totalDelays },
            { title: 'Delayed for 1-120 Minutes', value: props.metricsData.between1_120Count },
            { title: 'Delayed for 121-600 Minutes', value: props.metricsData.between121_600Count },
            { title: 'Delayed for +601 Minutes', value: props.metricsData.moreThan601Count },
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

// Check if there's enough data for the chart (more than 1 data point)
const hasEnoughChartData = computed(() => {
    if (!props.chartData || !props.chartData.labels) {
        return false;
    }
    return props.chartData.labels.length > 1;
});
</script>
