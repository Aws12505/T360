<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Left Panel: Maintenance Overview -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Maintenance Overview</h3>
      </div>
      <div class="p-4">
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">Total Repair Orders:</span>
            <span class="font-medium">{{ maintenanceData.total_repair_orders || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">Total Invoice Amount:</span>
            <span class="font-medium">${{ formatCurrency(maintenanceData.total_invoice_amount) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">QS Invoice Amount:</span>
            <span class="font-medium">${{ formatCurrency(maintenanceData.qs_invoice_amount) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">Total Miles:</span>
            <span class="font-medium">{{ formatNumber(maintenanceData.total_miles) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">Cost Per Mile (CPM):</span>
            <span class="font-medium">${{ formatCurrency(maintenanceData.cpm, 3) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">QS CPM:</span>
            <span class="font-medium">${{ formatCurrency(maintenanceData.qs_cpm, 3) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-muted-foreground">QS MVtS:</span>
            <span class="font-medium">{{ formatNumber(maintenanceData.qs_MVtS, 2) }}</span>
          </div>
        </div>
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

    <!-- Middle Panel: Areas of Concern -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Areas of Concern</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <li v-for="(area, index) in topAreasOfConcern" :key="index" class="flex justify-between items-center">
            <span class="text-sm">{{ area.concern }}</span>
            <Badge variant="outline">{{ area.count }}</Badge>
          </li>
          <li v-if="topAreasOfConcern.length === 0" class="text-center text-muted-foreground">
            No data available
          </li>
        </ul>
      </div>
    </div>

    <!-- Right Panel: Work Orders by Truck -->
    <div class="bg-card rounded-lg border shadow-sm">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Top Trucks by Work Orders</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <li v-for="(truck, index) in topWorkOrdersByTruck" :key="index" class="flex justify-between items-center">
            <span class="text-sm">Truck #{{ truck.truckid }}</span>
            <Badge variant="outline">{{ truck.work_order_count }}</Badge>
          </li>
          <li v-if="topWorkOrdersByTruck.length === 0" class="text-center text-muted-foreground">
            No data available
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  maintenanceData: {
    type: Object,
    default: () => ({})
  },
  tenantSlug: {
    type: String,
    required: true
  }
});

// Navigate to maintenance details page
const navigateToDetails = () => {
  router.visit(route('repair_orders.index', { tenantSlug: props.tenantSlug }));
};

// Get top 5 areas of concern
const topAreasOfConcern = computed(() => {
  const areas = props.maintenanceData.areas_of_concern || [];
  return areas.slice(0, 5);
});

// Get top 5 trucks by work orders
const topWorkOrdersByTruck = computed(() => {
  const trucks = props.maintenanceData.work_orders_by_truck || [];
  return trucks.slice(0, 5);
});

// Format currency values
const formatCurrency = (value, decimals = 2) => {
  if (value === undefined || value === null) return '0.00';
  return Number(value).toFixed(decimals);
};

// Format number values
const formatNumber = (value, decimals = 0) => {
  if (value === undefined || value === null) return '0';
  return Number(value).toLocaleString('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  });
};
</script>