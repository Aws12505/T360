<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  entry: {
    type: Object,
    default: () => ({})
  },
  tenants: {
    type: Array,
    default: () => [],
  }
})

const emit = defineEmits(['saved', 'cancel'])

const operators = [
  { label: 'Less', value: 'less' },
  { label: 'Less or Equal', value: 'less_or_equal' },
  { label: 'Equal', value: 'equal' },
  { label: 'More or Equal', value: 'more_or_equal' },
  { label: 'More', value: 'more' },
]

const form = useForm({
  tenant_id: props.entry.tenant_id ?? '',

  // Acceptance
  acceptance_fantastic_plus: props.entry.acceptance_fantastic_plus ?? '',
  acceptance_fantastic_plus_operator: props.entry.acceptance_fantastic_plus_operator ?? '',
  acceptance_fantastic: props.entry.acceptance_fantastic ?? '',
  acceptance_fantastic_operator: props.entry.acceptance_fantastic_operator ?? '',
  acceptance_good: props.entry.acceptance_good ?? '',
  acceptance_good_operator: props.entry.acceptance_good_operator ?? '',
  acceptance_fair: props.entry.acceptance_fair ?? '',
  acceptance_fair_operator: props.entry.acceptance_fair_operator ?? '',
  acceptance_poor: props.entry.acceptance_poor ?? '',
  acceptance_poor_operator: props.entry.acceptance_poor_operator ?? '',

  // On-Time
  on_time_fantastic_plus: props.entry.on_time_fantastic_plus ?? '',
  on_time_fantastic_plus_operator: props.entry.on_time_fantastic_plus_operator ?? '',
  on_time_fantastic: props.entry.on_time_fantastic ?? '',
  on_time_fantastic_operator: props.entry.on_time_fantastic_operator ?? '',
  on_time_good: props.entry.on_time_good ?? '',
  on_time_good_operator: props.entry.on_time_good_operator ?? '',
  on_time_fair: props.entry.on_time_fair ?? '',
  on_time_fair_operator: props.entry.on_time_fair_operator ?? '',
  on_time_poor: props.entry.on_time_poor ?? '',
  on_time_poor_operator: props.entry.on_time_poor_operator ?? '',

  // Maintenance Variance
  maintenance_variance_fantastic_plus: props.entry.maintenance_variance_fantastic_plus ?? '',
  maintenance_variance_fantastic_plus_operator: props.entry.maintenance_variance_fantastic_plus_operator ?? '',
  maintenance_variance_fantastic: props.entry.maintenance_variance_fantastic ?? '',
  maintenance_variance_fantastic_operator: props.entry.maintenance_variance_fantastic_operator ?? '',
  maintenance_variance_good: props.entry.maintenance_variance_good ?? '',
  maintenance_variance_good_operator: props.entry.maintenance_variance_good_operator ?? '',
  maintenance_variance_fair: props.entry.maintenance_variance_fair ?? '',
  maintenance_variance_fair_operator: props.entry.maintenance_variance_fair_operator ?? '',
  maintenance_variance_poor: props.entry.maintenance_variance_poor ?? '',
  maintenance_variance_poor_operator: props.entry.maintenance_variance_poor_operator ?? '',

  // Open BOC
  open_boc_fantastic_plus: props.entry.open_boc_fantastic_plus ?? '',
  open_boc_fantastic_plus_operator: props.entry.open_boc_fantastic_plus_operator ?? '',
  open_boc_fantastic: props.entry.open_boc_fantastic ?? '',
  open_boc_fantastic_operator: props.entry.open_boc_fantastic_operator ?? '',
  open_boc_good: props.entry.open_boc_good ?? '',
  open_boc_good_operator: props.entry.open_boc_good_operator ?? '',
  open_boc_fair: props.entry.open_boc_fair ?? '',
  open_boc_fair_operator: props.entry.open_boc_fair_operator ?? '',
  open_boc_poor: props.entry.open_boc_poor ?? '',
  open_boc_poor_operator: props.entry.open_boc_poor_operator ?? '',

  // Safety Bonus
  safety_bonus_eligible_levels: props.entry.safety_bonus_eligible_levels ?? [],

  // VCR Preventable
  vcr_preventable_fantastic_plus: props.entry.vcr_preventable_fantastic_plus ?? '',
  vcr_preventable_fantastic_plus_operator: props.entry.vcr_preventable_fantastic_plus_operator ?? '',
  vcr_preventable_fantastic: props.entry.vcr_preventable_fantastic ?? '',
  vcr_preventable_fantastic_operator: props.entry.vcr_preventable_fantastic_operator ?? '',
  vcr_preventable_good: props.entry.vcr_preventable_good ?? '',
  vcr_preventable_good_operator: props.entry.vcr_preventable_good_operator ?? '',
  vcr_preventable_fair: props.entry.vcr_preventable_fair ?? '',
  vcr_preventable_fair_operator: props.entry.vcr_preventable_fair_operator ?? '',
  vcr_preventable_poor: props.entry.vcr_preventable_poor ?? '',
  vcr_preventable_poor_operator: props.entry.vcr_preventable_poor_operator ?? '',
})

