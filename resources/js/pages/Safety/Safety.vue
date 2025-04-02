<template>
  <!-- Main layout wrapped in AppLayout with breadcrumbs and tenantSlug props -->
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Success message notification -->
      <Alert v-if="successMessage" variant="success">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>

      <!-- Actions Section -->
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Safety Management</h1>
        <div class="flex flex-wrap gap-3">
          <!-- Create New Entry button -->
          <Button @click="openCreateModal" variant="default">
            <Icon name="plus" class="mr-2 h-4 w-4" />
            Create New Entry
          </Button>

          <!-- Tenant selection for SuperAdmin (only visible if SuperAdmin) -->
          <div v-if="SuperAdmin" class="flex items-center gap-2">
            <select v-model="importForm.tenant_id" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
              <option disabled value="">Select Tenant</option>
              <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">{{ tenant.name }}</option>
            </select>
          </div>

          <!-- Date input for the import file -->
          <div class="flex items-center gap-2">
            <Input
              v-model="importForm.date"
              type="date"
              required
              placeholder="Date for Import"
            />
          </div>

          <!-- Import XLSX button -->
          <label class="cursor-pointer">
            <Button variant="secondary" as="span">
              <Icon name="upload" class="mr-2 h-4 w-4" />
              Import XLSX
            </Button>
            <input type="file" class="hidden" @change="handleImport" accept=".xlsx" />
          </label>

          <!-- Export CSV button -->
          <Button @click.prevent="exportCSV" variant="outline">
            <Icon name="download" class="mr-2 h-4 w-4" />
            Export CSV
          </Button>
        </div>
      </div>

      <!-- Data Table Section -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto bg-background dark:bg-background border-t border-border">
            <Table>
              <TableHeader>
                <TableRow>
                  <!-- If SuperAdmin, show Tenant column -->
                  <TableHead v-if="SuperAdmin">Tenant</TableHead>
                  <!-- Dynamically render table columns from the tableColumns array -->
                  <TableHead
                    v-for="col in tableColumns"
                    :key="col"
                    class="whitespace-nowrap"
                  >
                    {{ col.replace(/_/g, ' ') }}
                  </TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="entries.data.length === 0">
                  <TableCell :colspan="SuperAdmin ? tableColumns.length + 2 : tableColumns.length + 1" class="text-center py-8">
                    No entries found
                  </TableCell>
                </TableRow>
                <TableRow v-for="item in entries.data" :key="item.id" class="hover:bg-muted/50">
                  <!-- Display Tenant name for SuperAdmin -->
                  <TableCell v-if="SuperAdmin">{{ item.tenant?.name ?? '—' }}</TableCell>
                  <!-- Render each field for the entry -->
                  <TableCell
                    v-for="col in tableColumns"
                    :key="col"
                    class="whitespace-nowrap"
                  >
                    {{ item[col] }}
                  </TableCell>
                  <!-- Actions for each entry -->
                  <TableCell>
                    <div class="flex space-x-2">
                      <Button @click="openEditModal(item)" variant="warning" size="sm">
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>
                      <Button @click="deleteEntry(item.id)" variant="destructive" size="sm">
                        <Icon name="trash" class="mr-1 h-4 w-4" />
                        Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <!-- Pagination -->
          <div class="bg-muted/20 px-4 py-3 border-t" v-if="entries.links">
            <div class="flex justify-between items-center">
              <div class="text-sm text-muted-foreground">
                Showing {{ entries.data.length }} entries
              </div>
              <div class="flex">
                <Button 
                  v-for="link in entries.links" 
                  :key="link.label" 
                  @click="visitPage(link.url)" 
                  :disabled="!link.url" 
                  variant="ghost"
                  size="sm"
                  class="mx-1"
                  :class="{'bg-primary/10 text-primary border-primary': link.active}"
                >
                  <span v-html="link.label"></span>
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Modal for creating/editing an entry -->
      <Dialog v-model:open="showModal">
        <DialogContent class="sm:max-w-4xl">
          <DialogHeader>
            <DialogTitle>{{ formTitle }}</DialogTitle>
            <DialogDescription>
              Fill in the details to {{ formAction.toLowerCase() }} an entry.
            </DialogDescription>
          </DialogHeader>
          
          <form @submit.prevent="submitForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">
            <!-- Tenant dropdown for SuperAdmin users -->
            <div v-if="SuperAdmin" class="col-span-2">
              <Label for="tenant">Tenant</Label>
              <div class="relative">
                <select 
                  id="tenant" 
                  v-model="form.tenant_id" 
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
                >
                  <option disabled value="">Select Tenant</option>
                  <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                    {{ tenant.name }}
                  </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- Dynamically render form fields based on formColumns -->
            <template v-for="col in formColumns" :key="col">
              <div>
                <Label :for="col" class="capitalize">{{ col.replace(/_/g, ' ') }}</Label>
                <Input
                  :id="col"
                  v-model="form[col]"
                  :type="getInputType(col)"
                  :step="getStep(col)"
                  :min="getMin(col)"
                />
              </div>
            </template>
            
            <DialogFooter class="col-span-2 mt-4">
              <Button type="button" @click="closeModal" variant="outline">
                Cancel
              </Button>
              <Button type="submit" variant="default">
                {{ formAction }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <!-- Hidden form for CSV export -->
      <form ref="exportForm" method="GET" class="hidden" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import { 
  Button,
  Card, CardContent,
  Table, TableHeader, TableBody, TableHead, TableRow, TableCell,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Label, Input,
  Alert, AlertTitle, AlertDescription
} from '@/components/ui';

// Define props passed from the backend via Inertia
const props = defineProps({
  entries: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenantSlug: String,
  SuperAdmin: Boolean,
  tenants: Array,
});

// Reactive state variables
const successMessage = ref('');
const showModal = ref(false);
const formTitle = ref('Create Entry');
const formAction = ref('Create');
const exportForm = ref(null);

// Define breadcrumbs for the layout
const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard')
  },
  {
    title: 'Safety',
    href: '#',
  }
];

