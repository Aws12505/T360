<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="max-w-6xl mx-auto p-6 space-y-8">
      <!-- Success Message -->
      <p
        v-if="successMessage"
        class="bg-green-100 text-green-800 border border-green-300 px-4 py-2 rounded"
      >
        {{ successMessage }}
      </p>

      <!-- Action Buttons -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <button
          @click="openCreateModal"
          class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow transition"
        >
          Create New Performance
        </button>
        <label class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow cursor-pointer transition">
          Import CSV
          <input
            type="file"
            class="hidden"
            @change="handleImport"
            accept=".csv"
          />
        </label>
        <button
          @click.prevent="exportCSV"
          class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded shadow transition"
        >
          Export CSV
        </button>
      </div>

      <!-- Performance Table -->
      <div class="overflow-x-auto shadow rounded-lg">
        <table class="min-w-full table-auto">
          <thead class="bg-gray-100 text-left">
            <tr>
              <th v-if="SuperAdmin" class="px-4 py-2">Tenant</th>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Acceptance</th>
              <th class="px-4 py-2">On Time to Origin</th>
              <th class="px-4 py-2">On Time to Destination</th>
              <th class="px-4 py-2">On Time</th>
              <th class="px-4 py-2">Maintenance Variance</th>
              <th class="px-4 py-2">Open BOC</th>
              <th class="px-4 py-2">Safety Bonus?</th>
              <th class="px-4 py-2">VCR Preventable</th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="item in performances"
              :key="item.id"
              class="text-sm hover:bg-gray-50"
            >
              <td v-if="SuperAdmin" class="px-4 py-2">
                {{ item.tenant?.name ?? '—' }}
              </td>
              <td class="px-4 py-2">{{ item.date }}</td>
              <td class="px-4 py-2">
                {{ item.acceptance }}
                <div class="text-xs italic text-gray-500">
                  ({{ item.acceptance_rating }})
                </div>
              </td>
              <td class="px-4 py-2">{{ item.on_time_to_origin }}</td>
              <td class="px-4 py-2">{{ item.on_time_to_destination }}</td>
              <td class="px-4 py-2">
                {{ item.on_time }}
                <div class="text-xs italic text-gray-500">
                  ({{ item.on_time_rating }})
                </div>
              </td>
              <td class="px-4 py-2">
                {{ item.maintenance_variance_to_spend }}
                <div class="text-xs italic text-gray-500">
                  ({{ item.maintenance_variance_to_spend_rating }})
                </div>
              </td>
              <td class="px-4 py-2">
                {{ item.open_boc }}
                <div class="text-xs italic text-gray-500">
                  ({{ item.open_boc_rating }})
                </div>
              </td>
              <td class="px-4 py-2">
                {{ item.meets_safety_bonus_criteria ? 'Yes' : 'No' }}
                <div class="text-xs italic text-gray-500">
                  ({{ item.meets_safety_bonus_criteria_rating }})
                </div>
              </td>
              <td class="px-4 py-2">
                {{ item.vcr_preventable }}
                <div class="text-xs italic text-gray-500">
                  ({{ item.vcr_preventable_rating }})
                </div>
              </td>
              <td class="px-4 py-2 space-x-2">
                <button
                  @click="openEditModal(item)"
                  class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded text-sm transition"
                >
                  Edit
                </button>
                <button
                  @click="deletePerformance(item.id)"
                  class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm transition"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal for Create/Edit -->
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-lg space-y-6">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ formTitle }}
          </h2>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div v-if="SuperAdmin">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tenant
              </label>
              <select
                v-model="form.tenant_id"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option disabled value="">Select a tenant</option>
                <option
                  v-for="tenant in tenants"
                  :key="tenant.id"
                  :value="tenant.id"
                >
                  {{ tenant.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Date
              </label>
              <input
                v-model="form.date"
                type="date"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Acceptance
              </label>
              <input
                v-model="form.acceptance"
                type="number"
                step="0.01"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                On Time to Origin
              </label>
              <input
                v-model="form.on_time_to_origin"
                type="number"
                step="0.01"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                On Time to Destination
              </label>
              <input
                v-model="form.on_time_to_destination"
                type="number"
                step="0.01"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Maintenance Variance to Spend
              </label>
              <input
                v-model="form.maintenance_variance_to_spend"
                type="number"
                step="0.01"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Open BOC
              </label>
              <input
                v-model="form.open_boc"
                type="number"
                step="1"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div class="flex items-center gap-2">
              <input
                v-model="form.meets_safety_bonus_criteria"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 focus:ring-blue-500"
              />
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Meets Safety Bonus Criteria
              </label>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                VCR Preventable
              </label>
              <input
                v-model="form.vcr_preventable"
                type="number"
                step="1"
                required
                class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div class="flex justify-end gap-3">
              <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded transition"
              >
                {{ formAction }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="bg-gray-300 hover:bg-gray-400 text-black font-semibold px-4 py-2 rounded transition"
              >
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
  performances: Array,
  tenantSlug: {
    type: String,
    default: null
  },
  SuperAdmin: {
    type: Boolean,
    default: false,
  },
  tenants: {
    type: Array,
    default: () => [],
  }
})

const successMessage = ref('')
const showModal = ref(false)
const formTitle = ref('Create Performance')
const formAction = ref('Create')

const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard')
  }
]

