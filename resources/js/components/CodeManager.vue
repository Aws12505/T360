<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'

const props = defineProps({
  model: String,
  codes: Array,
  label: String,
  isSuperAdmin: Boolean,
  tenantSlug: String,
})

const emit = defineEmits(['refresh'])

const field = computed(() =>
  props.model === 'delay_codes' ? 'code' : 'reason_code'
)

const form = useForm({
  code: '',
  reason_code: '',
})

const deleteForm = useForm()
const input = ref('')

const submit = () => {
  form[field.value] = input.value

  const routeName = props.isSuperAdmin
    ? `${props.model}.store.admin`
    : `${props.model}.store`

  const routeParams = props.isSuperAdmin ? [] : [props.tenantSlug]

  form.post(route(routeName, ...routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      input.value = ''
      emit('refresh')
    },
  })
}

const deleteCode = (id) => {
  const routeName = props.isSuperAdmin
    ? `${props.model}.destroy.admin`
    : `${props.model}.destroy`

  const routeParams = props.isSuperAdmin ? [id] : [props.tenantSlug, id]

  deleteForm.delete(route(routeName, ...routeParams), {
    preserveScroll: true,
    onSuccess: () => emit('refresh'),
  })
}
</script>

<template>
  <div >
    <form @submit.prevent="submit" class="flex space-x-2 mb-4">
      <Input v-model="input" :placeholder="`Enter new ${label}...`" />
      <Button type="submit" :disabled="form.processing">Add</Button>
    </form>

    <ul class="space-y-2">
      <li
        v-for="item in codes"
        :key="item.id"
        class="flex justify-between items-center border p-2 rounded"
      >
        <span>{{ item.code || item.reason_code }}</span>
        <Button
          variant="destructive"
          size="sm"
          @click="deleteCode(item.id)"
          :disabled="deleteForm.processing"
        >
          Delete
        </Button>
      </li>
    </ul>
  </div>
</template>
