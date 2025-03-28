<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 * Props:
 * - entry: Object containing the current performance metrics values.
 * 
 * Emits:
 * - 'saved': When the form is successfully submitted.
 * - 'cancel': When the form is cancelled.
 */
const props = defineProps({
  entry: {
    type: Object,
    default: () => ({}),
  },
})
const emit = defineEmits(['saved', 'cancel'])

// Define the levels and metrics used for thresholds.
const levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor']
const metrics = ['acceptance', 'on_time', 'maintenance_variance', 'open_boc', 'vcr_preventable']

// Operators available for comparison.
const operators = [
  { label: 'Less', value: 'less' },
  { label: 'Less or Equal', value: 'less_or_equal' },
  { label: 'Equal', value: 'equal' },
  { label: 'More or Equal', value: 'more_or_equal' },
  { label: 'More', value: 'more' },
]

// Initialize form with default values using Inertia's useForm helper.
// The form object includes safety bonus eligibility and for each metric/level, both the value and its operator.
const form = useForm({
  safety_bonus_eligible_levels: props.entry.safety_bonus_eligible_levels ?? [],
  ...Object.fromEntries(
    metrics.flatMap(metric =>
      levels.flatMap(level => [
        [`${metric}_${level}`, props.entry[`${metric}_${level}`] ?? ''],
        [`${metric}_${level}_operator`, props.entry[`${metric}_${level}_operator`] ?? ''],
      ])
    )
  ),
})

// Function to submit the form data to the backend.
const submit = () => {
  form.post(route('performance-metrics.update'), {
    onSuccess: () => emit('saved'),
  })
}
</script>

<template>
  <!-- Form container with Tailwind classes for spacing, border, rounded corners, shadow, and dark mode support -->
  <form @submit.prevent="submit" class="space-y-10 border border-gray-200 dark:border-gray-700 p-6 rounded-lg shadow-lg bg-white dark:bg-gray-800">
    <!-- Loop through each metric -->
    <div v-for="metric in metrics" :key="metric" class="space-y-4">
      <h2 class="text-lg font-bold capitalize text-gray-800 dark:text-gray-100">
        {{ metric.replace(/_/g, ' ') }}
      </h2>
      <!-- Create a responsive grid for each level's input -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="level in levels" :key="`${metric}-${level}`">
          <!-- Label for the level -->
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 capitalize">
            {{ level.replace(/_/g, ' ') }}
          </label>
          <!-- Input for the metric value -->
          <input
            v-model="form[`${metric}_${level}`]"
            type="number"
            step="0.01"
            class="w-full rounded-md border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <!-- Select for the operator -->
          <select
            v-model="form[`${metric}_${level}_operator`]"
            class="w-full rounded-md border border-gray-300 dark:border-gray-600 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 mt-1"
          >
            <option v-for="op in operators" :key="op.value" :value="op.value">
              {{ op.label }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Safety Bonus Eligibility Section -->
    <div>
      <h2 class="text-lg font-bold mb-3 text-gray-800 dark:text-gray-100">Safety Bonus Eligibility</h2>
      <div class="flex gap-4 flex-wrap">
        <label v-for="level in levels" :key="level" class="flex items-center gap-2">
          <input
            type="checkbox"
            :value="level"
            v-model="form.safety_bonus_eligible_levels"
            class="h-4 w-4 text-blue-600 border-gray-300 rounded"
          />
          <span class="capitalize text-gray-700 dark:text-gray-300">
            {{ level.replace(/_/g, ' ') }}
          </span>
        </label>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="pt-6 flex gap-4">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition" :disabled="form.processing">
        Save
      </button>
      <button type="button" @click="emit('cancel')" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-md transition">
        Cancel
      </button>
    </div>
  </form>
</template>
