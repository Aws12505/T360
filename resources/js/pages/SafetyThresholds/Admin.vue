<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import ThresholdForm from './Form.vue'
import { Head } from '@inertiajs/vue3';
import { 
  Card, CardHeader, CardTitle, CardContent,
  Table, TableHeader, TableBody, TableHead, TableRow, TableCell,
  Button,
  Badge
} from '@/components/ui';

/**
 * Props passed from the backend via Inertia.
 * - thresholds: Array of safety threshold objects.
 * - metrics: Array of available safety metrics.
 * - tenantSlug: Optional string if tenant-specific.
 */
const props = defineProps({
  thresholds: Array,
  metrics: Array,
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
  {
    title: 'Safety Thresholds',
    href: route('sms-coaching.edit', { tenantSlug }),
  },
]

// Reactive state to control the modal for editing thresholds.
const showForm = ref(false)
// Reactive state to hold the current threshold data for editing.
const editing = ref({})
// Flag to determine if we're creating a new threshold
const isCreating = ref(false)

// Map status to badge variants
const getStatusBadge = (status) => {
  switch(status) {
    case true:
      return 'success'
    case false:
      return 'destructive'
    default:
      return 'outline'
  }
}

// Function to open the threshold editor modal for editing.
function openEditor(threshold) {
  editing.value = threshold
  isCreating.value = false
  showForm.value = true
}

// Function to open the threshold editor modal for creating.
function openCreator() {
  editing.value = {
    metric_name: '',
    good_threshold: null,
    bad_threshold: null,
    good_enabled: true,
    bad_enabled: true
  }
  isCreating.value = true
  showForm.value = true
}

// Function to close the editor modal.
function closeEditor() {
  showForm.value = false
  editing.value = {}
}

// Function to format threshold values
function formatThreshold(value) {
  if (value === null || value === undefined) return '-'
  return value
}

// Function to get available metrics that don't have thresholds yet
const availableMetrics = computed(() => {
  if (!props.metrics || !props.thresholds) return []
  
  const existingMetrics = props.thresholds.map(t => t.metric_name)
  return props.metrics.filter(m => !existingMetrics.includes(m))
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head title="Safety Thresholds Management"/>
    <div class="container w-full md:max-w-3xl lg:max-w-4xl xl:max-w-6xl lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
      <!-- Header with title and Add button -->
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-foreground">
          Safety Thresholds
        </h1>
        <Button 
          @click="openCreator" 
          variant="default" 
          class="mt-4 sm:mt-0"
          v-if="availableMetrics.length > 0"
        >
          Add New Threshold
        </Button>
      </div>

      <!-- Show the threshold form modal if editing -->
      <div v-if="showForm">
        <ThresholdForm
          :tenantSlug="tenantSlug"
          :entry="editing" 
          :isCreating="isCreating"
          :availableMetrics="availableMetrics"
          @saved="closeEditor" 
          @cancel="closeEditor" 
        />
      </div>

      <!-- Display thresholds as a table when not editing -->
      <Card v-else-if="props.thresholds && props.thresholds.length > 0">
        <CardHeader>
          <CardTitle>Safety Thresholds</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Metric</TableHead>
                  <TableHead>Good Threshold</TableHead>
                  <TableHead>Good Enabled</TableHead>
                  <TableHead>Bad Threshold</TableHead>
                  <TableHead>Bad Enabled</TableHead>
                  <TableHead class="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="threshold in props.thresholds" :key="threshold.id">
                  <TableCell class="capitalize">{{ threshold.metric_name.replace(/_/g, ' ') }}</TableCell>
                  <TableCell>{{ formatThreshold(threshold.good_threshold) }}</TableCell>
                  <TableCell>
                    <Badge :variant="getStatusBadge(threshold.good_enabled)">
                      {{ threshold.good_enabled ? 'Enabled' : 'Disabled' }}
                    </Badge>
                  </TableCell>
                  <TableCell>{{ formatThreshold(threshold.bad_threshold) }}</TableCell>
                  <TableCell>
                    <Badge :variant="getStatusBadge(threshold.bad_enabled)">
                      {{ threshold.bad_enabled ? 'Enabled' : 'Disabled' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <Button 
                      variant="outline" 
                      size="sm"
                      @click="openEditor(threshold)"
                    >
                      Edit
                    </Button>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
      
      <!-- Empty state when no thresholds exist -->
      <div v-else class="text-center py-12">
        <h3 class="text-lg font-medium text-muted-foreground mb-4">No safety thresholds defined yet</h3>
        <Button @click="openCreator" variant="default" v-if="availableMetrics.length > 0">
          Create Your First Threshold
        </Button>
      </div>
    </div>
  </AppLayout>
</template>