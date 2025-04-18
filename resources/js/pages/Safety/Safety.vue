<template>
  <!-- Main layout wrapped in AppLayout with breadcrumbs and tenantSlug props -->
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head title="Safety"/>
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
          
          <!-- Delete Selected button - only shows when items are selected -->
          <Button 
            v-if="selectedEntries.length > 0" 
            @click="confirmDeleteSelected()" 
            variant="destructive"
          >
            <Icon name="trash" class="mr-2 h-4 w-4" />
            Delete Selected ({{ selectedEntries.length }})
          </Button>

          <!-- Tenant selection for SuperAdmin (only visible if SuperAdmin) -->
          <div v-if="SuperAdmin" class="flex items-center gap-2">
            <select v-model="importForm.tenant_id" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm">
              <option disabled value="">Select Company Name</option>
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
              Upload XLSX
            </Button>
            <input type="file" class="hidden" @change="handleImport" accept=".xlsx" />
          </label>

          <!-- Export CSV button -->
          <Button @click.prevent="exportCSV" variant="outline">
            <Icon name="download" class="mr-2 h-4 w-4" />
            Download CSV
          </Button>
        </div>
      </div>

      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-4">
          <div class="flex flex-col gap-2">
            <div class="flex flex-wrap gap-2">
              <Button 
                @click="selectDateFilter('yesterday')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'yesterday'}"
              >
                Yesterday
              </Button>
              <Button 
                @click="selectDateFilter('current-week')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'current-week'}"
              >
                Current Week
              </Button>
              <Button 
                @click="selectDateFilter('6w')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === '6w'}"
              >
                6 Weeks
              </Button>
              <Button 
                @click="selectDateFilter('quarterly')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'quarterly'}"
              >
                Quarterly
              </Button>
              <Button 
                @click="selectDateFilter('full')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'full'}"
              >
                Full
              </Button>
            </div>
            
            <!-- Date range info and Freeze Column toggle in separate row -->
            <div class="flex justify-between items-center">
              <div v-if="dateRange" class="text-sm text-muted-foreground">
                <span v-if="activeTab === 'yesterday' && dateRange.start">
                  Showing data from {{ formatDate(dateRange.start) }}
                </span>
                <span v-else-if="dateRange.start && dateRange.end">
                  Showing data from {{ formatDate(dateRange.start) }} to {{ formatDate(dateRange.end) }}
                </span>
                <span v-else>
                  {{ dateRange.label }}
                </span>
              </div>
              
              <!-- Toggle Freeze Column Button -->
              <Button 
                @click="toggleFreezeColumn" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': freezeColumns}"
              >
                <Icon :name="freezeColumns ? 'lock' : 'unlock'" class="mr-2 h-4 w-4" />
                {{ freezeColumns ? 'Unfreeze Names' : 'Freeze Names' }}
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Data Table Section -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto bg-background dark:bg-background border-t border-border">
            <Table class="relative h-[500px] overflow-auto">
              <TableHeader>
                <TableRow class="sticky top-0 bg-background border-b z-10 hover:bg-background">
                  <!-- Checkbox column for selecting all -->
                  <TableHead class="w-[50px]" :class="{ 'sticky left-0 z-20 bg-background': freezeColumns }">
                    <div class="flex items-center justify-center">
                      <input 
                        type="checkbox" 
                        @change="toggleSelectAll" 
                        :checked="isAllSelected"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                      />
                    </div>
                  </TableHead>
                  <!-- If SuperAdmin, show Tenant column -->
                  <TableHead v-if="SuperAdmin" :class="{ 'sticky left-[50px] z-20 bg-background': freezeColumns }">Company Name</TableHead>
                  <!-- Dynamically render table columns from the tableColumns array -->
                  <TableHead
                    v-for="col in tableColumns"
                    :key="col"
                    class="whitespace-nowrap"
                    :class="{
                      'sticky z-20 bg-background': freezeColumns && col === 'driver_name',
                      'left-[50px]': freezeColumns && col === 'driver_name' && !SuperAdmin, 
                      'left-[150px]': freezeColumns && col === 'driver_name' && SuperAdmin
                    }"
                  >
                    {{ col.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}
                  </TableHead>
                  <!-- Actions column - removed freezing -->
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="entries.data.length === 0">
                  <TableCell :colspan="SuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2" class="text-center py-8">
                    No entries found
                  </TableCell>
                </TableRow>
                <TableRow v-for="item in entries.data" :key="item.id" class="hover:bg-muted/50">
                  <!-- Checkbox for selecting individual row -->
                  <TableCell class="text-center" :class="{ 'sticky left-0 z-10 bg-background': freezeColumns }">
                    <input 
                      type="checkbox" 
                      :value="item.id" 
                      v-model="selectedEntries"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                  </TableCell>
                  <!-- Display Tenant name for SuperAdmin -->
                  <TableCell v-if="SuperAdmin" :class="{ 'sticky left-[50px] z-10 bg-background': freezeColumns }">{{ item.tenant?.name ?? '—' }}</TableCell>
                  <!-- Render each field for the entry -->
                  <TableCell
                    v-for="col in tableColumns"
                    :key="col"
                    class="whitespace-nowrap"
                    :class="{
                      'sticky z-10 bg-background': freezeColumns && col === 'driver_name',
                      'left-[50px]': freezeColumns && col === 'driver_name' && !SuperAdmin, 
                      'left-[150px]': freezeColumns && col === 'driver_name' && SuperAdmin
                    }"
                  >
                    {{ item[col] }}
                  </TableCell>
                  <!-- Actions for each entry - removed freezing -->
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
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                  <Label for="perPage" class="text-sm">Per page:</Label>
                  <select 
                    id="perPage" 
                    v-model="perPage" 
                    @change="changePerPage"
                    class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
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
          </div>
        </CardContent>
      </Card>

      <!-- Modal for creating/editing an entry -->
      <Dialog v-model:open="showModal">
        <DialogContent class="sm:max-w-lg md:max-w-2xl lg:max-w-4xl w-[90vw]">
          <DialogHeader>
            <DialogTitle>{{ formTitle }}</DialogTitle>
            <DialogDescription>
              Fill in the details to {{ formAction.toLowerCase() }} an entry.
            </DialogDescription>
          </DialogHeader>
          
          <form @submit.prevent="submitForm" class="grid gap-6 max-h-[70vh] overflow-y-auto p-1">
            <!-- Tenant dropdown for SuperAdmin users -->
            <div v-if="SuperAdmin" class="col-span-full">
              <Label for="tenant">Company Name</Label>
              <div class="relative">
                <select 
                  id="tenant" 
                  v-model="form.tenant_id" 
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
                >
                  <option disabled value="">Select Company</option>
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
            
            <!-- Date field (important, so keep it separate at the top) -->
            <div class="col-span-full">
              <Label for="date" class="font-medium">Date</Label>
              <Input
                id="date"
                v-model="form.date"
                type="date"
                required
                class="w-full"
              />
            </div>
            
            <!-- Group fields into sections for better organization -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
              <!-- Driver Information Section -->
              <div class="col-span-full mb-2">
                <h3 class="text-md font-semibold border-b pb-1">Driver Information</h3>
              </div>
              
              <div v-for="col in ['driver_name', 'user_name', 'group', 'group_hierarchy', 'vehicle_type']" :key="col" 
                   class="space-y-1">
                <Label :for="col" class="capitalize">{{ col.replace(/_/g, ' ') }}</Label>
                <Input
                  :id="col"
                  v-model="form[col]"
                  :type="getInputType(col)"
                  :step="getStep(col)"
                  :min="getMin(col)"
                />
              </div>
              
              <!-- Performance Metrics Section -->
              <div class="col-span-full mt-4 mb-2">
                <h3 class="text-md font-semibold border-b pb-1">Performance Metrics</h3>
              </div>
              
              <div v-for="col in ['minutes_analyzed', 'green_minutes_percent', 'overspeeding_percent', 'driver_score', 'safety_normalisation_factor']" :key="col" 
                   class="space-y-1">
                <Label :for="col" class="capitalize">{{ col.replace(/_/g, ' ') }}</Label>
                <Input
                  :id="col"
                  v-model="form[col]"
                  :type="getInputType(col)"
                  :step="getStep(col)"
                  :min="getMin(col)"
                />
              </div>
              
              <!-- Events Section -->
              <div class="col-span-full mt-4 mb-2">
                <h3 class="text-md font-semibold border-b pb-1">Events & Violations</h3>
              </div>
              
              <!-- Remaining fields in a grid -->
              <div v-for="col in formColumns.filter(c => 
                !['driver_name', 'user_name', 'group', 'group_hierarchy', 'vehicle_type', 
                  'minutes_analyzed', 'green_minutes_percent', 'overspeeding_percent', 'driver_score', 
                  'safety_normalisation_factor', 'date'].includes(c))" 
                :key="col" 
                class="space-y-1">
                <Label :for="col" class="capitalize text-sm">{{ col.replace(/_/g, ' ') }}</Label>
                <Input
                  :id="col"
                  v-model="form[col]"
                  :type="getInputType(col)"
                  :step="getStep(col)"
                  :min="getMin(col)"
                />
              </div>
            </div>
            
            <DialogFooter class="flex-col space-y-2 sm:space-y-0 sm:flex-row sm:justify-end sm:space-x-2 mt-6 pt-4 border-t">
              <Button type="button" @click="closeModal" variant="outline" class="w-full sm:w-auto">
                Cancel
              </Button>
              <Button type="submit" variant="default" class="w-full sm:w-auto">
                {{ formAction }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
<!-- Delete Selected Entries Confirmation Dialog -->
<Dialog v-model:open="showDeleteSelectedModal">
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Confirm Bulk Deletion</DialogTitle>
      <DialogDescription>
        Are you sure you want to delete {{ selectedEntries.length }} safety records? This action cannot be undone.
      </DialogDescription>
    </DialogHeader>
    <DialogFooter class="mt-4">
      <Button type="button" @click="showDeleteSelectedModal = false" variant="outline">
        Cancel
      </Button>
      <Button type="button" @click="deleteSelectedEntries()" variant="destructive">
        Delete Selected
      </Button>
    </DialogFooter>
  </DialogContent>
</Dialog>
      <!-- Hidden form for CSV export -->
      <form ref="exportForm" method="GET" class="hidden" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, Head } from '@inertiajs/vue3';
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
  dateRange: Object,
  dateFilter: {
    type: String,
    default: 'full'
  }
});

