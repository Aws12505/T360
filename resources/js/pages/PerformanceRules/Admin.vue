<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import EntryForm from './Form.vue'  // Global metrics form component

/**
 * Props passed from the backend via Inertia.
 * - metrics: Object containing global performance metrics.
 * - tenantSlug: Optional string if tenant-specific.
 */
const props = defineProps({
  metrics: Object,
  tenantSlug: {
    type: String,
    default: null,
  },
})
const { tenantSlug } = props

// Define breadcrumbs for the layout.
const breadcrumbs = [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? route('dashboard', { tenantSlug }) : route('admin.dashboard'),
  },
]

// Reactive state to control the modal for editing global metrics.
const showForm = ref(false)
// Reactive state to hold the current metrics data for editing.
const editing = ref({})

// List of metrics and levels for display.
const metricsList = ['acceptance', 'on_time', 'maintenance_variance', 'open_boc', 'vcr_preventable']
const levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor']

// Compute a display object from the metrics data to show each metric as a card.
const displayMetrics = computed(() => {
  if (!props.metrics) return {}
  const data = {}
  metricsList.forEach(metric => {
    data[metric] = levels.map(level => {
      return {
        level: level.replace(/_/g, ' '),
        value: props.metrics[`${metric}_${level}`] ?? '-',
        operator: props.metrics[`${metric}_${level}_operator`] ?? '-',
      }
    })
  })
  // Add safety bonus eligibility levels.
  data.safety_bonus_eligible_levels = props.metrics.safety_bonus_eligible_levels || []
  return data
})

// Function to open the metrics editor modal.
function openEditor() {
  editing.value = props.metrics ?? {}
  showForm.value = true
}

// Function to close the editor modal.
function closeEditor() {
  showForm.value = false
  editing.value = {}
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-6xl mx-auto p-6 space-y-8">
      <!-- Header with title and Edit button -->
      <div class="flex flex-col sm:flex-row justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Performance Metrics
        </h1>
        <button @click="openEditor" class="mt-4 sm:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">
          Edit Global Metrics
        </button>
      </div>

      <!-- Show the metrics form modal if editing -->
      <div v-if="showForm">
        <EntryForm :entry="editing" @saved="closeEditor" @cancel="closeEditor" />
      </div>

      <!-- Display metrics as cards when not editing -->
      <div v-else-if="props.metrics" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="metric in metricsList" :key="metric" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 capitalize">
            {{ metric.replace(/_/g, ' ') }}
          </h2>
          <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Level</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Value</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Operator</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="(item, idx) in displayMetrics[metric]" :key="idx" class="hover:bg-gray-100 dark:hover:bg-gray-700">
                  <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.level }}</td>
                  <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.value }}</td>
                  <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.operator }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Safety Bonus Eligibility Card -->
        <div v-if="displayMetrics.safety_bonus_eligible_levels.length" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 col-span-1 md:col-span-2">
          <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">
            Safety Bonus Eligibility
          </h2>
          <div class="flex flex-wrap gap-4">
            <span
              v-for="(level, index) in displayMetrics.safety_bonus_eligible_levels"
              :key="index"
              class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 px-3 py-1 rounded-full text-sm"
            >
              {{ level.replace(/_/g, ' ') }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
