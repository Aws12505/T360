<script setup>
import { useForm } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import { Checkbox } from '@/Components/ui/checkbox'
import { Label } from '@/Components/ui/label'

const props = defineProps({
  delay: Object,
  delayCodes: Array,
  tenants: Array,
  isSuperAdmin: Boolean,
  tenantSlug: String,
})

const emit = defineEmits(['close'])

const form = useForm({
  id: props.delay?.id,
  tenant_id: props.isSuperAdmin ? props.delay?.tenant_id : null,
  date: props.delay?.date || '',
  driver_name: props.delay?.driver_name || '',
  delay_type: props.delay?.delay_type || 'origin',
  delay_category: props.delay?.delay_category || '1_120',
  delay_code_id: props.delay?.delay_code_id || null,
  disputed: !!props.delay?.disputed,
  driver_controllable:
    props.delay?.driver_controllable === 1 || props.delay?.driver_controllable === true
      ? true
      : props.delay?.driver_controllable === 0 || props.delay?.driver_controllable === false
      ? false
      : null,
})

const submit = () => {
  const isEdit = !!form.id
  const routeName = props.isSuperAdmin
    ? isEdit ? 'delays.update.admin' : 'delays.store.admin'
    : isEdit ? 'delays.update' : 'delays.store'

  const routeParams = props.isSuperAdmin
    ? isEdit
      ? { delay: form.id }
      : {}
    : isEdit
      ? { tenantSlug: props.tenantSlug, delay: form.id }
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
        <Label>Delay Type</Label>
        <select v-model="form.delay_type" class="w-full border rounded px-2 py-1">
          <option value="origin">Origin</option>
          <option value="destination">Destination</option>
        </select>
      </div>

      <div>
        <Label>Delay Category</Label>
        <select v-model="form.delay_category" class="w-full border rounded px-2 py-1">
          <option value="1_120">1–120 min</option>
          <option value="121_600">121–600 min</option>
          <option value="601_plus">601+ min</option>
        </select>
      </div>

      <div>
        <Label>Delay Code</Label>
        <select v-model="form.delay_code_id" class="w-full border rounded px-2 py-1">
          <option v-for="code in delayCodes" :key="code.id" :value="code.id">
            {{ code.code }}
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
