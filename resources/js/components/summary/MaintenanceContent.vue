<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
  
  <!-- Outstanding Invoices Filter -->
  <div class="bg-card rounded-lg border shadow-sm p-4 mb-6">
    <h3 class="text-lg font-semibold mb-4">Outstanding Invoices Filter</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <Label for="min-invoice-amount">Minimum Invoice Amount ($)</Label>
        <Input 
          id="min-invoice-amount" 
          v-model="minInvoiceAmount" 
          type="number" 
          min="0" 
          step="100"
          placeholder="Enter minimum amount" 
          class="mt-1"
        />
      </div>
      <div>
        <Label for="outstanding-since">Outstanding Since</Label>
        <Input 
          id="outstanding-since" 
          v-model="outstandingDate" 
          type="date" 
          class="mt-1"
        />
      </div>
    </div>
    <div class="mt-4 flex justify-end">
      <Button @click="applyFilter">Apply Filter</Button>
    </div>
  </div>
  
  <!-- Outstanding Invoices Section -->
  <div v-if="maintenanceData.outstanding_invoices && maintenanceData.outstanding_invoices.length > 0" class="bg-card rounded-lg border shadow-sm mb-6">
    <div class="p-4 border-b flex justify-between items-center">
      <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
      <Badge variant="outline" class="text-sm">{{ maintenanceData.outstanding_invoices.length }} invoices</Badge>
    </div>
    <div class="p-4">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>RO Number</TableHead>
            <TableHead>Vendor</TableHead>
            <TableHead>Week</TableHead>
            <TableHead class="text-right">Amount</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="invoice in maintenanceData.outstanding_invoices" :key="invoice.ro_number">
            <TableCell>{{ invoice.ro_number }}</TableCell>
            <TableCell>{{ invoice.vendor_name }}</TableCell>
            <TableCell>W{{ invoice.week_number }}/{{ invoice.year }}</TableCell>
            <TableCell class="text-right">${{ formatCurrency(invoice.invoice_amount) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
  <div v-else-if="showOutstandingInvoicesSection" class="bg-card rounded-lg border shadow-sm mb-6">
    <div class="p-4 border-b">
      <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
    </div>
    <div class="p-4 text-center text-muted-foreground">
      No outstanding invoices match the current criteria
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { router } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
  maintenanceData: {
    type: Object,
    default: () => ({})
  },
  tenantSlug: {
    type: String,
    required: true
  },
  showOutstandingInvoicesSection: {
    type: Boolean,
    default: false
  },
  initialMinInvoiceAmount: {
    type: [Number, String, null],
    default: null
  },
  initialOutstandingDate: {
    type: [String, null],
    default: null
  }
});

const emit = defineEmits(['filter-applied']);

// Filter state
const minInvoiceAmount = ref(props.initialMinInvoiceAmount);
const outstandingDate = ref(props.initialOutstandingDate);

// Apply filter
const applyFilter = () => {
  emit('filter-applied', {
    minInvoiceAmount: minInvoiceAmount.value,
    outstandingDate: outstandingDate.value
  });
};

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