// Field definitions based on your migration
const fieldTypes = {
  driver_name: 'text',
  user_name: 'text',
  group: 'text',
  group_hierarchy: 'text',
  minutes_analyzed: 'decimal',
  green_minutes_percent: 'decimal',
  overspeeding_percent: 'decimal',
  driver_score: 'decimal',
  total_events_avg_fd_impact: 'decimal',
  average_following_distance_sec: 'decimal',
  average_following_distance_gz_impact: 'decimal',
  total_events: 'decimal',
  high_g: 'decimal',
  low_impact: 'decimal',
  driver_initiated: 'decimal',
  potential_collision: 'decimal',
  sign_violations: 'decimal',
  sign_violations_gz_impact: 'decimal',
  traffic_light_violation: 'decimal',
  traffic_light_violation_gz_impact: 'decimal',
  u_turn: 'decimal',
  u_turn_gz_impact: 'decimal',
  hard_braking: 'decimal',
  hard_braking_gz_impact: 'decimal',
  hard_turn: 'decimal',
  hard_turn_gz_impact: 'decimal',
  hard_acceleration: 'decimal',
  hard_acceleration_gz_impact: 'decimal',
  driver_distraction: 'decimal',
  driver_distraction_gz_impact: 'decimal',
  following_distance: 'decimal',
  following_distance_gz_impact: 'decimal',
  speeding_violations: 'decimal',
  speeding_violations_gz_impact: 'decimal',
  seatbelt_compliance: 'decimal',
  camera_obstruction: 'decimal',
  driver_drowsiness: 'decimal',
  weaving: 'decimal',
  weaving_gz_impact: 'decimal',
  collision_warning: 'decimal',
  collision_warning_gz_impact: 'decimal',
  requested_video: 'decimal',
  backing: 'decimal',
  roadside_parking: 'decimal',
  driver_distracted_hard_brake: 'decimal',
  following_distance_hard_brake: 'decimal',
  driver_distracted_following_distance: 'decimal',
  driver_star: 'decimal',
  driver_star_gz_impact: 'decimal',
  vehicle_type: 'text',
  safety_normalisation_factor: 'decimal',
  date: 'date'
};

