<script setup>
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Checkbox } from '@/Components/ui/checkbox'
import { Label } from '@/Components/ui/label'

const props = defineProps({
  rejection: Object,
  reasons: Array,
  tenants: Array,
  isSuperAdmin: Boolean,
  tenantSlug: String,
})

const emit = defineEmits(['close'])

const form = useForm({
  id: props.rejection?.id,
  tenant_id: props.isSuperAdmin ? props.rejection?.tenant_id : null,
  date: props.rejection?.date || '',
  driver_name: props.rejection?.driver_name || '',
  rejection_type: props.rejection?.rejection_type || 'block',
  rejection_category: props.rejection?.rejection_category || 'more_than_6',
  reason_code_id: props.rejection?.reason_code_id || null,
  disputed: props.rejection?.disputed || false,
  driver_controllable:
    props.rejection?.driver_controllable === 1 || props.rejection?.driver_controllable === true
      ? true
      : props.rejection?.driver_controllable === 0 || props.rejection?.driver_controllable === false
      ? false
      : null,
})

const submit = () => {
  const isEdit = !!form.id
  const routeName = props.isSuperAdmin
    ? isEdit ? 'rejections.update.admin' : 'rejections.store.admin'
    : isEdit ? 'rejections.update' : 'rejections.store'

  const routeParams = props.isSuperAdmin
    ? isEdit
      ? { rejection: form.id }
      : {}
    : isEdit
      ? { tenantSlug: props.tenantSlug, rejection: form.id }
      : { tenantSlug: props.tenantSlug }

  const method = isEdit ? 'put' : 'post'

  form[method](route(routeName, routeParams), {
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <form @submit.prevent="submit">
    <div class="grid grid-cols-2 gap-4">
      <div v-if="isSuperAdmin">
        <Label>Tenant</Label>
        <select v-model="form.tenant_id" class="w-full border rounded px-2 py-1">
          <option :value="null" disabled>Select tenant</option>
          <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
            {{ tenant.name }}
          </option>
        </select>
      </div>

      <div><Label>Date</Label><Input type="date" v-model="form.date" /></div>
      <div><Label>Driver Name</Label><Input v-model="form.driver_name" /></div>

      <div>
        <Label>Rejection Type</Label>
        <select v-model="form.rejection_type" class="w-full border rounded px-2 py-1">
          <option value="block">Block</option>
          <option value="load">Load</option>
        </select>
      </div>

      <div>
        <Label>Rejection Category</Label>
        <select v-model="form.rejection_category" class="w-full border rounded px-2 py-1">
          <option value="more_than_6">More than 6 hrs</option>
          <option value="within_6">Within 6 hrs</option>
          <option value="after_start">After start</option>
        </select>
      </div>

      <div>
        <Label>Reason Code</Label>
        <select v-model="form.reason_code_id" class="w-full border rounded px-2 py-1">
          <option v-for="reason in reasons" :key="reason.id" :value="reason.id">
            {{ reason.reason_code }}
          </option>
        </select>
      </div>

      <div class="flex items-center space-x-2">
        <Checkbox v-model:checked="form.disputed" />
        <Label>Disputed</Label>
      </div>

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
