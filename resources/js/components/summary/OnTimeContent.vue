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
              <TableHead>OTO</TableHead>
              <TableHead>OTD</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(driver, index) in drivers" :key="index">
              <TableCell>{{ driver.name }}</TableCell>
              <TableCell>{{ driver.oto }}</TableCell>
              <TableCell>{{ driver.otd }}</TableCell>
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
        <h3 class="text-lg font-semibold">Delays By Reason</h3>
      </div>
      <div class="p-4 flex flex-col items-center">
        <DonutChart
          category="value"
          :data="chartData" 
          :colors="chartColors" 
          index="label"
          :showTooltip="true"
          :tooltipTemplate="(item : any) => `${item.label}: ${item.value}%`"
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
import { 
  Table, TableHeader, TableBody, TableHead, TableRow, TableCell 
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { DonutChart } from '@/components/ui/chart-donut';

// Sample data for the table
const drivers = [
  { name: 'John Doe', oto: '95%', otd: '92%' },
  { name: 'Jane Smith', oto: '93%', otd: '90%' },
  { name: 'Mike Johnson', oto: '97%', otd: '95%' },
  { name: 'Sarah Williams', oto: '91%', otd: '89%' },
  { name: 'David Brown', oto: '94%', otd: '93%' }
];

// Sample data for the chart
const chartData = [
  { label: 'Mechanical tractors', value: 90 },
  { label: 'Driver arriving late', value: 6 },
  { label: 'Medical', value: 2 },
  { label: 'HOS', value: 2 }
];

// Blue color palette for the chart
const chartColors = [
  '#1e40af', // Dark blue
  '#3b82f6', // Medium blue
  '#60a5fa', // Light blue
  '#93c5fd'  // Very light blue
];
</script>