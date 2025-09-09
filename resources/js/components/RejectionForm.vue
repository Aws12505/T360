<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Tenant dropdown for SuperAdmin users -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div v-if="isSuperAdmin" class="col-span-full">
        <Label>Company Name</Label>
        <div class="relative">
          <select 
            v-model="form.tenant_id" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option :value="null" disabled>Select Company</option>
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
      
      <!-- Date Field -->
      <div class="col-span-full">
        <Label>Date</Label>
        <Input type="date" v-model="form.date" class="w-full" />
      </div>
      
      <!-- Main form fields in a responsive grid -->
      <div>
        <Label>Driver Name</Label>
        <Input v-model="form.driver_name" class="w-full" />
      </div>
      
      <!-- Rejection Type Field -->
      <div>
        <Label>Rejection Type</Label>
        <div class="relative">
          <select 
            v-model="form.rejection_type" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option value="block">Block</option>
            <option value="load">Load</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Rejection Category Field -->
      <div>
        <Label>Rejection Category</Label>
        <div class="relative">
          <select 
            v-model="form.rejection_category" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option value="advanced_rejection">Advanced Rejection</option>
            <option value="more_than_24">More than 24 hrs</option>
            <option value="within_24">Within 24 hrs</option>
            <option value="after_start">After start</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Reason Code Field -->
      <div>
        <Label>Reason Code</Label>
        <div class="relative">
          <select 
            v-model="form.reason_code_id" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option v-for="reason in reasons" :key="reason.id" :value="reason.id">
              {{ reason.reason_code }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Driver Controllable Field -->
      <div>
        <Label>Driver Controllable</Label>
        <div class="relative">
          <select 
            v-model="form.driver_controllable" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option :value="null">N/A</option>
            <option :value="true">Yes</option>
            <option :value="false">No</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Disputed Checkbox -->
      <div class="flex items-center space-x-2">
        <Checkbox v-model:checked="form.disputed" id="disputed" />
        <Label for="disputed">Disputed</Label>
      </div>
    </div>

    <div class="flex-col space-y-2 sm:space-y-0 sm:flex-row sm:justify-end sm:space-x-2 pt-4 border-t flex">
      <Button type="button" @click="emit('close')" variant="outline" class="w-full sm:w-auto">
        Cancel
      </Button>
      <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
        {{ form.id ? 'Update' : 'Create' }}
      </Button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components from correct folders
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import Label from '@/components/ui/label/Label.vue';

const props = defineProps({
  rejection: { type: Object, default: null },
  reasons: { type: Array, default: () => [] },
  tenants: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
});
const emit = defineEmits(['close']);

// Initialize the form. For driver_controllable, convert the value to boolean
const form = useForm({
  id: props.rejection?.id,
  tenant_id: props.isSuperAdmin ? props.rejection?.tenant_id : null,
  date: props.rejection?.date || '',
  driver_name: props.rejection?.driver_name || '',
  rejection_type: props.rejection?.rejection_type || 'block',
  rejection_category: props.rejection?.rejection_category || 'more_than_24',
  reason_code_id: props.rejection?.reason_code_id || null,
  disputed: props.rejection && props.rejection.disputed !== null
    ? (parseInt(props.rejection.disputed) === 1)
    : false,
  driver_controllable: props.rejection && props.rejection.driver_controllable !== null
    ? (parseInt(props.rejection.driver_controllable) === 1)
    : null,
});

// Watch for changes in the rejection prop and update the form accordingly.
watch(() => props.rejection, (newVal) => {
  if (newVal) {
    form.id = newVal.id;
    form.tenant_id = newVal.tenant_id;
    form.date = newVal.date;
    form.driver_name = newVal.driver_name;
    form.rejection_type = newVal.rejection_type;
    form.rejection_category = newVal.rejection_category;
    form.reason_code_id = newVal.reason_code_id;
    form.disputed = newVal.disputed !== null ? (parseInt(newVal.disputed) === 1) : false;
    form.driver_controllable = newVal.driver_controllable !== null ? (parseInt(newVal.driver_controllable) === 1) : null;
  } else {
    form.reset();
  }
}, { immediate: true });

const submit = () => {
  const isEdit = !!form.id;
  const routeName = props.isSuperAdmin
    ? isEdit ? 'acceptance.update.admin' : 'acceptance.store.admin'
    : isEdit ? 'acceptance.update' : 'acceptance.store';
  const routeParams = props.isSuperAdmin
    ? isEdit ? { rejection: form.id } : {}
    : isEdit ? { tenantSlug: props.tenantSlug, rejection: form.id } : { tenantSlug: props.tenantSlug };
  const method = isEdit ? 'put' : 'post';
  form[method](route(routeName, routeParams), {
    onSuccess: () => emit('close'),
  });
};
</script>
