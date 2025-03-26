<template>
  <div class="bg-white shadow-md rounded-lg p-4 border">
    <h2 class="text-lg font-semibold mb-3">{{ range }}</h2>

    <!-- 📊 Performance Data -->
    <div v-for="(value, key) in data" :key="key" class="mb-2">
      <div class="flex justify-between text-sm text-gray-600">
        <span class="capitalize">{{ formatKey(key) }}</span>
        <span class="font-medium text-gray-800">
          {{ formatValue(key, value) }}
        </span>
      </div>
      <div class="text-xs mt-1">
        <span
          class="inline-block px-2 py-0.5 rounded-full"
          :class="ratingBadgeClass(ratings[normalizeKey(key)])"
        >
          {{ ratings[normalizeKey(key)] || 'N/A' }}
        </span>
      </div>
    </div>

    <!-- 🟦 Safety Summary Section -->
    <div class="mt-6 border-t pt-4">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Safety Summary</h3>
      <div
        v-for="(value, key) in safetySummary"
        :key="'safety-' + key"
        class="flex justify-between text-sm text-gray-600 mb-1"
      >
        <span class="capitalize">{{ formatKey(key) }}</span>
        <span class="font-medium text-gray-800">{{ formatValue(key, value) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  range: String,
  data: Object,
  ratings: Object,
  safetySummary: {
    type: Object,
    default: () => ({}),
  },
})

const formatKey = (key) => key.replace(/_/g, ' ')

const normalizeKey = (key) => {
  if (key === 'sum_vcr_preventable') return 'vcr_preventable'
  if (key === 'sum_open_boc') return 'open_boc'
  return key
}

const formatValue = (key, val) => {
  if (key === 'meets_safety_bonus_criteria') {
    return val == 1 ? 'Yes' : 'No'
  }

  const num = Number(val)
  if (!isNaN(num)) {
    const formatted = num.toFixed(2)
    return key === 'average_maintenance_variance_to_spend'
      ? `${formatted}%`
      : formatted
  }

  return val
}

const ratingBadgeClass = (rating) => {
  switch (rating) {
    case 'fantastic_plus':
      return 'bg-green-600 text-white'
    case 'fantastic':
      return 'bg-green-400 text-white'
    case 'good':
      return 'bg-blue-400 text-white'
    case 'fair':
      return 'bg-yellow-400 text-white'
    case 'poor':
      return 'bg-red-500 text-white'
    default:
      return 'bg-gray-300 text-gray-800'
  }
}
</script>
