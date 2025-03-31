<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Success Message -->
      <p v-if="successMessage" class="bg-green-100 text-green-800 border border-green-300 px-4 py-2 rounded">
        {{ successMessage }}
      </p>

      <!-- Actions Section -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <Button @click="openCreateModal" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow transition">
          Create New Entry
        </Button>

        <div v-if="SuperAdmin">
          <label class="block text-sm font-medium mb-1">Tenant for Import</label>
          <select v-model="importForm.tenant_id" class="w-full border rounded px-3 py-2">
            <option disabled value="">Select Tenant</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
              {{ tenant.name }}
            </option>
          </select>
        </div>

        <label class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow cursor-pointer transition">
          Import CSV
          <input type="file" class="hidden" @change="handleImport" accept=".csv, .txt" />
        </label>

        <Button @click.prevent="exportCSV" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded shadow transition">
          Export CSV
        </Button>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto shadow rounded-lg">
        <table class="min-w-full table-auto text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th v-if="SuperAdmin" class="px-4 py-2">Tenant</th>
              <th v-for="col in tableColumns" :key="col" class="px-4 py-2 capitalize whitespace-nowrap">
                {{ col.replace(/_/g, ' ') }}
              </th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in entries.data" :key="item.id">
              <td v-if="SuperAdmin" class="px-4 py-2">
                {{ item.tenant?.name ?? '—' }}
              </td>
              <td v-for="col in tableColumns" :key="col" class="px-4 py-2 whitespace-nowrap">
                <template v-if="col === 'is_active'">
                  {{ item[col] ? 'Yes' : 'No' }}
                </template>
                <template v-else>
                  {{ item[col] }}
                </template>
              </td>
              <td class="px-4 py-2 space-x-2">
                <button @click="openEditModal(item)" class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded">
                  Edit
                </button>
                <button @click="deleteEntry(item.id)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        
        <div class="mt-4 flex justify-center" v-if="entries.links">
          <button v-for="link in entries.links" :key="link.label" @click="visitPage(link.url)" :disabled="!link.url" class="mx-1 px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50">
            <span v-html="link.label"></span>
          </button>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-4xl space-y-6 overflow-y-auto max-h-screen">
          <h2 class="text-xl font-semibold">{{ formTitle }}</h2>
          <form @submit.prevent="submitForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div v-if="SuperAdmin" class="col-span-2">
              <label class="block text-sm font-medium">Tenant</label>
              <select v-model="form.tenant_id" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select Tenant</option>
                <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                  {{ tenant.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium">Truck ID</label>
              <input v-model.number="form.truckid" type="number" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
              <label class="block text-sm font-medium">Type</label>
              <select v-model="form.type" class="w-full border rounded px-3 py-2" required>
                <option value="Daycab">Daycab</option>
                <option value="Sleepercab">Sleepercab</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium">Make</label>
              <select v-model="form.make" class="w-full border rounded px-3 py-2" required>
                <option value="International">International</option>
                <option value="Kenworth">Kenworth</option>
                <option value="Peterbilt">Peterbilt</option>
                <option value="Volvo">Volvo</option>
                <option value="Freightliner">Freightliner</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium">Fuel</label>
              <select v-model="form.fuel" class="w-full border rounded px-3 py-2" required>
                <option value="diesel">Diesel</option>
                <option value="cng">CNG</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium">License</label>
              <input v-model.number="form.license" type="number" min="0" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
              <label class="block text-sm font-medium">VIN</label>
              <input v-model="form.vin" type="text" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
              <label class="block text-sm font-medium">Active Status</label>
              <div class="mt-2">
                <input type="checkbox" v-model="form.is_active" true-value="1" false-value="0" class="rounded">
                <span class="ml-2">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
              </div>
            </div>

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

      <form ref="exportForm" method="GET" class="hidden" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  entries: Object,
  tenantSlug: String,
  SuperAdmin: Boolean,
  tenants: Array,
});

const successMessage = ref('');
const showModal = ref(false);
const formTitle = ref('Create Entry');
const formAction = ref('Create');
const exportForm = ref(null);

const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
];

const tableColumns = [
  'truckid',
  'type',
  'make',
  'fuel',
  'license',
  'vin',
  'is_active'
];

const form = useForm({
  id: null,
  truckid: null,
  type: 'Daycab',
  make: 'International',
  fuel: 'diesel',
  license: null,
  vin: '',
  is_active: 1,
  tenant_id: null
});

const importForm = useForm({
  csv_file: null,
  tenant_id: null,
});

const deleteForm = useForm({});

function openCreateModal() {
  form.reset();
  form.is_active = 1;
  form.tenant_id = null;
  formTitle.value = 'Create Entry';
  formAction.value = 'Create';
  showModal.value = true;
}

function openEditModal(item) {
  form.id = item.id;
  form.truckid = item.truckid;
  form.type = item.type;
  form.make = item.make;
  form.fuel = item.fuel;
  form.license = item.license;
  form.vin = item.vin;
  form.is_active = item.is_active;
  form.tenant_id = item.tenant_id;
  
  formTitle.value = 'Edit Entry';
  formAction.value = 'Update';
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

function submitForm() {
  const payload = {
    truckid: Number(form.truckid),
    type: form.type,
    make: form.make,
    fuel: form.fuel,
    license: Number(form.license),
    vin: form.vin,
    is_active: Number(form.is_active),
    tenant_id: form.tenant_id
  };

  if (form.id) {
    form.put(props.SuperAdmin
      ? route('truck.update.admin', [form.id])
      : route('truck.update', [props.tenantSlug, form.id]), {
      data: payload,
      onSuccess: () => {
        successMessage.value = 'Entry updated.';
        closeModal();
      },
      onError: () => alert('Error updating entry')
    });
  } else {
    form.post(props.SuperAdmin
      ? route('truck.store.admin')
      : route('truck.store', props.tenantSlug), {
      data: payload,
      onSuccess: () => {
        successMessage.value = 'Entry created.';
        closeModal();
      },
      onError: () => alert('Error creating entry')
    });
  }
}

function deleteEntry(id) {
  if (!confirm('Are you sure?')) return;
  deleteForm.delete(props.SuperAdmin
    ? route('truck.destroy.admin', [id])
    : route('truck.destroy', [props.tenantSlug, id]), {
    onSuccess: () => successMessage.value = 'Entry deleted.'
  });
}

function handleImport(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  
  importForm.csv_file = file;
  importForm.post(props.SuperAdmin
    ? route('truck.import.admin')
    : route('truck.import', props.tenantSlug), {
    forceFormData: true,
    onSuccess: () => successMessage.value = 'Data imported successfully.',
    onError: () => alert('Import failed')
  });
}

function exportCSV() {
  const routeName = props.SuperAdmin
    ? route('truck.export.admin')
    : route('truck.export', props.tenantSlug);
  exportForm.value?.setAttribute('action', routeName);
  exportForm.value?.submit();
}

function visitPage(url) {
  if (url) router.get(url, {}, { only: ['entries'] });
}
</script>