<template>
  <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 border">
    <!-- Card Header: Display the summary range -->
    <h2 class="text-lg font-semibold mb-3">{{ range }}</h2>

    <!-- Performance Data Section -->
    <div v-for="(value, key) in performanceData" :key="key" class="mb-2">
      <div class="flex justify-between text-sm text-gray-600">
        <span class="capitalize">{{ formatKey(key) }}</span>
        <span class="font-medium text-gray-800">{{ formatValue(key, value) }}</span>
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

    <!-- Safety Data Section -->
    <div class="mt-6 border-t pt-4">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Safety Summary</h3>
      <div
        v-for="(value, key) in safetyData"
        :key="'safety-' + key"
        class="flex justify-between text-sm text-gray-600 mb-1"
      >
        <span class="capitalize">{{ formatKey(key) }}</span>
        <span class="font-medium text-gray-800">{{ formatValue(key, value) }}</span>
      </div>
    </div>

    <!-- Rejection Breakdown Section -->
    <div v-if="rejectionBreakdown" class="mt-6 border-t pt-4">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Rejections by Driver</h3>
      <div
        v-for="driver in rejectionBreakdown.by_driver"
        :key="driver.driver_name"
        class="text-sm text-gray-700 mb-1"
      >
        <div class="font-medium">{{ driver.driver_name }}</div>
        <ul class="ml-4 text-xs text-gray-600">
          <li>Total: {{ driver.total_rejections }} ({{ driver.total_penalty }} pts)</li>
          <li>Block: {{ driver.total_block_rejections }} ({{ driver.total_block_penalty }} pts)</li>
          <li>Load: {{ driver.total_load_rejections }} ({{ driver.total_load_penalty }} pts)</li>
        </ul>
      </div>

      <h3 class="text-sm font-semibold text-gray-700 mt-4 mb-2">Rejections by Reason</h3>
      <div
        v-for="reason in rejectionBreakdown.by_reason"
        :key="reason.reason_code"
        class="text-sm text-gray-700 mb-1"
      >
        <div class="font-medium">{{ reason.reason_code }}</div>
        <ul class="ml-4 text-xs text-gray-600">
          <li>Total: {{ reason.total_rejections }} ({{ reason.total_penalty }} pts)</li>
          <li>Block: {{ reason.total_block_rejections }} ({{ reason.total_block_penalty }} pts)</li>
          <li>Load: {{ reason.total_load_rejections }} ({{ reason.total_load_penalty }} pts)</li>
        </ul>
      </div>
    </div>

    <!-- Delay Breakdown Section -->
    <div v-if="delayBreakdown" class="mt-6 border-t pt-4">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Delays by Driver</h3>
      <div
        v-for="driver in delayBreakdown.by_driver"
        :key="driver.driver_name"
        class="text-sm text-gray-700 mb-1"
      >
        <div class="font-medium">{{ driver.driver_name }}</div>
        <ul class="ml-4 text-xs text-gray-600">
          <li>Total: {{ driver.total_delays }} ({{ driver.total_penalty }} pts)</li>
          <li>Origin: {{ driver.total_origin_delays }} ({{ driver.total_origin_penalty }} pts)</li>
          <li>Destination: {{ driver.total_destination_delays }} ({{ driver.total_destination_penalty }} pts)</li>
        </ul>
      </div>

      <h3 class="text-sm font-semibold text-gray-700 mt-4 mb-2">Delays by Code</h3>
      <div
        v-for="code in delayBreakdown.by_code"
        :key="code.code"
        class="text-sm text-gray-700 mb-1"
      >
        <div class="font-medium">{{ code.code }}</div>
        <ul class="ml-4 text-xs text-gray-600">
          <li>Total: {{ code.total_delays }} ({{ code.total_penalty }} pts)</li>
          <li>Origin: {{ code.total_origin_delays }} ({{ code.total_origin_penalty }} pts)</li>
          <li>Destination: {{ code.total_destination_delays }} ({{ code.total_destination_penalty }} pts)</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * Props:
 * - range: The summary range label (e.g., "Yesterday").
 * - performanceData: Object containing performance data for this range.
 * - ratings: Object mapping keys to rating strings.
 * - safetyData: Object with safety data.
 * - rejectionBreakdown: (Optional) Object with rejection breakdown data.
 * - delayBreakdown: (Optional) Object with delay breakdown data.
 */
const props = defineProps({
  range: String,
  performanceData: Object,
  ratings: Object,
  safetyData: {
    type: Object,
    default: () => ({}),
  },
  rejectionBreakdown: {
    type: Object,
    default: null,
  },
  delayBreakdown: {
    type: Object,
    default: null,
  },
})

// Helper: Replace underscores with spaces.
const formatKey = (key: string) => key.replace(/_/g, ' ')

// Helper: Normalize key names for rating lookup.
const normalizeKey = (key: string) => {
  if (key === 'sum_vcr_preventable') return 'vcr_preventable'
  if (key === 'sum_open_boc') return 'open_boc'
  return key
}

// Helper: Format values and add "%" for specific keys if needed.
const formatValue = (key: string, val: any) => {
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

// Helper: Return Tailwind CSS classes for rating badges.
const ratingBadgeClass = (rating: string) => {
  switch (rating) {
    case 'Fantastic+':
      return 'bg-green-600 text-white'
    case 'Fantastic':
      return 'bg-green-400 text-white'
    case 'Good':
      return 'bg-blue-400 text-white'
    case 'Fair':
      return 'bg-yellow-400 text-white'
    case 'Poor':
      return 'bg-red-500 text-white'
    default:
      return 'bg-gray-300 text-gray-800'
  }
}
</script>
