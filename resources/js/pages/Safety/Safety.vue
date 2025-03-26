<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <p v-if="successMessage" class="bg-green-100 text-green-800 border border-green-300 px-4 py-2 rounded">
        {{ successMessage }}
      </p>

      <!-- Actions -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <button @click="openCreateModal" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow transition">
          Create New Entry
        </button>

        <div v-if="SuperAdmin">
          <label class="block text-sm font-medium mb-1">Tenant for Import</label>
          <select v-model="importForm.tenant_id" class="w-full border rounded px-3 py-2">
            <option disabled value="">Select Tenant</option>
            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">{{ tenant.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Date for Imported XLSX</label>
          <input v-model="importForm.date" type="date" required class="w-full border rounded px-3 py-2" />
        </div>

        <label class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow cursor-pointer transition">
          Import XLSX
          <input type="file" class="hidden" @change="handleImport" accept=".xlsx" />
        </label>

        <button @click.prevent="exportCSV" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded shadow transition">
          Export CSV
        </button>
      </div>

      <!-- Table -->
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
            <tr v-for="item in entries" :key="item.id">
              <td v-if="SuperAdmin" class="px-4 py-2">{{ item.tenant?.name ?? '—' }}</td>
              <td v-for="col in tableColumns" :key="col" class="px-4 py-2 whitespace-nowrap">
                {{ item[col] }}
              </td>
              <td class="px-4 py-2 space-x-2">
                <button @click="openEditModal(item)" class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded">Edit</button>
                <button @click="deleteEntry(item.id)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
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
                <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">{{ tenant.name }}</option>
              </select>
            </div>

            <template v-for="col in formColumns" :key="col">
              <div>
                <label class="block text-sm font-medium capitalize">{{ col.replace(/_/g, ' ') }}</label>
                <input
                  v-model="form[col]"
                  :type="getInputType(col)"
                  :step="getStep(col)"
                  :min="getMin(col)"
                  class="w-full border rounded px-3 py-2"
                />
              </div>
            </template>

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
  import { ref } from 'vue'
  import { useForm } from '@inertiajs/vue3'
  import AppLayout from '@/layouts/AppLayout.vue'
  
  const props = defineProps({
    entries: Array,
    tenantSlug: String,
    SuperAdmin: Boolean,
    tenants: Array
  })
  
  const successMessage = ref('')
  const showModal = ref(false)
  const formTitle = ref('Create Entry')
  const formAction = ref('Create')
  const exportForm = ref(null)
  
  const breadcrumbs = [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href: props.tenantSlug
        ? route('dashboard', { tenantSlug: props.tenantSlug })
        : route('admin.dashboard')
    }
  ]
  
  // Field types based on migration
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
}

  
  const formColumns = Object.keys(fieldTypes)
  const tableColumns = [...formColumns]
  
  const form = useForm({
    tenant_id: null,
    ...Object.fromEntries(formColumns.map(col => [col, ''])),
    id: null
  })
  
  const importForm = useForm({
  csv_file: null,
  date: '',
  tenant_id: null // ✅ added
})
  
  const deleteForm = useForm({})
  
  function getInputType(field) {
    const type = fieldTypes[field]
    if (type === 'decimal' || type === 'integer') return 'number'
    if (type === 'date') return 'date'
    return 'text'
  }
  
  function getStep(field) {
    const type = fieldTypes[field]
    if (type === 'decimal') return '0.01'
    if (type === 'integer') return '1'
    return null
  }
  
  function getMin(field) {
    const type = fieldTypes[field]
    return type === 'decimal' || type === 'integer' ? '-99999' : null
  }
  
  function openCreateModal() {
    form.reset()
    formTitle.value = 'Create Entry'
    formAction.value = 'Create'
    showModal.value = true
  }
  
  function openEditModal(item) {
    formTitle.value = 'Edit Entry'
    formAction.value = 'Update'
  
    form.tenant_id = props.SuperAdmin ? item.tenant_id : null
    form.id = item.id
  
    formColumns.forEach(col => {
      form[col] = item[col]
    })
  
    showModal.value = true
  }
  
  function closeModal() {
    showModal.value = false
  }
  
  function submitForm() {
    const isCreate = formAction.value === 'Create'
    const routeName = isCreate
      ? props.SuperAdmin ? route('safety.store.admin') : route('safety.store', props.tenantSlug)
      : props.SuperAdmin ? route('safety.update.admin', [form.id]) : route('safety.update', [props.tenantSlug, form.id])
  
    const method = isCreate ? 'post' : 'put'
  
    form[method](routeName, {
      onSuccess: () => {
        successMessage.value = isCreate ? 'Entry created.' : 'Entry updated.'
        closeModal()
      },
      onError: () => alert('Something went wrong.')
    })
  }
  
  function deleteEntry(id) {
    if (!confirm('Are you sure?')) return
  
    const routeName = props.SuperAdmin
      ? route('safety.destroy.admin', [id])
      : route('safety.destroy', [props.tenantSlug, id])
  
    deleteForm.delete(routeName, {
      onSuccess: () => successMessage.value = 'Entry deleted.'
    })
  }
  
  function handleImport(e) {
  const file = e.target.files?.[0]
  if (!file) return

  if (!importForm.date) {
    alert('Please select the date for this import.')
    return
  }

  if (props.SuperAdmin && !importForm.tenant_id) {
    alert('Please select a tenant for import.')
    return
  }

  importForm.csv_file = file

  const routeName = props.SuperAdmin
    ? route('safety.import.admin')
    : route('safety.import', props.tenantSlug)

  importForm.post(routeName, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Data imported successfully.'
      importForm.reset()
    },
    onError: () => alert('Import failed.')
  })
}
  
  function exportCSV() {
    const routeName = props.SuperAdmin
      ? route('safety.export.admin')
      : route('safety.export', props.tenantSlug)
  
    exportForm.value?.setAttribute('action', routeName)
    exportForm.value?.submit()
  }
  </script>
  