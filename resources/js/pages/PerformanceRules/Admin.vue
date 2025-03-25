<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import EntryForm from './Form.vue'

const props = defineProps({
  metrics: Object,
  tenantSlug: {
    type: String,
    default: null,
  },
})
const { tenantSlug } = props

const breadcrumbs = [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? route('dashboard', { tenantSlug }) : route('admin.dashboard'),
  },
]

const showForm = ref(false)
const editing = ref({})

function openEditor() {
  editing.value = props.metrics ?? {}
  showForm.value = true
}

function closeEditor() {
  showForm.value = false
  editing.value = {}
}

// Only show the metrics that are present in the EntryForm
const metricsList = ['acceptance', 'on_time', 'maintenance_variance', 'open_boc', 'vcr_preventable']
const levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor']

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
  // Add safety bonus eligibility if available
  data.safety_bonus_eligible_levels = props.metrics.safety_bonus_eligible_levels || []
  return data
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-6xl mx-auto p-6 space-y-8">
      <div class="flex flex-col sm:flex-row justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Performance Metrics
        </h1>
        <button @click="openEditor" class="mt-4 sm:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">
          Edit Global Metrics
        </button>
      </div>

      <div v-if="showForm">
        <EntryForm :entry="editing" @saved="closeEditor" @cancel="closeEditor" />
      </div>

      <div v-else-if="metrics" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Display each metric as a card -->
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
