<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Left Panel: Top 5 Drivers -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Top 5 Drivers</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <li v-for="(driver, index) in safetyData.top_drivers || []" :key="index" class="flex justify-between items-center">
            <span>{{ driver.name }}</span>
            <Badge variant="outline">{{ driver.score }}</Badge>
          </li>
          <li v-if="(safetyData.top_drivers || []).length === 0" class="text-center text-muted-foreground">
            No data available
          </li>
        </ul>
        <div class="mt-4 text-right">
          <Button 
            variant="link" 
            size="sm" 
            class="text-primary"
            @click="navigateToDetails"
          >
            See details...
          </Button>
        </div>
      </div>
    </div>

    <!-- Middle Panel: Bottom 5 Drivers -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Bottom 5 Drivers</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <li v-for="(driver, index) in safetyData.bottom_drivers || []" :key="index" class="flex justify-between items-center">
            <span>{{ driver.name }}</span>
            <Badge variant="outline">{{ driver.score }}</Badge>
          </li>
          <li v-if="(safetyData.bottom_drivers || []).length === 0" class="text-center text-muted-foreground">
            No data available
          </li>
        </ul>
      </div>
    </div>

    <!-- Right Panel: Safety Summary Chart -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Safety Summary</h3>
      </div>
      <div class="p-4 flex flex-col items-center">
        <DonutChart 
          category="value"
          :data="chartData" 
          :colors="chartColors" 
          index="label"
          :showTooltip="true"
          :tooltipTemplate="(item) => `${item.label}: ${item.value}%`"
          class="max-w-[200px]" 
        />
        <div class="mt-6 w-full space-y-2">
          <div v-for="(item, index) in chartData" :key="index" class="flex items-center justify-between">
            <div class="flex items-center">
              <div 
                class="w-3 h-3 rounded-full mr-2" 
                :style="{ backgroundColor: chartColors[index] }"
              ></div>
              <span class="text-sm">{{ item.label }}</span>
            </div>
            <span class="text-sm font-medium">{{ item.value }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DonutChart } from '@/components/ui/chart-donut';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  safetyData: {
    type: Object,
    default: () => ({})
  },
  tenantSlug: {
    type: String,
    required: true
  }
});

// Navigate to safety details page
const navigateToDetails = () => {
  router.visit(route('safety.index', { tenantSlug: props.tenantSlug }));
};

// Generate chart data from safety violations
const chartData = computed(() => {
  const totalViolations = 
    (props.safetyData.traffic_light_violation || 0) +
    (props.safetyData.speeding_violations || 0) +
    (props.safetyData.following_distance_hard_brake || 0) +
    (props.safetyData.driver_distraction || 0) +
    (props.safetyData.sign_violations || 0);
  
  if (totalViolations === 0) {
    return [
      { label: 'No Data', value: 100 }
    ];
  }
  
  return [
    { 
      label: 'Speeding Violations', 
      value: totalViolations > 0 ? Math.round((props.safetyData.speeding_violations || 0) / totalViolations * 100 * 10) / 10 : 0 
    },
    { 
      label: 'Traffic Light Violation', 
      value: totalViolations > 0 ? Math.round((props.safetyData.traffic_light_violation || 0) / totalViolations * 100 * 10) / 10 : 0 
    },
    { 
      label: 'Following Distance', 
      value: totalViolations > 0 ? Math.round((props.safetyData.following_distance_hard_brake || 0) / totalViolations * 100 * 10) / 10 : 0 
    },
    { 
      label: 'Driver Distraction', 
      value: totalViolations > 0 ? Math.round((props.safetyData.driver_distraction || 0) / totalViolations * 100 * 10) / 10 : 0 
    },
    { 
      label: 'Sign Violations', 
      value: totalViolations > 0 ? Math.round((props.safetyData.sign_violations || 0) / totalViolations * 100 * 10) / 10 : 0 
    }
  ].filter(item => item.value > 0);
});

// Color palette for the chart
const chartColors = [
  '#1e40af', // Dark blue
  '#3b82f6', // Medium blue
  '#60a5fa', // Light blue
  '#93c5fd', // Very light blue
  '#bfdbfe'  // Extremely light blue
];
</script>