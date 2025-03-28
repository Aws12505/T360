<template>
  <div>
    <!-- Form to add a new code -->
    <form @submit.prevent="submit" class="flex space-x-2 mb-4">
      <Input v-model="input" :placeholder="`Enter new ${label}...`" class="w-full" />
      <Button type="submit" :disabled="form.processing">Add</Button>
    </form>

    <!-- List of existing codes -->
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

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components from correct folders
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';

const props = defineProps({
  model: { type: String, required: true }, // e.g., "rejection_reason_codes" or "delay_codes"
  label: { type: String, required: true },
  codes: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
});
const emit = defineEmits(['refresh']);

// Determine the field to use based on the model.
const field = computed(() => (props.model === 'delay_codes' ? 'code' : 'reason_code'));

// Initialize form for adding a new code.
const form = useForm({
  code: '',
  reason_code: '',
});

// Local reactive variable for the input field.
const input = ref('');

// Initialize a form for deletion.
const deleteForm = useForm({});

// Submit new code
const submit = () => {
  form[field.value] = input.value;
  const routeName = props.isSuperAdmin
    ? `${props.model}.store.admin`
    : `${props.model}.store`;
  const routeParams = props.isSuperAdmin ? [] : [props.tenantSlug];
  form.post(route(routeName, ...routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      input.value = '';
      emit('refresh');
    },
  });
};

// Delete a code
const deleteCode = (id: number) => {
  const routeName = props.isSuperAdmin
    ? `${props.model}.destroy.admin`
    : `${props.model}.destroy`;
  const routeParams = props.isSuperAdmin ? [id] : [props.tenantSlug, id];
  deleteForm.delete(route(routeName, ...routeParams), {
    preserveScroll: true,
    onSuccess: () => emit('refresh'),
  });
};
</script>
