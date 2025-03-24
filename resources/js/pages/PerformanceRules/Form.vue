<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  entry: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['saved', 'cancel'])

const levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor']
const metrics = ['acceptance', 'on_time', 'maintenance_variance', 'open_boc', 'vcr_preventable']

const operators = [
  { label: 'Less', value: 'less' },
  { label: 'Less or Equal', value: 'less_or_equal' },
  { label: 'Equal', value: 'equal' },
  { label: 'More or Equal', value: 'more_or_equal' },
  { label: 'More', value: 'more' },
]

const form = useForm({
  safety_bonus_eligible_levels: props.entry.safety_bonus_eligible_levels ?? [],
  ...Object.fromEntries(metrics.flatMap(metric =>
    levels.flatMap(level => [
      [`${metric}_${level}`, props.entry[`${metric}_${level}`] ?? ''],
      [`${metric}_${level}_operator`, props.entry[`${metric}_${level}_operator`] ?? '']
    ])
  ))
})

const submit = () => {
  form.post(route('performance-metrics.update'), {
    onSuccess: () => emit('saved'),
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-10 border p-6 rounded-lg shadow bg-white">
    <div v-for="metric in metrics" :key="metric" class="space-y-4">
      <h2 class="text-lg font-bold capitalize">{{ metric.replace(/_/g, ' ') }}</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="level in levels" :key="`${metric}-${level}`">
          <label class="block text-sm font-medium capitalize mb-1">
            {{ level.replace(/_/g, ' ') }}
          </label>
          <input
            v-model="form[`${metric}_${level}`]"
            type="number"
            step="0.01"
            class="input w-full mb-1"
          />
          <select
            v-model="form[`${metric}_${level}_operator`]"
            class="input w-full"
          >
            <option v-for="op in operators" :key="op.value" :value="op.value">
              {{ op.label }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <div>
      <h2 class="text-lg font-bold mb-3">Safety Bonus Eligibility</h2>
      <div class="flex gap-4 flex-wrap">
        <label v-for="level in levels" :key="level" class="flex items-center gap-2">
          <input
            type="checkbox"
            :value="level"
            v-model="form.safety_bonus_eligible_levels"
          />
          <span class="capitalize">{{ level.replace('_', ' ') }}</span>
        </label>
      </div>
    </div>

    <div class="pt-6 flex gap-4">
      <button type="submit" class="btn btn-primary" :disabled="form.processing">
        Save
      </button>
      <button type="button" @click="emit('cancel')" class="btn btn-secondary">Cancel</button>
    </div>
  </form>
</template>
