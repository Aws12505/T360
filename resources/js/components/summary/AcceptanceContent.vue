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
            <TableRow v-for="(driver, index) in drivers" :key="index">
              <TableCell>{{ driver.name }}</TableCell>
              <TableCell>{{ driver.loadRejections }}</TableCell>
              <TableCell>{{ driver.blockRejections }}</TableCell>
              <TableCell>{{ driver.penalty }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
        <div class="mt-4 text-right">
          <Button variant="link" size="sm" class="text-primary">
            See details...
          </Button>
        </div>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Rejections By Reason</h3>
      </div>
      <div class="p-4 flex flex-col items-center">
        <DonutChart 
          :data="chartData"
          :colors="chartColors" 
          index="label"
          category="value"
        
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
import { 
  Table, TableHeader, TableBody, TableHead, TableRow, TableCell 
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { DonutChart } from '@/components/ui/chart-donut';

// Sample data for the table
const drivers = [
  { name: 'Adam', loadRejections: '3', blockRejections: '2', penalty: '$150' },
  { name: 'Adam', loadRejections: '2', blockRejections: '1', penalty: '$100' },
  { name: 'Adam', loadRejections: '4', blockRejections: '0', penalty: '$200' },
  { name: 'Adam', loadRejections: '1', blockRejections: '3', penalty: '$175' },
  { name: 'Adam', loadRejections: '2', blockRejections: '2', penalty: '$150' }
];

// Sample data for the chart
const chartData = [
  { label: 'Mechanical tractors', value: 85 },
  { label: 'Driver arriving late', value: 8 },
  { label: 'Medical', value: 4 },
  { label: 'HOS', value: 3 }
];

// Blue color palette for the chart
const chartColors = [
  '#1e40af', // Dark blue
  '#3b82f6', // Medium blue
  '#60a5fa', // Light blue
  '#93c5fd'  // Very light blue
];
</script>