<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  rule: Object,
  tenantSlug: {
    type: String,
    default: null,
  },
})

const { tenantSlug } = props

const breadcrumbs = [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? `/${tenantSlug}/dashboard` : '/dashboard',
  },
]

const operators = [
  { label: 'Less', value: 'less' },
  { label: 'Less or Equal', value: 'less_or_equal' },
  { label: 'Equal', value: 'equal' },
  { label: 'More or Equal', value: 'more_or_equal' },
  { label: 'More', value: 'more' },
]

const form = useForm({
  // Acceptance
  acceptance_fantastic_plus: props.rule?.acceptance_fantastic_plus ?? '',
  acceptance_fantastic_plus_operator: props.rule?.acceptance_fantastic_plus_operator ?? '',
  acceptance_fantastic: props.rule?.acceptance_fantastic ?? '',
  acceptance_fantastic_operator: props.rule?.acceptance_fantastic_operator ?? '',
  acceptance_good: props.rule?.acceptance_good ?? '',
  acceptance_good_operator: props.rule?.acceptance_good_operator ?? '',
  acceptance_fair: props.rule?.acceptance_fair ?? '',
  acceptance_fair_operator: props.rule?.acceptance_fair_operator ?? '',
  acceptance_poor: props.rule?.acceptance_poor ?? '',
  acceptance_poor_operator: props.rule?.acceptance_poor_operator ?? '',

  // On-Time
  on_time_fantastic_plus: props.rule?.on_time_fantastic_plus ?? '',
  on_time_fantastic_plus_operator: props.rule?.on_time_fantastic_plus_operator ?? '',
  on_time_fantastic: props.rule?.on_time_fantastic ?? '',
  on_time_fantastic_operator: props.rule?.on_time_fantastic_operator ?? '',
  on_time_good: props.rule?.on_time_good ?? '',
  on_time_good_operator: props.rule?.on_time_good_operator ?? '',
  on_time_fair: props.rule?.on_time_fair ?? '',
  on_time_fair_operator: props.rule?.on_time_fair_operator ?? '',
  on_time_poor: props.rule?.on_time_poor ?? '',
  on_time_poor_operator: props.rule?.on_time_poor_operator ?? '',

  // Maintenance Variance
  maintenance_variance_fantastic_plus: props.rule?.maintenance_variance_fantastic_plus ?? '',
  maintenance_variance_fantastic_plus_operator: props.rule?.maintenance_variance_fantastic_plus_operator ?? '',
  maintenance_variance_fantastic: props.rule?.maintenance_variance_fantastic ?? '',
  maintenance_variance_fantastic_operator: props.rule?.maintenance_variance_fantastic_operator ?? '',
  maintenance_variance_good: props.rule?.maintenance_variance_good ?? '',
  maintenance_variance_good_operator: props.rule?.maintenance_variance_good_operator ?? '',
  maintenance_variance_fair: props.rule?.maintenance_variance_fair ?? '',
  maintenance_variance_fair_operator: props.rule?.maintenance_variance_fair_operator ?? '',
  maintenance_variance_poor: props.rule?.maintenance_variance_poor ?? '',
  maintenance_variance_poor_operator: props.rule?.maintenance_variance_poor_operator ?? '',

  // Open BOC
  open_boc_fantastic_plus: props.rule?.open_boc_fantastic_plus ?? '',
  open_boc_fantastic_plus_operator: props.rule?.open_boc_fantastic_plus_operator ?? '',
  open_boc_fantastic: props.rule?.open_boc_fantastic ?? '',
  open_boc_fantastic_operator: props.rule?.open_boc_fantastic_operator ?? '',
  open_boc_good: props.rule?.open_boc_good ?? '',
  open_boc_good_operator: props.rule?.open_boc_good_operator ?? '',
  open_boc_fair: props.rule?.open_boc_fair ?? '',
  open_boc_fair_operator: props.rule?.open_boc_fair_operator ?? '',
  open_boc_poor: props.rule?.open_boc_poor ?? '',
  open_boc_poor_operator: props.rule?.open_boc_poor_operator ?? '',

  // Safety Bonus (no toggle)
  safety_bonus_eligible_levels: props.rule?.safety_bonus_eligible_levels ?? [],

  // VCR Preventable
  vcr_preventable_fantastic_plus: props.rule?.vcr_preventable_fantastic_plus ?? '',
  vcr_preventable_fantastic_plus_operator: props.rule?.vcr_preventable_fantastic_plus_operator ?? '',
  vcr_preventable_fantastic: props.rule?.vcr_preventable_fantastic ?? '',
  vcr_preventable_fantastic_operator: props.rule?.vcr_preventable_fantastic_operator ?? '',
  vcr_preventable_good: props.rule?.vcr_preventable_good ?? '',
  vcr_preventable_good_operator: props.rule?.vcr_preventable_good_operator ?? '',
  vcr_preventable_fair: props.rule?.vcr_preventable_fair ?? '',
  vcr_preventable_fair_operator: props.rule?.vcr_preventable_fair_operator ?? '',
  vcr_preventable_poor: props.rule?.vcr_preventable_poor ?? '',
  vcr_preventable_poor_operator: props.rule?.vcr_preventable_poor_operator ?? '',
})

const submit = () => {
  form.put(route('performancemetrics.update',tenantSlug))
}

const fieldKeys = Object.keys(form.data())

const sections = [
  'acceptance',
  'on_time',
  'maintenance_variance',
  'open_boc',
  'vcr_preventable',
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="p-6 max-w-5xl mx-auto">
      <h1 class="text-2xl font-bold mb-4">Edit Performance Metrics</h1>

      <form @submit.prevent="submit" class="space-y-8">
        <!-- Metric Sections -->
        <div v-for="section in sections" :key="section">
          <h2 class="text-lg font-semibold mb-4 capitalize">{{ section.replace(/_/g, ' ') }}</h2>

          <div class="grid grid-cols-2 gap-6">
            <div
              v-for="key in fieldKeys.filter(k => k.startsWith(section))"
              :key="key"
            >
              <label :for="key" class="block mb-1 text-sm font-medium text-gray-700">
                {{ key }}
              </label>

              <select
                v-if="key.endsWith('_operator')"
                :id="key"
                v-model="form[key]"
                class="w-full border-gray-300 rounded shadow-sm"
              >
                <option v-for="op in operators" :key="op.value" :value="op.value">
                  {{ op.label }}
                </option>
              </select>

              <input
                v-else
                :id="key"
                v-model="form[key]"
                type="text"
                class="w-full border-gray-300 rounded shadow-sm"
                :placeholder="form[key] === '' ? 'N/A' : ''"
              />
            </div>
          </div>
        </div>

        <!-- Safety Bonus Criteria -->
        <div>
          <h2 class="text-lg font-semibold mb-4">Safety Bonus Eligibility</h2>
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">
                Eligible Levels
              </label>
              <div class="space-y-2">
                <label
                  v-for="level in ['fantastic_plus', 'fantastic', 'good' , 'fair' , 'poor']"
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
          </div>
        </div>

        <!-- Submit -->
        <div class="pt-6">
          <button
            type="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
            :disabled="form.processing"
          >
            Save
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