const sections = [
  'acceptance',
  'on_time',
  'maintenance_variance',
  'open_boc',
  'vcr_preventable',
]

const fieldKeys = computed(() => Object.keys(form.data()))

const submit = () => {
  const method = props.entry.id ? 'put' : 'post'
  const url = props.entry.id
    ? route('performance-metrics.update', props.entry.id)
    : route('performance-metrics.store')

  form[method](url, {
    onSuccess: () => emit('saved'),
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-10 border p-6 rounded-lg shadow bg-white">
    <!-- Tenant -->
    <div>
      <label class="block font-semibold mb-1">Tenant</label>
      <select v-model="form.tenant_id" class="input w-full">
        <option value="" disabled>Select Tenant</option>
        <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
          {{ tenant.name }}
        </option>
      </select>
      <div v-if="form.errors.tenant_id" class="text-red-500 text-sm mt-1">{{ form.errors.tenant_id }}</div>
    </div>

    <!-- Metrics Sections -->
    <div v-for="section in sections" :key="section">
      <h2 class="text-lg font-bold mb-3 capitalize">{{ section.replace(/_/g, ' ') }}</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="key in fieldKeys.filter(k => k.startsWith(section))"
          :key="key"
        >
          <label class="block font-medium mb-1 text-sm">
            {{ key.replace(`${section}_`, '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}
          </label>

          <select
            v-if="key.endsWith('_operator')"
            v-model="form[key]"
            class="input w-full"
          >
            <option v-for="op in operators" :key="op.value" :value="op.value">
              {{ op.label }}
            </option>
          </select>

          <input
            v-else
            v-model="form[key]"
            type="text"
            class="input w-full"
          />

          <div v-if="form.errors[key]" class="text-red-500 text-sm mt-1">
            {{ form.errors[key] }}
          </div>
        </div>
      </div>
    </div>

    <!-- Safety Bonus -->
    <div>
      <h2 class="text-lg font-bold mb-3">Safety Bonus Eligibility</h2>
      <div class="flex gap-4 flex-wrap">
        <label
          v-for="level in ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor']"
          :key="level"
          class="flex items-center gap-2"
        >
          <input
            type="checkbox"
            :value="level"
            v-model="form.safety_bonus_eligible_levels"
          />
          <span class="capitalize">{{ level.replace('_', ' ') }}</span>
        </label>
      </div>
    </div>

    <!-- Submit -->
    <div class="pt-6 flex gap-4">
      <button
        type="submit"
        class="btn btn-primary"
        :disabled="form.processing"
      >
        Save
      </button>
      <button type="button" @click="emit('cancel')" class="btn btn-secondary">Cancel</button>
    </div>
  </form>
</template>
