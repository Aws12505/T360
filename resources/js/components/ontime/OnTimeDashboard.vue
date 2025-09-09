<template>
  <div class="space-y-2 md:space-y-4 lg:space-y-6 mb-2 md:mb-4 lg:mb-6">
    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-4">
      <TotalLateStops 
        v-for="(metric, index) in metrics" 
        :key="index" 
        :title="metric.title" 
        :value="metric.value" 
        class="transition-all duration-300 hover:shadow-md"
      />
    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-2 md:gap-4 lg:gap-6">
      <BottomDrivers 
        :title="'Bottom 5 Drivers'" 
        :drivers="bottomDrivers" 
        :delayType="delayType"
        :totalDelays="totalDelays"
        :class="{
          'lg:col-span-1': hasEnoughChartData, 
          'lg:col-span-1 md:col-span-1': !hasEnoughChartData,
          'transition-all duration-300 hover:shadow-md': true
        }" 
      />
      
      <!-- Line Chart (shown when there's enough data) -->
      <LineChart 
        v-if="props.currentDateFilter!='Yesterday' && hasEnoughChartData" 
        :title="'On-Time Score'" 
        :chartData="lineChartData" 
        :averageOntime="averageOntime" 
        class="lg:col-span-3 md:col-span-2 transition-all duration-300 hover:shadow-md" 
      />
      
      <!-- Score Card (shown when there's not enough data but at least one data point) -->
      <div 
        v-if="!hasEnoughChartData && hasSomeData" 
        class="bg-background rounded-lg border shadow-sm p-4 md:p-6 lg:col-span-3 md:col-span-2 transition-all duration-300 hover:shadow-md flex flex-col justify-center"
      >
        <h3 class="text-base md:text-lg text-center font-semibold mb-4">On-Time Score</h3>
        <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-around gap-3 mb-4">
          <div class="text-4xl md:text-5xl font-bold text-center" :class="getScoreColorClass(ontimeRating)">
            {{ formatPercentage(yesterdayScore) }}
          </div>
          <Badge :variant="getRatingVariant(ontimeRating)" class="text-xs md:text-sm px-3 py-1">
            {{ formatRating(ontimeRating) }}
          </Badge>
        </div>
        <p class="text-xs text-muted-foreground text-center mt-2">
          {{ getScoreDescription(ontimeRating) }}
        </p>
      </div>
      
      <!-- No Data Card (shown when there's absolutely no data) -->
      <div 
        v-if="!hasSomeData" 
        class="bg-background rounded-lg border shadow-sm p-4 md:p-6 lg:col-span-3 md:col-span-2 transition-all duration-300 hover:shadow-md flex flex-col justify-center items-center"
      >
        <h3 class="text-base md:text-lg text-center font-semibold mb-4">On-Time Score</h3>
        <div class="flex flex-col items-center justify-center gap-3 mb-4">
          <Icon name="chart_bar" class="h-16 w-16 text-muted-foreground/50" />
          <div class="text-lg font-medium text-center text-primary">
            No data available
          </div>
          <p class="text-sm text-primary/70 text-center max-w-md">
            There is no on-time data available for the selected time period. Try selecting a different date range or check back later.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import TotalLateStops from './TotalLateStops.vue';
import BottomDrivers from './BottomDrivers.vue';
import LineChart from './LineChart.vue';
import { Badge } from '@/components/ui/badge';
import Icon from '@/components/Icon.vue';

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
            { title: 'Delayed for 1-60 Minutes', value: props.metricsData.between1_60Count },
            { title: 'Delayed for 61-240 Minutes', value: props.metricsData.between61_240Count },
            { title: 'Delayed for 241-600 Minutes', value: props.metricsData.between241_600Count },
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

// Add a computed property for totalDelays
const totalDelays = computed(() => {
    return props.metricsData?.totalDelays || 0;
});

// Check if there's enough data for the chart (more than 1 data point)
const hasEnoughChartData = computed(() => {
    if (!props.chartData || !props.chartData.labels) {
        return false;
    }
    return props.chartData.labels.length > 1;
});

// Check if there's at least some data (at least 1 data point)
const hasSomeData = computed(() => {
    return props.chartData?.datasets?.[0]?.data?.length > 0;
});

// Get yesterday's score from the chart data
const yesterdayScore = computed(() => {
    if (hasSomeData.value) {
        return props.chartData.datasets[0].data[0];
    }
    return 0;
});

// Determine the rating based on the score
const ontimeRating = computed(() => {
    const score = yesterdayScore.value;
    
    if (score >= 98) return 'fantastic_plus';
    if (score >= 95) return 'fantastic';
    if (score >= 90) return 'good';
    if (score >= 85) return 'fair';
    return 'poor';
});

// Format percentage for display
const formatPercentage = (value) => {
    if (value === undefined || value === null) return '0%';
    return `${Math.round(parseFloat(value))}%`;
};

// Format rating for display
const formatRating = (rating) => {
    if (!rating) return 'Not Available';

    switch (rating) {
        case 'fantastic_plus':
            return 'Fantastic +';
        case 'fantastic':
            return 'Fantastic';
        case 'good':
            return 'Good';
        case 'fair':
            return 'Fair';
        case 'poor':
            return 'Poor';
        default:
            return rating;
    }
};

// Get badge variant based on rating
const getRatingVariant = (rating) => {
    if (!rating) return 'outline';

    switch (rating) {
        case 'fantastic_plus':
            return 'fantastic-plus';
        case 'fantastic':
            return 'fantastic';
        case 'good':
            return 'good';
        case 'fair':
            return 'fair';
        case 'poor':
            return 'poor';
        default:
            return 'outline';
    }
};

// Get score color class based on rating
const getScoreColorClass = (rating) => {
    if (!rating) return 'text-gray-600';

    switch (rating) {
        case 'fantastic_plus':
            return 'text-green-600';
        case 'fantastic':
            return 'text-emerald-600';
        case 'good':
            return 'text-blue-600';
        case 'fair':
            return 'text-amber-600';
        case 'poor':
            return 'text-red-600';
        default:
            return 'text-indigo-600';
    }
};

// Get description based on rating
const getScoreDescription = (rating) => {
    if (!rating) return '';

    switch (rating) {
        case 'fantastic_plus':
            return 'Outstanding performance with exceptional on-time rate';
        case 'fantastic':
            return 'Excellent performance with very high on-time rate';
        case 'good':
            return 'Solid performance with good on-time rate';
        case 'fair':
            return 'Acceptable performance with room for improvement';
        case 'poor':
            return 'Performance needs significant improvement';
        default:
            return '';
    }
};
</script>
