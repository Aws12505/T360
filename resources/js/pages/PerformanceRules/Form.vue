<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
// Import ShadCN UI components
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Loader2 } from 'lucide-vue-next'

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

// Define safety metrics and their tiers
const safetyMetrics = [
  'driver_distraction',
  'speeding_violation',
  'sign_violation',
  'traffic_light_violation',
  'following_distance'
]
const safetyTiers = ['gold', 'silver', 'not_eligible']

// Operators available for comparison.
const operators = [
  { label: 'Less', value: 'less' },
  { label: 'Less or Equal', value: 'less_or_equal' },
  { label: 'Equal', value: 'equal' },
  { label: 'More or Equal', value: 'more_or_equal' },
  { label: 'More', value: 'more' },
]

// Initialize form with default values using Inertia's useForm helper.
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
  // Add safety metrics
  ...Object.fromEntries(
    safetyMetrics.flatMap(metric =>
      safetyTiers.flatMap(tier => [
        [`${metric}_${tier}`, props.entry[`${metric}_${tier}`] ?? ''],
        [`${metric}_${tier}_operator`, props.entry[`${metric}_${tier}_operator`] ?? ''],
      ])
    )
  ),
})

// Function to toggle safety bonus eligibility levels
const toggleSafetyBonusLevel = (level: string) => {
  const index = form.safety_bonus_eligible_levels.indexOf(level)
  if (index === -1) {
    form.safety_bonus_eligible_levels.push(level)
  } else {
    form.safety_bonus_eligible_levels.splice(index, 1)
  }
}

// Function to submit the form data to the backend.
const submit = () => {
  form.post(route('performance-metrics.update'), {
    onSuccess: () => emit('saved'),
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-8">
    <Card v-for="metric in metrics" :key="metric" class="shadow-sm">
      <CardHeader>
        <CardTitle class="capitalize">{{ metric.replace(/_/g, ' ') }}</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="level in levels" :key="`${metric}-${level}`" class="space-y-2">
            <Label :for="`${metric}-${level}`" class="capitalize">
              {{ level.replace(/_/g, ' ') }}
            </Label>
            <Input
              :id="`${metric}-${level}`"
              v-model="form[`${metric}_${level}`]"
              type="number"
              step="0.01"
              class="w-full"
            />
            <Select v-model="form[`${metric}_${level}_operator`]">
              <SelectTrigger class="w-full">
                <SelectValue :placeholder="form[`${metric}_${level}_operator`] ? operators.find(op => op.value === form[`${metric}_${level}_operator`])?.label : 'Select operator'" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="op in operators" :key="op.value" :value="op.value">
                  {{ op.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Safety Metrics Section -->
    <Card v-for="metric in safetyMetrics" :key="metric" class="shadow-sm">
      <CardHeader>
        <CardTitle class="capitalize">{{ metric.replace(/_/g, ' ') }}</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="tier in safetyTiers" :key="`${metric}-${tier}`" class="space-y-2">
            <Label :for="`${metric}-${tier}`" class="capitalize">
              {{ tier.replace(/_/g, ' ') }}
            </Label>
            <Input
              :id="`${metric}-${tier}`"
              v-model="form[`${metric}_${tier}`]"
              type="number"
              step="0.01"
              class="w-full"
            />
            <Select v-model="form[`${metric}_${tier}_operator`]">
              <SelectTrigger class="w-full">
                <SelectValue :placeholder="form[`${metric}_${tier}_operator`] ? operators.find(op => op.value === form[`${metric}_${tier}_operator`])?.label : 'Select operator'" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="op in operators" :key="op.value" :value="op.value">
                  {{ op.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Safety Bonus Eligibility Section -->
    <Card class="shadow-sm">
      <CardHeader>
        <CardTitle>Safety Bonus Eligibility</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="flex flex-wrap gap-4">
          <div v-for="level in levels" :key="level" class="flex items-center space-x-2">
            <Checkbox
              :id="`safety-bonus-${level}`"
              :checked="form.safety_bonus_eligible_levels.includes(level)"
              @update:checked="toggleSafetyBonusLevel(level)"
            />
            <Label :for="`safety-bonus-${level}`" class="capitalize">
              {{ level.replace(/_/g, ' ') }}
            </Label>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3">
      <Button
        type="button"
        @click="emit('cancel')"
        variant="outline"
      >
        Cancel
      </Button>
      <Button
        type="submit"
        :disabled="form.processing"
      >
        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
        {{ form.processing ? 'Saving...' : 'Save' }}
      </Button>
    </div>
  </form>
</template>
