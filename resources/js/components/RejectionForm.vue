<template>
  <form @submit.prevent="submit">
    <div class="grid grid-cols-2 gap-4">
      <!-- Tenant dropdown for SuperAdmin users -->
      <div v-if="isSuperAdmin">
        <Label>Company Name</Label>
        <select v-model="form.tenant_id" class="w-full border rounded px-2 py-1">
          <option :value="null" disabled>Select Company</option>
          <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
            {{ tenant.name }}
          </option>
        </select>
      </div>
      <!-- Date Field -->
      <div>
        <Label>Date</Label>
        <Input type="date" v-model="form.date" class="w-full" />
      </div>
      <!-- Driver Name Field -->
      <div>
        <Label>Driver Name</Label>
        <Input v-model="form.driver_name" class="w-full" />
      </div>
      <!-- Rejection Type Field -->
      <div>
        <Label>Rejection Type</Label>
        <select v-model="form.rejection_type" class="w-full border rounded px-2 py-1">
          <option value="block">Block</option>
          <option value="load">Load</option>
        </select>
      </div>
      <!-- Rejection Category Field -->
      <div>
        <Label>Rejection Category</Label>
        <select v-model="form.rejection_category" class="w-full border rounded px-2 py-1">
          <option value="more_than_6">More than 6 hrs</option>
          <option value="within_6">Within 6 hrs</option>
          <option value="after_start">After start</option>
        </select>
      </div>
      <!-- Reason Code Field -->
      <div>
        <Label>Reason Code</Label>
        <select v-model="form.reason_code_id" class="w-full border rounded px-2 py-1">
          <option v-for="reason in reasons" :key="reason.id" :value="reason.id">
            {{ reason.reason_code }}
          </option>
        </select>
      </div>
      <!-- Disputed Checkbox -->
      <div class="flex items-center space-x-2">
        <Checkbox v-model:checked="form.disputed" />
        <Label>Disputed</Label>
      </div>
      <!-- Driver Controllable Field -->
      <div>
        <Label>Driver Controllable</Label>
        <select v-model="form.driver_controllable" class="w-full border rounded px-2 py-1">
          <option :value="null">N/A</option>
          <option :value="true">Yes</option>
          <option :value="false">No</option>
        </select>
      </div>
    </div>

    <div class="mt-6 flex justify-end">
      <Button type="submit" :disabled="form.processing">
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
  rejection_category: props.rejection?.rejection_category || 'more_than_6',
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