// Reactive state variables
const successMessage = ref('');
const showModal = ref(false);
const formTitle = ref('Create Entry');
const formAction = ref('Create');
const exportForm = ref(null);
const activeTab = ref(props.dateFilter || 'full');
const perPage = ref(10);
const selectedEntries = ref([]);
const showDeleteSelectedModal = ref(false);
const freezeColumns = ref(false); // Changed to false by default

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
    href: props.tenantSlug
     ? route('safety.index', { tenantSlug: props.tenantSlug }) : route('safety.index.admin')
  }
];

// Toggle column freezing function
function toggleFreezeColumn() {
  freezeColumns.value = !freezeColumns.value;
}

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

// Remove this duplicate visitPage function
// const visitPage = (url) => {
//   if (url) {
//     router.get(url, {}, {only: ['entries'] });
//   }
// };

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  }
});

// Function to handle date filter selection
function selectDateFilter(filter) {
  activeTab.value = filter;
  
  const routeName = props.tenantSlug 
    ? route('safety.index', { tenantSlug: props.tenantSlug }) 
    : route('safety.index.admin');
    
  router.get(routeName, { 
    dateFilter: filter,
    perPage: perPage.value 
  }, { preserveState: true });
}

// Function to handle per page change
function changePerPage() {
  const routeName = props.tenantSlug 
    ? route('safety.index', { tenantSlug: props.tenantSlug }) 
    : route('safety.index.admin');
    
  router.get(routeName, { 
    dateFilter: activeTab.value,
    perPage: perPage.value 
  }, { preserveState: true });
}

