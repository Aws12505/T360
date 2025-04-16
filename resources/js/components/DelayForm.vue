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
      
      <!-- Delay Type Field -->
      <div>
        <Label>Delay Type</Label>
        <div class="relative">
          <select 
            v-model="form.delay_type" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option value="origin">Origin</option>
            <option value="destination">Destination</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Delay Category Field -->
      <div>
        <Label>Delay Category</Label>
        <div class="relative">
          <select 
            v-model="form.delay_category" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option value="1_120">1–120 min</option>
            <option value="121_600">121–600 min</option>
            <option value="601_plus">601+ min</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
            <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Delay Code Field -->
      <div>
        <Label>Delay Code</Label>
        <div class="relative">
          <select 
            v-model="form.delay_code_id" 
            class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
          >
            <option v-for="code in delayCodes" :key="code.id" :value="code.id">
              {{ code.code }}
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
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import Label from '@/components/ui/label/Label.vue';

const props = defineProps({
  delay: { type: Object, default: null },
  delayCodes: { type: Array, default: () => [] },
  tenants: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
});
const emit = defineEmits(['close']);

// Initialize the form. Convert driver_controllable from tinyint to boolean.
const form = useForm({
  id: props.delay?.id,
  tenant_id: props.isSuperAdmin ? props.delay?.tenant_id : null,
  date: props.delay?.date || '',
  driver_name: props.delay?.driver_name || '',
  delay_type: props.delay?.delay_type || 'origin',
  delay_category: props.delay?.delay_category || '1_120',
  delay_code_id: props.delay?.delay_code_id || null,
  disputed: props.delay && props.delay.disputed !== null
    ? (parseInt(props.delay.disputed) === 1)
    : false,
  driver_controllable: props.delay && props.delay.driver_controllable !== null
    ? (parseInt(props.delay.driver_controllable) === 1)
    : null,
});

watch(() => props.delay, (newVal) => {
  if (newVal) {
    form.id = newVal.id;
    form.tenant_id = newVal.tenant_id;
    form.date = newVal.date;
    form.driver_name = newVal.driver_name;
    form.delay_type = newVal.delay_type;
    form.delay_category = newVal.delay_category;
    form.delay_code_id = newVal.delay_code_id;
    form.disputed = newVal.disputed !== null ? (parseInt(newVal.disputed) === 1) : false;
    form.driver_controllable = newVal.driver_controllable !== null ? (parseInt(newVal.driver_controllable) === 1) : null;
  } else {
    form.reset();
  }
}, { immediate: true });

const submit = () => {
  const isEdit = !!form.id;
  const routeName = props.isSuperAdmin
    ? isEdit ? 'ontime.update.admin' : 'ontime.store.admin'
    : isEdit ? 'ontime.update' : 'ontime.store';
  const routeParams = props.isSuperAdmin
    ? isEdit ? { delay: form.id } : {}
    : isEdit ? { tenantSlug: props.tenantSlug, delay: form.id } : { tenantSlug: props.tenantSlug };
  const method = isEdit ? 'put' : 'post';
  form[method](route(routeName, routeParams), {
    onSuccess: () => emit('close'),
  });
};
</script>
