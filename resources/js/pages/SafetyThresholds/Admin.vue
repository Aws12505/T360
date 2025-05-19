<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
// Import the layout
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
// Import the type for breadcrumbs
import { type BreadcrumbItem } from '@/types';

// Import ThresholdForm component
import ThresholdForm from './Form.vue';

// Import ShadCN UI components
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import { Search } from 'lucide-vue-next';
import HeadingSmall from '@/components/HeadingSmall.vue';

// Add these imports for enhanced UI components
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Plus, AlertTriangle, CheckCircle } from 'lucide-vue-next';

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
});
const { tenantSlug } = props;

// Make breadcrumbs reactive with computed property
const breadcrumbs = computed(() => [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'),
  },
]);

// Reactive state to control the modal for editing thresholds.
const showForm = ref(false);
// Reactive state to hold the current threshold data for editing.
const editing = ref({});
// Flag to determine if we're creating a new threshold
const isCreating = ref(false);

// Map status to badge variants
const getStatusBadge = (status) => {
  switch(status) {
    case true:
      return 'success';
    case false:
      return 'destructive';
    default:
      return 'outline';
  }
};

// Function to open the threshold editor modal for editing.
function openEditor(threshold) {
  editing.value = threshold;
  isCreating.value = false;
  showForm.value = true;
}

// Function to open the threshold editor modal for creating.
function openCreator() {
  editing.value = {
    metric_name: '',
    good_threshold: null,
    bad_threshold: null,
    good_enabled: true,
    bad_enabled: true
  };
  isCreating.value = true;
  showForm.value = true;
}

// Function to close the editor modal.
function closeEditor() {
  showForm.value = false;
  editing.value = {};
}

// Function to format threshold values
function formatThreshold(value) {
  if (value === null || value === undefined) return '-';
  return value;
}

// Function to get available metrics that don't have thresholds yet
const availableMetrics = computed(() => {
  if (!props.metrics || !props.thresholds) return [];
  
  const existingMetrics = props.thresholds.map(t => t.metric_name);
  return props.metrics.filter(m => !existingMetrics.includes(m));
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <!-- Set the page head -->
    <Head title="Safety Thresholds Management" />

    <SettingsLayout>
      <div class="flex flex-col space-y-6 w-full">
        <!-- Page header with title and description -->
        <HeadingSmall 
          title="Safety Thresholds" 
          description="Manage safety thresholds for driver coaching and alerts"
        />

        <Separator />

        <!-- Add New Threshold button -->
        <div class="flex justify-end">
          <Button
            @click="openCreator"
            variant="default"
            size="sm"
            class="gap-1"
            v-if="availableMetrics.length > 0"
          >
            <Plus class="h-4 w-4" />
            <span>Add New Threshold</span>
          </Button>
        </div>

        <!-- Thresholds Section -->
        <Card class="w-full">
          <CardHeader class="p-2 md:p-4 lg:p-6 flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-xl font-semibold">
              <div class="flex items-center space-x-2">
                <AlertTriangle class="h-5 w-5" />
                <span>Safety Thresholds</span>
              </div>
            </CardTitle>
          </CardHeader>
          <CardContent class="p-2 md:p-4 lg:p-6">
            <div v-if="props.thresholds && props.thresholds.length > 0" class="overflow-x-auto">
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
            
            <!-- Empty state when no thresholds exist -->
            <div v-else class="text-center py-12">
              <CheckCircle class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 class="text-lg font-medium text-muted-foreground mb-4">No safety thresholds defined yet</h3>
              <Button @click="openCreator" variant="default" v-if="availableMetrics.length > 0">
                Create Your First Threshold
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Threshold Form Modal -->
        <Transition name="fade">
          <div
            v-if="showForm"
            class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden"
          >
            <!-- gray overlay -->
            <div
              class="fixed inset-0 bg-black/50 backdrop-blur-sm"
              @click="closeEditor"
            />
            <!-- form sits on top -->
            <div class="relative z-10 mx-auto my-4 w-full max-w-md p-4 md:max-w-xl">
              <ThresholdForm
                :tenantSlug="tenantSlug"
                :entry="editing" 
                :isCreating="isCreating"
                :availableMetrics="availableMetrics"
                @saved="closeEditor" 
                @cancel="closeEditor" 
              />
            </div>
          </div>
        </Transition>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.98);
}
</style>