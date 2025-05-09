<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
// Import ShadCN UI components
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
// Remove Select component imports
import { Switch } from '@/components/ui/switch'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Loader2 } from 'lucide-vue-next'

/**
 * Props:
 * - entry: Object containing the current threshold values.
 * - isCreating: Boolean indicating if we're creating a new threshold.
 * - availableMetrics: Array of available metrics for new thresholds.
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
  isCreating: {
    type: Boolean,
    default: false
  },
  availableMetrics: {
    type: Array,
    default: () => []
  },
  tenantSlug: {
    type: String,
    default: null,
  },
})
const emit = defineEmits(['saved', 'cancel'])
const { tenantSlug } = props

// Initialize form with default values using Inertia's useForm helper.
const form = useForm({
  id: props.entry.id,
  metric_name: props.entry.metric_name || '',
  good_threshold: props.entry.good_threshold ?? null,
  bad_threshold: props.entry.bad_threshold ?? null,
  good_enabled: props.entry.good_enabled ?? true,
  bad_enabled: props.entry.bad_enabled ?? true,
})

// Computed property for form title
const formTitle = computed(() => {
  return props.isCreating ? 'Create New Threshold' : 'Edit Threshold'
})

// Function to submit the form data to the backend.
const submit = () => {
  const routeName = 'sms-coaching.update'
    
  form.post(route(routeName, {tenantSlug}), {
    onSuccess: () => emit('saved'),
  })
}

// Format metric name for display
const formatMetricName = (name) => {
  return name.replace(/_/g, ' ')
}
</script>

<template>
  <Card class="shadow-sm">
    <CardHeader>
      <CardTitle>{{ formTitle }}</CardTitle>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Metric Name Field -->
        <div class="space-y-2">
          <Label for="metric_name">Metric Name</Label>
          <div v-if="isCreating">
            <!-- Replace Select component with standard HTML select -->
            <select
              id="metric_name"
              v-model="form.metric_name"
              required
              class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground dark:text-white shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <option value="" disabled>Select a metric</option>
              <option 
                v-for="metric in availableMetrics" 
                :key="metric" 
                :value="metric"
                class="capitalize"
              >
                {{ formatMetricName(metric) }}
              </option>
            </select>
          </div>
          <div v-else class="text-lg font-medium capitalize text-foreground">
            {{ formatMetricName(form.metric_name) }}
          </div>
        </div>
        
        <!-- Good Threshold Section -->
        <div class="space-y-4 p-4 border rounded-md border-border bg-card">
          <div class="flex items-center justify-between">
            <Label for="good_enabled" class="text-base font-medium text-foreground">Good Threshold</Label>
            <Switch 
              id="good_enabled"
              v-model="form.good_enabled"
            />
          </div>
          
          <div class="space-y-2" v-if="form.good_enabled">
            <Label for="good_threshold" class="text-foreground">Value</Label>
            <Input
              id="good_threshold"
              v-model="form.good_threshold"
              type="number"
              step="0.01"
              placeholder="Enter threshold value"
              class="w-full"
            />
            <p class="text-sm text-muted-foreground">
              Values less than or equal to this threshold will be considered "good".
            </p>
          </div>
        </div>
        
        <!-- Bad Threshold Section -->
        <div class="space-y-4 p-4 border rounded-md border-border bg-card">
          <div class="flex items-center justify-between">
            <Label for="bad_enabled" class="text-base font-medium text-foreground">Bad Threshold</Label>
            <Switch 
              id="bad_enabled"
              v-model="form.bad_enabled"
            />
          </div>
          
          <div class="space-y-2" v-if="form.bad_enabled">
            <Label for="bad_threshold" class="text-foreground">Value</Label>
            <Input
              id="bad_threshold"
              v-model="form.bad_threshold"
              type="number"
              step="0.01"
              placeholder="Enter threshold value"
              class="w-full"
            />
            <p class="text-sm text-muted-foreground">
              Values more than or equal to this threshold will be considered "bad".
            </p>
          </div>
        </div>

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
    </CardContent>
  </Card>
</template>