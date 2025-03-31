<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Success Message -->
      <p v-if="successMessage" class="bg-green-100 text-green-800 border border-green-300 px-4 py-2 rounded">
        {{ successMessage }}
      </p>

      <!-- Actions Section -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <Button @click="openCreateModal" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow transition">
          Create New Driver
        </Button>

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
                <template v-if="col === 'hiring_date'">
                  {{ formatDate(item[col]) }}
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
              <label class="block text-sm font-medium">First Name</label>
              <input v-model="form.first_name" type="text" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
              <label class="block text-sm font-medium">Last Name</label>
              <input v-model="form.last_name" type="text" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium">Email Address</label>
              <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium">Mobile Phone Number</label>
              <input v-model="form.mobile_phone" type="text" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium">Hiring Date</label>
              <input v-model="form.hiring_date" type="date" class="w-full border rounded px-3 py-2" required>
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
const formTitle = ref('Create Driver');
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
  'first_name',
  'last_name',
  'email',
  'mobile_phone',
  'hiring_date'
];

const form = useForm({
  id: null,
  first_name: '',
  last_name: '',
  email: '',
  mobile_phone: '',
  hiring_date: '',
  tenant_id: null
});

const importForm = useForm({
  csv_file: null,
});

const deleteForm = useForm({});

function openCreateModal() {
  form.reset();
  form.tenant_id = null;
  formTitle.value = 'Create Driver';
  formAction.value = 'Create';
  showModal.value = true;
}

function openEditModal(item) {
  form.id = item.id;
  form.first_name = item.first_name;
  form.last_name = item.last_name;
  form.email = item.email;
  form.mobile_phone = item.mobile_phone;
  form.hiring_date = item.hiring_date;
  form.tenant_id = item.tenant_id;
  
  formTitle.value = 'Edit Driver';
  formAction.value = 'Update';
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

function submitForm() {
  const payload = {
    first_name: form.first_name,
    last_name: form.last_name,
    email: form.email,
    mobile_phone: form.mobile_phone,
    hiring_date: form.hiring_date,
    tenant_id: form.tenant_id
  };

  if (form.id) {
    form.put(props.SuperAdmin
      ? route('driver.update.admin', [form.id])
      : route('driver.update', [props.tenantSlug, form.id]), {
      data: payload,
      onSuccess: () => {
        successMessage.value = 'Driver updated.';
        closeModal();
      },
      onError: () => alert('Error updating driver')
    });
  } else {
    form.post(props.SuperAdmin
      ? route('driver.store.admin')
      : route('driver.store', props.tenantSlug), {
      data: payload,
      onSuccess: () => {
        successMessage.value = 'Driver created.';
        closeModal();
      },
      onError: () => alert('Error creating driver')
    });
  }
}

function deleteEntry(id) {
  if (!confirm('Are you sure?')) return;
  deleteForm.delete(props.SuperAdmin
    ? route('driver.destroy.admin', [id])
    : route('driver.destroy', [props.tenantSlug, id]), {
    onSuccess: () => successMessage.value = 'Driver deleted.'
  });
}

function handleImport(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  
  importForm.csv_file = file;
  importForm.post(props.SuperAdmin
    ? route('driver.import.admin')
    : route('driver.import', props.tenantSlug), {
    forceFormData: true,
    onSuccess: () => successMessage.value = 'Data imported successfully.',
    onError: () => alert('Import failed')
  });
}

function exportCSV() {
  const routeName = props.SuperAdmin
    ? route('driver.export.admin')
    : route('driver.export', props.tenantSlug);
  exportForm.value?.setAttribute('action', routeName);
  exportForm.value?.submit();
}

function visitPage(url) {
  if (url) router.get(url, {}, { only: ['entries'] });
}

// Updated: Format date string from YYYY-MM-DD to m/d/Y without using Date()
// to avoid timezone-related day shifts.
function formatDate(dateStr) {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}
</script>
