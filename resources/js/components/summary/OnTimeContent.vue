<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Table Section -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Total delays by driver</h3>
      </div>
      <div class="p-4">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Driver Name</TableHead>
              <TableHead>Origin Delays</TableHead>
              <TableHead>Destination Delays</TableHead>
              <TableHead>Total</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(driver, index) in topDrivers" :key="index">
              <TableCell>{{ driver.driver_name || 'Unknown' }}</TableCell>
              <TableCell>{{ driver.total_origin_delays }}</TableCell>
              <TableCell>{{ driver.total_destination_delays }}</TableCell>
              <TableCell>{{ driver.total_delays }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
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

    <!-- Chart Section -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Delays By Reason</h3>
      </div>
      <div class="p-4 flex flex-col items-center">
        <DonutChart
          category="value"
          :data="chartData" 
          :colors="chartColors" 
          index="label"
          :showTooltip="true"
          :tooltipTemplate="(item) => `${item.label}: ${item.value}%`"
          class="max-w-[500px]" 
        />
        <div class="mt-6 w-full grid grid-cols-2 gap-4">
          <div v-for="(item, index) in chartData" :key="index" class="flex items-center">
            <div 
              class="w-3 h-3 rounded-full mr-2" 
              :style="{ backgroundColor: chartColors[index] }"
            ></div>
            <span class="text-sm">{{ item.label }}: {{ item.value }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { 
  Table, TableHeader, TableBody, TableHead, TableRow, TableCell 
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { DonutChart } from '@/components/ui/chart-donut';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  delayBreakdownsByDriver: {
    type: Array,
    default: () => []
  },
  delayBreakdownsByCode: {
    type: Array,
    default: () => []
  },
  tenantSlug: {
    type: String,
    required: true
  }
});

// Get top 5 drivers with highest total delays
const topDrivers = computed(() => {
  return [...props.delayBreakdownsByDriver]
    .sort((a, b) => b.total_delays - a.total_delays)
    .slice(0, 5);
});

// Navigate to on-time details page
const navigateToDetails = () => {
  router.visit(route('ontime.index', { tenantSlug: props.tenantSlug }));
};

// Chart data computation remains unchanged
const chartData = computed(() => {
  // Get top 4 delay codes by total
  const topCodes = [...props.delayBreakdownsByCode]
    .sort((a, b) => b.total_delays - a.total_delays)
    .slice(0, 4);
  
  // Calculate total delays for percentage calculation
  const totalDelays = topCodes.reduce((sum, code) => sum + code.total_delays, 0);
  
  // Convert to chart data format with percentages
  return topCodes.map(code => ({
    label: code.code || 'Unknown',
    value: totalDelays > 0 ? Math.round((code.total_delays / totalDelays) * 100) : 0
  }));
});

// Blue color palette for the chart
const chartColors = [
  '#1e40af', // Dark blue
  '#3b82f6', // Medium blue
  '#60a5fa', // Light blue
  '#93c5fd'  // Very light blue
];
</script>