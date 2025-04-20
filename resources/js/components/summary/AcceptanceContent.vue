<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Table Section -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Total rejections by driver</h3>
      </div>
      <div class="p-4">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Driver Name</TableHead>
              <TableHead>Load rejections</TableHead>
              <TableHead>Block rejections</TableHead>
              <TableHead>Penalty</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(driver, index) in topDrivers" :key="index">
              <TableCell>{{ driver.driver_name || 'Unknown' }}</TableCell>
              <TableCell>{{ driver.total_load_rejections }}</TableCell>
              <TableCell>{{ driver.total_block_rejections }}</TableCell>
              <TableCell>{{ driver.total_penalty }}</TableCell>
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
        <h3 class="text-lg font-semibold">Rejections by Reason</h3>
      </div>
      <div class="p-4 flex flex-col items-center">
        <DonutChart 
          :data="chartData"
          :colors="chartColors" 
          index="label"
          category="value"
          :showTooltip="true"
          :tooltipTemplate="(item) => `${item.label}: ${item.value}%`"
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
  rejectionBreakdownsByDriver: {
    type: Array,
    default: () => []
  },
  rejectionBreakdownsByReason: {
    type: Array,
    default: () => []
  },
  tenantSlug: {
    type: String,
    required: true
  }
});

// Get top 5 drivers with highest total rejections
const topDrivers = computed(() => {
  return [...props.rejectionBreakdownsByDriver]
    .sort((a, b) => b.total_rejections - a.total_rejections)
    .slice(0, 5);
});

// Navigate to acceptance details page
const navigateToDetails = () => {
  router.visit(route('acceptance.index', { tenantSlug: props.tenantSlug }));
};

// Generate chart data from rejection breakdowns by reason
const chartData = computed(() => {
  // Get top 4 rejection reasons by total
  const topReasons = [...props.rejectionBreakdownsByReason]
    .sort((a, b) => b.total_rejections - a.total_rejections)
    .slice(0, 4);
  
  // Calculate total rejections for percentage calculation
  const totalRejections = topReasons.reduce((sum, reason) => sum + reason.total_rejections, 0);
  
  // Convert to chart data format with percentages
  return topReasons.map(reason => ({
    label: reason.reason_code || 'Unknown',
    value: totalRejections > 0 ? Math.round((reason.total_rejections / totalRejections) * 100) : 0
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