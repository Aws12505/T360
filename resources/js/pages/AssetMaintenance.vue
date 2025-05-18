<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head title="Asset Maintenance" />

    <div class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl m-auto pt-2 space-y-2 md:space-y-4 lg:space-y-6">
      <!-- Success/Error Messages -->
      <Alert v-if="successMessage" variant="success" class="animate-in fade-in duration-300">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>
      <Alert v-if="errorMessage" variant="destructive" class="animate-in fade-in duration-300">
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>{{ errorMessage }}</AlertDescription>
      </Alert>

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6">
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-200">
          Asset Management
        </h1>
        <div class="flex flex-wrap gap-3 mt-2 sm:mt-0">
          <!-- Optional action buttons could go here -->
        </div>
      </div>

      <!-- Tabs -->
      <Card class="shadow-sm border bg-card">
        <CardContent class="p-0">
          <div class="border-b">
            <div class="flex justify-center space-x-8 px-0 pt-4">
              <Button
                @click="switchComponent('trucks')"
                variant="ghost"
                :class="[
                  activeTab === 'trucks'
                    ? 'border-b-2 border-primary text-primary'
                    : 'text-muted-foreground hover:text-foreground'
                ]"
                class="py-2 px-3 -mb-px font-medium text-sm rounded-none transition-colors duration-200 inline-flex items-center"
              >
                <Icon name="truck" class="mr-2 h-4 w-4" />
                <span>Trucks</span>
              </Button>
              <Button
                @click="switchComponent('repairOrders')"
                variant="ghost"
                :class="[
                  activeTab === 'repairOrders'
                    ? 'border-b-2 border-primary text-primary'
                    : 'text-muted-foreground hover:text-foreground'
                ]"
                class="py-2 px-0 -mb-px font-medium text-sm rounded-none transition-colors duration-200 inline-flex items-center"
              >
                <Icon name="clipboardList" class="mr-2 h-4 w-4" />
                <span>Repair Orders</span>
              </Button>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="flex justify-center items-center p-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>

          <!-- Dynamic Component -->
          <div v-else class="p-0">
            <component :is="currentComponent" v-bind="currentProps" />
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import TrucksComponent from '@/components/Truck/TrucksComponent.vue'
import RepairOrdersComponent from '@/components/RepairOrders/RepairOrdersComponent.vue'
import Icon from '@/components/Icon.vue'
import { Card, CardContent, Button, Alert, AlertTitle, AlertDescription } from '@/components/ui'

// all props passed down from the parent controller
const props = defineProps({
  entries:        { type: Array,  default: () => [] },
  tenants:        { type: Array,  default: () => [] },
  trucks:         { type: Array,  default: () => [] },
  vendors:        { type: Array,  default: () => [] },
  areasOfConcern: { type: Array,  default: () => [] },
  repairOrders:   { type: Object, default: () => ({ data: [], links: [] }) },
  dateRange:               { type: Object, default: null },
  woStatuses:              { type: Array,  default: () => [] },
  weekNumber:              { type: Number, default: null },
  startWeekNumber:         { type: Number, default: null },
  endWeekNumber:           { type: Number, default: null },
  year:                    { type: Number, default: null },
  canceledQSInvoices:      { type: Array,  default: () => [] },
  outstandingInvoices:     { type: Array,  default: () => [] },
  workOrdersByTruck:       { type: Array,  default: () => [] },
  workOrderByAreasOfConcern:{ type: Array, default: () => [] },
  filters:                 { type: Object, default: () => ({}) },
  dateFilter:              { type: String, default: 'yesterday' },
  tenantSlug:  String,
  SuperAdmin:  Boolean,
  perPage:    { type: Number, default: 10 },
  openedComponent: { type: String, default: 'trucks' },
})

// UI state management
const isLoading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// single source of truth for your tab
const activeTab = ref<'trucks'|'repairOrders'>(props.openedComponent || 'trucks')

// breadcrumbs, unchanged
const breadcrumbs = computed(() => [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
  { title: 'Asset Management', href: '#' },
])

// pick which component to render
const currentComponent = computed(() =>
  activeTab.value === 'trucks'
    ? TrucksComponent
    : RepairOrdersComponent
)

// build props for that component
const currentProps = computed(() => {
  if (activeTab.value === 'trucks') {
    return {
      entries:        props.entries,
      tenantSlug:     props.tenantSlug,
      SuperAdmin:     props.SuperAdmin,
      tenants:        props.tenants,
      trucks:         props.trucks,
      vendors:        props.vendors,
      areasOfConcern: props.areasOfConcern,
    }
  } else {
    return {
      repairOrders:             props.repairOrders,
      tenantSlug:               props.tenantSlug,
      SuperAdmin:               props.SuperAdmin,
      tenants:                  props.tenants,
      trucks:                   props.trucks,
      vendors:                  props.vendors,
      areasOfConcern:           props.areasOfConcern,
      dateRange:                props.dateRange,
      woStatuses:               props.woStatuses,
      weekNumber:               props.weekNumber,
      startWeekNumber:          props.startWeekNumber,
      endWeekNumber:            props.endWeekNumber,
      year:                     props.year,
      canceledQSInvoices:       props.canceledQSInvoices,
      outstandingInvoices:      props.outstandingInvoices,
      workOrdersByTruck:        props.workOrdersByTruck,
      workOrderByAreasOfConcern:props.workOrderByAreasOfConcern,
      filters:                  props.filters,
      dateFilter:               props.dateFilter,
      perPage:                  props.perPage,
    }
  }
})

// flip UI + update only the openedComponent param in the URL
function switchComponent(component: 'trucks'|'repairOrders') {
  // Show loading state briefly
  isLoading.value = true
  activeTab.value = component

  // clear all other query params
  const url = new URL(window.location.href)
  url.search = ''

  // set only our tab
  url.searchParams.set('openedComponent', component)

  // replaceState so no extra history entry
  window.history.replaceState({}, '', url.href)
  
  // Hide loading after a short delay to show transition
  setTimeout(() => {
    isLoading.value = false
  }, 300)
}
</script>
