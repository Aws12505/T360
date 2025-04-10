<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Left Panel: Top 5 Drivers -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Top 5 Drivers</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <li v-for="(driver, index) in topDrivers" :key="index" class="flex justify-between items-center">
            <span>{{ driver.name }}</span>
            <Badge variant="outline">{{ driver.rank }}</Badge>
          </li>
        </ul>
      </div>
    </div>

    <!-- Middle Panel: Bottom 5 Drivers -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Bottom 5 Drivers</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <li v-for="(driver, index) in bottomDrivers" :key="index" class="flex justify-between items-center">
            <span>{{ driver.name }}</span>
            <Badge variant="outline">{{ driver.rank }}</Badge>
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
          :tooltipTemplate="(item : any) => `${item.label}: ${item.value}%`"
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
import { Badge } from '@/components/ui/badge';
import { DonutChart } from '@/components/ui/chart-donut';

// Sample data for top drivers
const topDrivers = [
  { name: 'Daniel Rice', rank: 1 },
  { name: 'Johnny Rice', rank: 2 },
  { name: 'Michael Johnson', rank: 3 },
  { name: 'Sarah Williams', rank: 4 },
  { name: 'David Brown', rank: 5 }
];

// Sample data for bottom drivers
const bottomDrivers = [
  { name: 'James Wilson', rank: 45 },
  { name: 'Emily Davis', rank: 46 },
  { name: 'Robert Miller', rank: 47 },
  { name: 'Jennifer Garcia', rank: 48 },
  { name: 'Thomas Rodriguez', rank: 49 }
];

// Sample data for the chart
const chartData = [
  { label: 'Speeding Violations', value: 40.3 },
  { label: 'Traffic Light Violation', value: 31.0 },
  { label: 'Following Distance Hard Brake', value: 15.2 },
  { label: 'Driver Distraction', value: 8.5 },
  { label: 'Sign Violations', value: 5.0 }
];

// Color palette for the chart
const chartColors = [
  '#1e40af', // Dark blue
  '#3b82f6', // Medium blue
  '#60a5fa', // Light blue
  '#93c5fd', // Very light blue
  '#bfdbfe'  // Extremely light blue
];
</script>