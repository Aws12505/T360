<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head title="Asset Management" />

    <div class="space-y-6">
      <!-- Tabs -->
      <div class="border-b">
        <div class="flex space-x-8">
          <button
            @click="activeTab = 'trucks'"
            :class="[tabClass('trucks'), 'py-2 px-1 -mb-px font-medium text-sm']"
          >
            Trucks
          </button>
          <button
            @click="activeTab = 'repairOrders'"
            :class="[tabClass('repairOrders'), 'py-2 px-1 -mb-px font-medium text-sm']"
          >
            Repair Orders
          </button>
        </div>
      </div>

      <!-- Dynamically mount the right component -->
      <component
        :is="currentComponent"
        v-bind="currentProps"
        v-on="currentListeners"
      />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import TrucksComponent from '@/components/Truck/TrucksComponent.vue'
import RepairOrdersComponent from '@/components/RepairOrders/RepairOrdersComponent.vue'

/** Props **/
const props = defineProps({
  entries:        { type: Array, default: () => [] },
  tenants:        { type: Array, default: () => [] },
  trucks:         { type: Array, default: () => [] },
  vendors:        { type: Array, default: () => [] },
  areasOfConcern: { type: Array, default: () => [] },
  repairOrders:   { type: Object, default: () => ({ data: [], links: [] }) },
  dateRange:               { type: Object, default: null },
  woStatuses:              { type: Array, default: () => [] },
  weekNumber:              { type: Number, default: null },
  startWeekNumber:         { type: Number, default: null },
  endWeekNumber:           { type: Number, default: null },
  year:                    { type: Number, default: null },
  canceledQSInvoices:      { type: Array, default: () => [] },
  outstandingInvoices:     { type: Array, default: () => [] },
  workOrdersByTruck:       { type: Array, default: () => [] },
  workOrderByAreasOfConcern:{ type: Array, default: () => [] },
  filters:                 { type: Object, default: () => ({}) },
  dateFilter:              { type: String, default: 'yesterday' },
  tenantSlug:  String,
  SuperAdmin:  Boolean,
  perPage:    { type: Number, default: 10 },
})

/** Emit **/
const emit = defineEmits<{
  (e: 'update:perPage', val: number): void
}>()

/** Local state **/
const activeTab = ref<'trucks'|'repairOrders'>('trucks')

/** Breadcrumbs **/
const breadcrumbs = computed(() => [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
  { title: 'Asset Management', href: '#' },
])

/** Helpers **/
const tabClass = (tab: typeof activeTab.value) =>
  activeTab.value === tab
    ? 'border-b-2 border-primary text-primary'
    : 'text-muted-foreground hover:text-foreground'

/** Decide which component to mount **/
const currentComponent = computed(() =>
  activeTab.value === 'trucks' ? TrucksComponent : RepairOrdersComponent
)

/** Build props for child **/
const currentProps = computed(() => {
  if (activeTab.value === 'trucks') {
    return {
      entries:        props.entries,
      tenantSlug:     props.tenantSlug,
      SuperAdmin:     props.SuperAdmin,
      tenants:        props.tenants,
      perPage:        props.perPage,
      trucks:         props.trucks,
      vendors:        props.vendors,
      areasOfConcern: props.areasOfConcern,
    }
  }
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
})

/** Propagate perPage updates **/
const currentListeners = computed(() => ({
  'update:perPage': (val: number) => emit('update:perPage', val)
}))
</script>
