<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Calendar } from 'lucide-vue-next'

const props = defineProps({
  driverData: { type: Object, required: true },
})

const filters = [
  { label: 'All Time', value: 'full' },
  { label: 'Quarterly', value: 'quarterly' },
  { label: 'T6W', value: '6w' },
  { label: 'WTD', value: 'current-week' },
  { label: 'Yesterday', value: 'yesterday' }
]

// ✅ Read-only computed filter
const activeFilter = computed(() => props.driverData.dateFilter ?? 'full')

function selectFilter(filterValue) {
  if (filterValue === activeFilter.value) return

  const routeParams = {dateFilter: filterValue}

  const routeName = 'driver.dashboard'

  router.visit(route(routeName, routeParams), {
    preserveScroll: true,
    only: ['driverData'],
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