// Derive formColumns and tableColumns from fieldTypes keys.
const formColumns = Object.keys(fieldTypes);
const tableColumns = [...formColumns];

// Initialize form state using Inertia's useForm helper.
const form = useForm({
  tenant_id: null,
  ...Object.fromEntries(formColumns.map(col => [col, ''])),
  id: null
});

// Initialize import form state.
const importForm = useForm({
  csv_file: null,
  date: '',
  tenant_id: null
});

// Initialize delete form state.
const deleteForm = useForm({});

// Helper functions to determine input type, step, and minimum value.
function getInputType(field) {
  const type = fieldTypes[field];
  if (type === 'decimal' || type === 'integer') return 'number';
  if (type === 'date') return 'date';
  return 'text';
}

function getStep(field) {
  const type = fieldTypes[field];
  if (type === 'decimal') return '0.01';
  if (type === 'integer') return '1';
  return null;
}

function getMin(field) {
  const type = fieldTypes[field];
  return type === 'decimal' || type === 'integer' ? '-99999' : null;
}

// Open the modal to create a new entry.
function openCreateModal() {
  form.reset();
  formTitle.value = 'Create Entry';
  formAction.value = 'Create';
  showModal.value = true;
}

// Open the modal to edit an existing entry.
function openEditModal(item) {
  formTitle.value = 'Edit Entry';
  formAction.value = 'Update';
  form.tenant_id = props.SuperAdmin ? item.tenant_id : null;
  form.id = item.id;
  formColumns.forEach(col => {
    form[col] = item[col];
  });
  showModal.value = true;
}

// Close the modal.
function closeModal() {
  showModal.value = false;
}

// Submit the form data to create or update an entry.
function submitForm() {
  const isCreate = formAction.value === 'Create';
  const routeName = isCreate
    ? props.SuperAdmin ? route('safety.store.admin') : route('safety.store', props.tenantSlug)
    : props.SuperAdmin ? route('safety.update.admin', [form.id]) : route('safety.update', [props.tenantSlug, form.id]);
  const method = isCreate ? 'post' : 'put';
  form[method](routeName, {
    onSuccess: () => {
      successMessage.value = isCreate ? 'Entry created.' : 'Entry updated.';
      closeModal();
    },
    onError: () => alert('Something went wrong.')
  });
}

// Delete an entry after confirmation.
function deleteEntry(id) {
  if (!confirm('Are you sure?')) return;
  const routeName = props.SuperAdmin
    ? route('safety.destroy.admin', [id])
    : route('safety.destroy', [props.tenantSlug, id]);
  deleteForm.delete(routeName, {
    onSuccess: () => successMessage.value = 'Entry deleted.'
  });
}

// Handle file import for XLSX file.
function handleImport(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  if (!importForm.date) {
    alert('Please select the date for this import.');
    return;
  }
  if (props.SuperAdmin && !importForm.tenant_id) {
    alert('Please select a tenant for import.');
    return;
  }
  importForm.csv_file = file;
  const routeName = props.SuperAdmin
    ? route('safety.import.admin')
    : route('safety.import', props.tenantSlug);
  importForm.post(routeName, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Data imported successfully.';
      importForm.reset();
    },
    onError: () => alert('Import failed.')
  });
}

// Export CSV by submitting a hidden form.
function exportCSV() {
  const routeName = props.SuperAdmin
    ? route('safety.export.admin')
    : route('safety.export', props.tenantSlug);
  exportForm.value?.setAttribute('action', routeName);
  exportForm.value?.submit();
}

const visitPage = (url) => {
  if (url) {
    router.get(url, {}, {only: ['entries'] });
  }
};

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  }
});
</script>