// Format date string helper function
function formatDate(dateStr) {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}

// Update visitPage to preserve perPage and dateFilter
function visitPage(url) {
  if (url) {
    // Add perPage and dateFilter parameters to the URL
    const urlObj = new URL(url);
    urlObj.searchParams.set('perPage', perPage.value);
    urlObj.searchParams.set('dateFilter', activeTab.value);
    
    router.get(urlObj.href, {}, { only: ['entries'] });
  }
}

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
  return props.entries.data.length > 0 && selectedEntries.value.length === props.entries.data.length;
});

// Bulk selection functions
function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedEntries.value = props.entries.data.map(entry => entry.id);
  } else {
    selectedEntries.value = [];
  }
}

function confirmDeleteSelected() {
  if (selectedEntries.value.length > 0) {
    showDeleteSelectedModal.value = true;
  }
}

function deleteSelectedEntries() {
  const form = useForm({
    ids: selectedEntries.value
  });
  
  const routeName = props.SuperAdmin ? 'safety.destroyBulk.admin' : 'safety.destroyBulk';
  const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };
  
  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = `${selectedEntries.value.length} safety records deleted successfully.`;
      selectedEntries.value = [];
      showDeleteSelectedModal.value = false;
    },
    onError: (errors) => {
      console.error(errors);
    }
  });
}
</script>

<style scoped>
/* Ensure proper table scrolling behavior */
:deep(.overflow-x-auto) {
  position: relative;
}

:deep(table) {
  border-collapse: separate;
  border-spacing: 0;
}

/* Add shadow effect to frozen columns for better visual separation */
:deep(.sticky) {
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

/* Ensure the table container has proper overflow handling */
:deep(.h-[500px]) {
  overflow: auto;
  position: relative;
}

/* Add column dividers that work in both light and dark mode */
:deep(th), :deep(td) {
  border-right: 1px solid hsl(var(--border));
}

:deep(th:last-child), :deep(td:last-child) {
  border-right: none;
}
</style>


