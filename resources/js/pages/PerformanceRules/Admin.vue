<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import EntryForm from './Form.vue'  // Global metrics form component
import { Head } from '@inertiajs/vue3';
import { 
  Card, CardHeader, CardTitle, CardContent,
  Table, TableHeader, TableBody, TableHead, TableRow, TableCell,
  Button,
  Badge
} from '@/components/ui';

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
    <Head title="Metrics Management"/>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header with title and Edit button -->
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-foreground">
          Performance Metrics
        </h1>
        <Button 
          @click="openEditor" 
          variant="default" 
          class="mt-4 sm:mt-0"
        >
          Edit Global Metrics
        </Button>
      </div>

      <!-- Show the metrics form modal if editing -->
      <div v-if="showForm">
        <EntryForm :entry="editing" @saved="closeEditor" @cancel="closeEditor" />
      </div>

      <!-- Display metrics as cards when not editing -->
      <div v-else-if="props.metrics" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card v-for="metric in metricsList" :key="metric">
          <CardHeader>
            <CardTitle class="capitalize">
              {{ metric.replace(/_/g, ' ') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Level</TableHead>
                    <TableHead>Value</TableHead>
                    <TableHead>Operator</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow 
                    v-for="(item, idx) in displayMetrics[metric]" 
                    :key="idx"
                  >
                    <TableCell>{{ item.level }}</TableCell>
                    <TableCell>{{ item.value }}</TableCell>
                    <TableCell>{{ item.operator }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>

        <!-- Safety Bonus Eligibility Card -->
        <Card 
          v-if="displayMetrics.safety_bonus_eligible_levels.length" 
          class="col-span-1 md:col-span-2"
        >
          <CardHeader>
            <CardTitle>
              Safety Bonus Eligibility
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex flex-wrap gap-2">
              <Badge 
                v-for="(level, index) in displayMetrics.safety_bonus_eligible_levels"
                :key="index"
                variant="success"
                class="capitalize"
              >
                {{ level.replace(/_/g, ' ') }}
              </Badge>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
