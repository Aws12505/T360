<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Delays</h1>
        <div class="space-x-2">
          <Button @click="openForm()">Add Delay</Button>
          <Button variant="outline" @click="openCodeModal()">Manage Delay Codes</Button>
        </div>
      </div>

      <div class="border rounded-lg">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-left">
            <tr>
              <th v-if="isSuperAdmin" class="p-3">Tenant</th>
              <th class="p-3">Date</th>
              <th class="p-3">Type</th>
              <th class="p-3">Driver</th>
              <th class="p-3">Penalty</th>
              <th class="p-3">Code</th>
              <th class="p-3">Disputed</th>
              <th class="p-3">Controllable</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="delay in delays" :key="delay.id" class="border-t">
              <td v-if="isSuperAdmin" class="p-3">{{ delay.tenant?.name || '—' }}</td>
              <td class="p-3">{{ delay.date }}</td>
              <td class="p-3">{{ delay.delay_type }}</td>
              <td class="p-3">{{ delay.driver_name }}</td>
              <td class="p-3">{{ delay.penalty }}</td>
              <td class="p-3">{{ delay.delay_code?.code || '—' }}</td>
              <td class="p-3">{{ delay.disputed ? 'Yes' : 'No' }}</td>
              <td class="p-3">{{ delay.driver_controllable === null ? 'N/A' : delay.driver_controllable ? 'Yes' : 'No' }}</td>
              <td class="p-3 space-x-2">
                <Button size="sm" @click="openForm(delay)">Edit</Button>
                <Button size="sm" variant="destructive" @click="deleteDelay(delay.id)">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Dialog v-model:open="formModal">
        <DialogContent class="sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>{{ selectedDelay ? 'Edit' : 'Add' }} Delay</DialogTitle>
          </DialogHeader>
          <DelayForm
            :delay="selectedDelay"
            :delay-codes="delay_codes"
            :tenants="tenants"
            :is-super-admin="isSuperAdmin"
            :tenant-slug="tenantSlug"
            @close="formModal = false"
          />
        </DialogContent>
      </Dialog>

      <Dialog v-model:open="codeModal" v-if="isSuperAdmin">
        <DialogContent class="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>Manage Delay Codes</DialogTitle>
          </DialogHeader>
          <CodeManager
            model="delay_codes"
            label="Delay Code"
            :codes="delay_codes"
            :is-super-admin="isSuperAdmin"
            :tenant-slug="tenantSlug"
            @refresh="$inertia.reload({ only: ['delay_codes'] })"
          />
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import DelayForm from '@/Components/DelayForm.vue'
import CodeManager from '@/Components/CodeManager.vue'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  delays: Array,
  tenantSlug: {
    type: String,
    default: null,
  },
  delay_codes: Array,
  tenants: {
    type: Array,
    default: () => [],
  },
  isSuperAdmin: {
    type: Boolean,
    default: false,
  },
})

const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
]

const formModal = ref(false)
const codeModal = ref(false)
const selectedDelay = ref(null)

const openForm = (delay = null) => {
  selectedDelay.value = delay
  formModal.value = true
}

const openCodeModal = () => {
  codeModal.value = true
}

const deleteDelay = (id) => {
  const form = useForm()
  const routeName = props.isSuperAdmin
    ? 'delays.destroy.admin'
    : 'delays.destroy'

  const routeParams = props.isSuperAdmin
    ? { delay: id }
    : { tenantSlug: props.tenantSlug, delay: id }

  if (!confirm('Are you sure you want to delete this delay?')) return

  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
  })
}
</script>
