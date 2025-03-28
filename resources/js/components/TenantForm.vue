<template>
  <!-- Modal overlay -->
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <!-- Modal container -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md overflow-y-auto">
      <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
        {{ tenant ? 'Edit Tenant' : 'Create Tenant' }}
      </h2>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Name Field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Name
          </label>
          <Input
            v-model="form.name"
            placeholder="Enter tenant name"
            class="w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:ring-blue-500"
          />
        </div>
        <!-- Slug Field (only for editing) -->
        <div v-if="tenant">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Slug
          </label>
          <Input
            v-model="form.slug"
            placeholder="Enter tenant slug"
            class="w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:ring-blue-500"
          />
        </div>
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
          <Button
            type="button"
            @click="() => emit('close')"
            class="bg-gray-500 hover:bg-gray-600 text-white rounded px-4 py-2"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white rounded px-4 py-2"
          >
            Save
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components from their correct paths
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';

const props = defineProps({
  tenant: { type: Object, default: null },
});
const emit = defineEmits(['close', 'saved']);

// Initialize form with tenant data if available
const form = useForm({
  name: props.tenant ? props.tenant.name : '',
  slug: props.tenant ? props.tenant.slug : '',
});

watch(() => props.tenant, (newVal) => {
  if (newVal) {
    form.name = newVal.name;
    form.slug = newVal.slug;
  } else {
    form.name = '';
    form.slug = '';
  }
}, { immediate: true });

const submit = () => {
  if (props.tenant) {
    form.put(route('admin.tenants.update', props.tenant), {
      onSuccess: () => {
        emit('saved');
      },
    });
  } else {
    form.post(route('admin.tenants.store'), {
      onSuccess: () => {
        emit('saved');
      },
    });
  }
};
</script>
