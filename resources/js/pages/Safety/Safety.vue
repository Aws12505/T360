<template>
  <!-- Main layout wrapped in AppLayout with breadcrumbs and tenantSlug props -->
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Success message notification -->
      <p v-if="successMessage" class="bg-green-100 text-green-800 border border-green-300 px-4 py-2 rounded">
        {{ successMessage }}
      </p>

      <!-- Actions Section -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <!-- Create New Entry button using ShadCN Button component -->
        <Button 
          @click="openCreateModal" 
          variant="default"
          class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow transition">
          Create New Entry
        </Button>

        <!-- Tenant selection for SuperAdmin (only visible if SuperAdmin) -->
        <div v-if="SuperAdmin">
          <label class="block text-sm font-medium mb-1">Tenant for Import</label>
          <select v-model="importForm.tenant_id" class="w-full border rounded px-3 py-2">
            <option disabled value="">Select Tenant</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">{{ tenant.name }}</option>
          </select>
        </div>

        <!-- Date input for the import file -->
        <div>
          <label class="block text-sm font-medium mb-1">Date for Imported XLSX</label>
          <input
            v-model="importForm.date"
            type="date"
            required
            class="w-full border rounded px-3 py-2"
          />
        </div>

        <!-- Import XLSX button styled as a label with hidden file input -->
        <label class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow cursor-pointer transition">
          Import XLSX
          <input type="file" class="hidden" @change="handleImport" accept=".xlsx" />
        </label>

        <!-- Export CSV button using ShadCN Button component -->
        <Button 
          @click.prevent="exportCSV" 
          variant="default"
          class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded shadow transition">
          Export CSV
        </Button>
      </div>

      <!-- Data Table Section -->
      <div class="overflow-x-auto shadow rounded-lg">
        <table class="min-w-full table-auto text-sm">
          <thead class="bg-gray-100">
            <tr>
              <!-- If SuperAdmin, show Tenant column -->
              <th v-if="SuperAdmin" class="px-4 py-2">Tenant</th>
              <!-- Dynamically render table columns from the tableColumns array -->
              <th
                v-for="col in tableColumns"
                :key="col"
                class="px-4 py-2 capitalize whitespace-nowrap"
              >
                {{ col.replace(/_/g, ' ') }}
              </th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in entries.data" :key="item.id">
              <!-- Display Tenant name for SuperAdmin -->
              <td v-if="SuperAdmin" class="px-4 py-2">{{ item.tenant?.name ?? '—' }}</td>
              <!-- Render each field for the entry -->
              <td
                v-for="col in tableColumns"
                :key="col"
                class="px-4 py-2 whitespace-nowrap"
              >
                {{ item[col] }}
              </td>
              <!-- Actions for each entry -->
              <td class="px-4 py-2 space-x-2">
                <!-- Edit button -->
                <button @click="openEditModal(item)" class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded">
                  Edit
                </button>
                <!-- Delete button -->
                <button @click="deleteEntry(item.id)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-center" v-if="entries.links">
      <button
        v-for="link in entries.links"
        :key="link.label"
        @click="visitPage(link.url)"
        :disabled="!link.url"
        class="mx-1 px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50"
      >
        <span v-html="link.label"></span>
      </button>
    </div>
      </div>

      <!-- Modal for creating/editing an entry -->
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-4xl space-y-6 overflow-y-auto max-h-screen">
          <h2 class="text-xl font-semibold">{{ formTitle }}</h2>
          <form @submit.prevent="submitForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tenant dropdown for SuperAdmin users -->
            <div v-if="SuperAdmin" class="col-span-2">
              <label class="block text-sm font-medium">Tenant</label>
              <select v-model="form.tenant_id" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select Tenant</option>
                <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                  {{ tenant.name }}
                </option>
              </select>
            </div>
            <!-- Dynamically render form fields based on formColumns -->
            <template v-for="col in formColumns" :key="col">
              <div>
                <label class="block text-sm font-medium capitalize">
                  {{ col.replace(/_/g, ' ') }}
                </label>
                <input
                  v-model="form[col]"
                  :type="getInputType(col)"
                  :step="getStep(col)"
                  :min="getMin(col)"
                  class="w-full border rounded px-3 py-2"
                />
              </div>
            </template>
            <!-- Action buttons for form submission and closing -->
            <div class="col-span-2 flex justify-end gap-3 mt-4">
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                {{ formAction }}
              </button>
              <button type="button" @click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-black font-semibold px-4 py-2 rounded">
                Close
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Hidden form for CSV export -->
      <form ref="exportForm" method="GET" class="hidden" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {router} from '@inertiajs/vue3';
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
</script>
