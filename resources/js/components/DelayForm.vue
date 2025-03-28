<template>
  <form @submit.prevent="submit">
    <div class="grid grid-cols-2 gap-4">
      <!-- Tenant dropdown for SuperAdmin users -->
      <div v-if="isSuperAdmin">
        <Label>Tenant</Label>
        <select v-model="form.tenant_id" class="w-full border rounded px-2 py-1">
          <option :value="null" disabled>Select tenant</option>
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
      <!-- Delay Type Field -->
      <div>
        <Label>Delay Type</Label>
        <select v-model="form.delay_type" class="w-full border rounded px-2 py-1">
          <option value="origin">Origin</option>
          <option value="destination">Destination</option>
        </select>
      </div>
      <!-- Delay Category Field -->
      <div>
        <Label>Delay Category</Label>
        <select v-model="form.delay_category" class="w-full border rounded px-2 py-1">
          <option value="1_120">1–120 min</option>
          <option value="121_600">121–600 min</option>
          <option value="601_plus">601+ min</option>
        </select>
      </div>
      <!-- Delay Code Field -->
      <div>
        <Label>Delay Code</Label>
        <select v-model="form.delay_code_id" class="w-full border rounded px-2 py-1">
          <option v-for="code in delayCodes" :key="code.id" :value="code.id">
            {{ code.code }}
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