const form = useForm({
  tenant_id: null, 
  date: '',
  acceptance: '',
  on_time_to_origin: '',
  on_time_to_destination: '',
  maintenance_variance_to_spend: '',
  open_boc: '',
  meets_safety_bonus_criteria: false,
  vcr_preventable: '',
  id: null
})

const deleteForm = useForm({})
const exportForm = ref(null)
const importForm = useForm({ csv_file: null })

function openCreateModal() {
  formTitle.value = 'Create Performance'
  formAction.value = 'Create'
  form.reset()
  showModal.value = true
}

function openEditModal(item) {
  formTitle.value = 'Edit Performance'
  formAction.value = 'Update'

  form.tenant_id = props.SuperAdmin ? item.tenant_id : null
  form.date = item.date
  form.acceptance = item.acceptance
  form.on_time_to_origin = item.on_time_to_origin
  form.on_time_to_destination = item.on_time_to_destination
  form.maintenance_variance_to_spend = item.maintenance_variance_to_spend
  form.open_boc = item.open_boc
  form.meets_safety_bonus_criteria = !!item.meets_safety_bonus_criteria
  form.vcr_preventable = item.vcr_preventable
  form.id = item.id

  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function submitForm() {
  const isCreate = formAction.value === 'Create'
  const routeName = isCreate
    ? props.SuperAdmin
      ? route('performance.store.admin')
      : route('performance.store', props.tenantSlug)
    : props.SuperAdmin
      ? route('performance.update.admin', [form.id])
      : route('performance.update', [props.tenantSlug, form.id])

  const method = isCreate ? 'post' : 'put'

  form[method](routeName, {
    onSuccess: () => {
      successMessage.value = isCreate
        ? 'Performance created successfully.'
        : 'Performance updated successfully.'
      closeModal()
    },
    onError: () => {
      alert('Something went wrong.')
    }
  })
}

function deletePerformance(id) {
  if (!confirm('Are you sure?')) return

  const routeName = props.SuperAdmin
    ? route('performance.destroy.admin', [id])
    : route('performance.destroy', [props.tenantSlug, id])

  deleteForm.delete(routeName, {
    onSuccess: () => {
      successMessage.value = 'Performance deleted.'
    }
  })
}

function handleImport(e) {
  const file = e.target.files?.[0]
  if (!file) return

  importForm.csv_file = file

  const routeName = props.SuperAdmin
    ? route('performance.import.admin')
    : route('performance.import', props.tenantSlug)

  importForm.post(routeName, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      importForm.reset()
      successMessage.value = 'CSV Imported successfully.'
    },
    onError: () => {
      alert('Import failed.')
    }
  })
}

function exportCSV() {
  const routeName = props.SuperAdmin
    ? route('performance.export.admin')
    : route('performance.export', props.tenantSlug)

  exportForm.value?.setAttribute('action', routeName)
  exportForm.value?.submit()
}
</script>

<style scoped>
/* Additional custom styling if needed */
</style>
