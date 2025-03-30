<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="p-6 space-y-6">
      <!-- Header Section -->
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Acceptance</h1>
        <div class="space-x-2">
          <!-- Button to open rejection form modal -->
          <Button @click="openForm()" variant="default" class="px-4 py-2">
            Add Rejection
          </Button>
          <!-- Button to open reason code management modal -->
          <Button @click="openCodeModal()" variant="outline" class="px-4 py-2">
            Manage Reason Codes
          </Button>
        </div>
      </div>

      <!-- Rejections Table -->
      <div class="border rounded-lg">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-left">
            <tr>
              <th v-if="isSuperAdmin" class="p-3">Tenant</th>
              <th class="p-3">Date</th>
              <th class="p-3">Type</th>
              <th class="p-3">Driver</th>
              <th class="p-3">Penalty</th>
              <th class="p-3">Reason</th>
              <th class="p-3">Disputed</th>
              <th class="p-3">Controllable</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rejection in rejections.data" :key="rejection.id" class="border-t">
              <td v-if="isSuperAdmin" class="p-3">{{ rejection.tenant?.name || '—' }}</td>
              <td class="p-3">{{ rejection.date }}</td>
              <td class="p-3 capitalize">{{ rejection.rejection_type }}</td>
              <td class="p-3">{{ rejection.driver_name }}</td>
              <td class="p-3">{{ rejection.penalty }}</td>
              <td class="p-3">{{ rejection.reason_code?.reason_code || '—' }}</td>
              <td class="p-3">{{ rejection.disputed ? 'Yes' : 'No' }}</td>
              <td class="p-3">
                {{ rejection.driver_controllable === null ? 'N/A' : (rejection.driver_controllable ? 'Yes' : 'No') }}
              </td>
              <td class="p-3 space-x-2">
                <Button size="sm" @click="openForm(rejection)" variant="default" class="px-3 py-1">
                  Edit
                </Button>
                <Button size="sm" variant="destructive" @click="deleteRejection(rejection.id)" class="px-3 py-1">
                  Delete
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="mt-4 flex justify-center" v-if="rejections.links">
      <button
        v-for="link in rejections.links"
        :key="link.label"
        @click="visitPage(link.url)"
        :disabled="!link.url"
        class="mx-1 px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50"
      >
        <span v-html="link.label"></span>
      </button>
    </div>
      </div>

      <!-- Rejection Form Modal -->
      <Dialog v-model:open="formModal">
        <DialogContent class="sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>{{ selectedRejection ? 'Edit' : 'Add' }} Rejection</DialogTitle>
          </DialogHeader>
          <RejectionForm
            :rejection="selectedRejection"
            :reasons="rejection_reason_codes"
            :tenants="tenants"
            :is-super-admin="isSuperAdmin"
            :tenant-slug="tenantSlug"
            @close="formModal = false"
          />
        </DialogContent>
      </Dialog>

      <!-- Code Manager Modal for Reason Codes (visible only for SuperAdmin) -->
      <Dialog v-model:open="codeModal" v-if="isSuperAdmin">
        <DialogContent class="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>Manage Reason Codes</DialogTitle>
          </DialogHeader>
          <CodeManager
            model="rejection_reason_codes"
            label="Reason Code"
            :codes="rejection_reason_codes"
            :is-super-admin="isSuperAdmin"
            :tenant-slug="tenantSlug"
            @refresh="$inertia.reload({ only: ['rejection_reason_codes'] })"
          />
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components from their correct folders
import Button from '@/components/ui/button/Button.vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import RejectionForm from '@/components/RejectionForm.vue';
import CodeManager from '@/components/CodeManager.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {router} from '@inertiajs/vue3';

const props = defineProps({
  rejections:  {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenantSlug: { type: String, default: null },
  rejection_reason_codes: Array,
  tenants: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
});

// Set up breadcrumbs
const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
];

// Reactive state for modals and selected rejection
const formModal = ref(false);
const codeModal = ref(false);
const selectedRejection = ref(null);

// Function to open the rejection form modal (for create or edit)
const openForm = (rejection = null) => {
  selectedRejection.value = rejection;
  formModal.value = true;
};

// Function to open the reason codes manager modal
const openCodeModal = () => {
  codeModal.value = true;
};

// Function to delete a rejection using Inertia form helper
const deleteRejection = (id) => {
  const form = useForm({});
  const routeName = props.isSuperAdmin ? 'acceptance.destroy.admin' : 'acceptance.destroy';
  const routeParams = props.isSuperAdmin ? { rejection: id } : { tenantSlug: props.tenantSlug, rejection: id };
  if (!confirm('Are you sure you want to delete this rejection?')) return;
  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
  });
};
const visitPage = (url) => {
  if (url) {
    router.get(url, {}, { replace: true });
  }
};
</script>
