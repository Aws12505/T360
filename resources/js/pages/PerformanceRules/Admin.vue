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
  permissions: {
  type: Array,
   default: () => []
  },
  TenantMetricsRules: {
   type: Array,
   default: () => []
  },
  tenants: {
   type: Array,
   default: () => []
  }
})
const { tenantSlug } = props

// Define breadcrumbs for the layout.
const breadcrumbs = [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? route('dashboard', { tenantSlug }) : route('admin.dashboard'),
  },
  {
    title: 'Metrics',
    href: route('performance-metrics.edit'),
  },
]

// Reactive state to control the modal for editing global metrics.
const showForm = ref(false)
// Reactive state to hold the current metrics data for editing.
const editing = ref({})

// List of metrics and levels for display.
const metricsList = ['acceptance', 'on_time', 'maintenance_variance', 'open_boc', 'vcr_preventable', 'vmcr_p']
const levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor']

// List of safety metrics and tiers for display
const safetyMetricsList = [
  'driver_distraction',
  'speeding_violation',
  'sign_violation',
  'traffic_light_violation',
  'following_distance',
  'roadside_parking'
]
const safetyTiers = ['gold', 'silver', 'not_eligible']

// Map operator codes to human-readable labels
const operatorLabels = {
  'less': 'Less than',
  'less_or_equal': 'Less than or equal to',
  'equal': 'Equal to',
  'more_or_equal': 'More than or equal to',
  'more': 'More than'
}

// Compute a display object from the metrics data to show each metric as a card.
const displayMetrics = computed(() => {
  if (!props.metrics) return {}
  const data = {}
  
  // Process regular metrics
  metricsList.forEach(metric => {
    data[metric] = levels.map(level => {
      const operatorCode = props.metrics[`${metric}_${level}_operator`] ?? '-'
      return {
        level: level.replace(/_/g, ' '),
        value: props.metrics[`${metric}_${level}`] ?? '-',
        operator: operatorLabels[operatorCode] || operatorCode,
      }
    })
  })
  
  // Process safety metrics
  safetyMetricsList.forEach(metric => {
    data[metric] = safetyTiers.map(tier => {
      const operatorCode = props.metrics[`${metric}_${tier}_operator`] ?? '-'
      return {
        level: tier.replace(/_/g, ' '),
        value: props.metrics[`${metric}_${tier}`] ?? '-',
        operator: operatorLabels[operatorCode] || operatorCode,
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

// Safe tenant handling
const selectedTenantId = ref(props.tenants?.[0]?.id || null)

// Computed MVTS config with fallback
const currentMvtsConfig = computed(() => {
  if (!Array.isArray(props.TenantMetricsRules) || !selectedTenantId.value) {
    return props.metrics || {}
  }
  return props.TenantMetricsRules.find(rule => rule.tenant_id === selectedTenantId.value) || props.metrics || {}
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="props.permissions">
    <Head title="Metrics Management"/>
    <div class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
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
        <EntryForm
  :entry="editing"
  :tenants="tenants"
  :metrics="metrics"
  :TenantMetricsRules="TenantMetricsRules"
  @saved="closeEditor"
  @cancel="closeEditor"
/>      </div>

      <!-- Display metrics as cards when not editing -->
      <div v-else-if="props.metrics" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Regular  -->
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

        <!-- Safety Metrics -->
        <Card v-for="metric in safetyMetricsList" :key="metric">
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
                    <TableHead>Tier</TableHead>
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

        <Card class="col-span-1 md:col-span-2">
  <CardHeader class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <CardTitle>MVtS Configuration</CardTitle>
    <!-- Tenant Selector -->
    <select
      v-if="tenants?.length"
      v-model="selectedTenantId"
      class="p-2 rounded-md border border-input bg-background text-sm ring-offset-background
             file:border-0 file:bg-transparent file:text-sm file:font-medium
             placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2
             focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed
             disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white
             max-w-[200px]"
    >
      <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
        {{ tenant.name }}
      </option>
    </select>
  </CardHeader>
  <CardContent>
    <div class="flex flex-col space-y-2">
      <div class="flex items-center justify-between">
        <span class="font-medium text-foreground">MVtS Divisor:</span>
        <span class="text-muted-foreground">{{ currentMvtsConfig?.mvts_divisor || '0.135' }}</span>
      </div>
      <p class="text-sm text-muted-foreground">
        This value is used to calculate the Maintenance Variance to Standard (MVtS) metric.
      </p>
    </div>
  </CardContent>
</Card>
        
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
