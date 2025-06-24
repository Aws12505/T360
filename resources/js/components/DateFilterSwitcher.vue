<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Calendar } from 'lucide-vue-next'

const props = defineProps({
  driverID: { type: Number, required: true },
  driver: { type: Object, required: true },
  tenantSlug: { type: String, default: null }
})

const filters = [
  { label: 'All Time', value: 'full' },
  { label: 'Quarterly', value: 'quarterly' },
  { label: 'T6W', value: '6w' },
  { label: 'WTD', value: 'current-week' },
  { label: 'Yesterday', value: 'yesterday' }
]

// ✅ Read-only computed filter
const activeFilter = computed(() => props.driver.dateFilter ?? 'full')

function selectFilter(filterValue) {
  if (filterValue === activeFilter.value) return

  const routeParams = props.tenantSlug
    ? { tenantSlug: props.tenantSlug, driver: props.driverID, dateFilter: filterValue }
    : { driver: props.driverID, dateFilter: filterValue }

  const routeName = props.tenantSlug ? 'driver.show' : 'driver.show.admin'

  router.visit(route(routeName, routeParams), {
    preserveScroll: true,
    only: ['driver'],
  })
}
</script>

<template>
  <div class="flex items-center space-x-2">
    <div class="border rounded-full px-3 py-1 text-sm flex items-center">
      <Calendar class="mr-2 h-4 w-4"/>
      <span>Filter:</span>
    </div>

    <button
      v-for="filter in filters"
      :key="filter.value"
      @click="selectFilter(filter.value)"
      class="border rounded-full px-3 py-1 text-sm transition-colors"
      :class="{
        'bg-primary/10 border-primary/30 text-primary': activeFilter === filter.value,
        'hover:bg-muted/20': activeFilter !== filter.value
      }"
    >
      {{ filter.label }}
    </button>
  </div>
</template>
