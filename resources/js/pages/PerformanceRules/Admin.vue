<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import EntryForm from './Form.vue'

const props = defineProps({
  metrics: Object,
  tenants: Array,
  tenantSlug: {
    type: String,
    default: null,
  },
})

const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug ? `/${props.tenantSlug}/dashboard` : '/dashboard',
  },
]

const showForm = ref(false)
const editing = ref(null)
const importForm = useForm({
  csv_file: null,
})

function handleImport(e) {
  const file = e.target.files?.[0]
  if (!file) return

  importForm.csv_file = file

  importForm.post(route('performance-metrics.import'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      importForm.reset()
    },
    onError: () => {
      alert('Import failed. Check file format.')
    }
  })
}

function newEntry() {
  editing.value = {}
  showForm.value = true
}

function editEntry(entry) {
  editing.value = { ...entry }
  showForm.value = true
}

function closeForm() {
  editing.value = null
  showForm.value = false
}

const exportForm = ref(null)

function exportCSV() {
  exportForm.value?.submit()
}



function goToPage(url) {
  if (url) router.visit(url)
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="p-6 max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Performance Metrics</h1>
        <div class="flex gap-3">
          <button @click="newEntry" class="btn btn-primary">New Entry</button>
          <form
    ref="exportForm"
    :action="route('performance-metrics.export')"
    method="GET"
    class="hidden"
  ></form>

  <button @click.prevent="exportCSV" class="btn btn-secondary">Export CSV</button>
            <label class="btn btn-secondary cursor-pointer">
    Import CSV
    <input type="file" class="hidden" @change="handleImport" accept=".csv" />
  </label>
        </div>
      </div>

      <!-- Entry Form -->
      <div v-if="showForm" class="mb-10">
        <EntryForm
          :entry="editing"
          :tenants="tenants"
          @saved="closeForm"
          @cancel="closeForm"
        />
      </div>

      <!-- Metrics Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm border rounded shadow bg-white">
          <thead class="bg-gray-100 text-left">
            <tr>
              <th class="p-3 border">ID</th>
              <th class="p-3 border">Tenant</th>
              <th class="p-3 border">Acceptance F+</th>
              <th class="p-3 border">On-Time F+</th>
              <th class="p-3 border">Maintenance F+</th>
              <th class="p-3 border">Open BOC F+</th>
              <th class="p-3 border">VCR F+</th>
              <th class="p-3 border text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="metric in metrics.data"
              :key="metric.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="p-3 border">{{ metric.id }}</td>
  <!-- Get tenant name from the tenants list based on tenant_id -->
  <td class="p-3 border">
    {{
      tenants.find(t => t.id === metric.tenant_id)?.name ?? '—'
    }}
  </td>              <td class="p-3 border">{{ metric.acceptance_fantastic_plus ?? '—' }}</td>
              <td class="p-3 border">{{ metric.on_time_fantastic_plus ?? '—' }}</td>
              <td class="p-3 border">{{ metric.maintenance_variance_fantastic_plus ?? '—' }}</td>
              <td class="p-3 border">{{ metric.open_boc_fantastic_plus ?? '—' }}</td>
              <td class="p-3 border">{{ metric.vcr_preventable_fantastic_plus ?? '—' }}</td>
              <td class="p-3 border text-right">
                <button
                  @click="editEntry(metric)"
                  class="btn btn-sm btn-outline"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="metrics.links.length > 3" class="mt-6 flex justify-center gap-2 flex-wrap">
        <button
          v-for="link in metrics.links"
          :key="link.label"
          v-html="link.label"
          @click="goToPage(link.url)"
          :disabled="!link.url"
          class="px-3 py-1 text-sm rounded border"
          :class="{
            'bg-blue-600 text-white': link.active,
            'text-gray-700 hover:bg-gray-100': !link.active
          }"
        />
      </div>
    </div>
  </AppLayout>
</template>
