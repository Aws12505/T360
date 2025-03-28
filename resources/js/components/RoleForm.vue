<template>
  <!-- Modal overlay -->
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <!-- Modal container with improved spacing and shadows -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md overflow-y-auto">
      <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
        {{ role ? 'Edit Role' : 'Create Role' }}
      </h2>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Role Name Field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Role Name
          </label>
          <Input
            v-model="form.name"
            placeholder="Enter role name"
            class="w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:ring-blue-500"
          />
        </div>
        <!-- Permissions Assignment with Search and Scrollable Container -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Permissions
          </label>
          <Input
            v-model="permissionSearch"
            placeholder="Search permissions..."
            class="mb-2 w-full rounded-md border border-gray-300 dark:border-gray-600"
          />
          <div class="border border-gray-300 dark:border-gray-600 rounded-md p-2 max-h-40 overflow-y-auto">
            <div
              v-for="permission in filteredPermissions"
              :key="permission.id"
              class="flex items-center mb-1"
            >
              <input
                type="checkbox"
                :value="permission.name"
                v-model="form.permissions"
                class="mr-2"
              />
              <span class="text-gray-700 dark:text-gray-300">
                {{ permission.name }}
              </span>
            </div>
          </div>
        </div>
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
          <Button
            type="button"
            @click="() => emit('close')"
            class="bg-gray-500 hover:bg-gray-600 text-white rounded px-4 py-2 transition"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white rounded px-4 py-2 transition"
          >
            Save
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
// Import Vue composition API functions
import { watch, ref, computed } from 'vue';
// Import Inertia form helper
import { useForm } from '@inertiajs/vue3';
// Import ShadCN UI components from their correct paths
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';

const emit = defineEmits(['saved', 'close']);

const props = defineProps({
  role: { type: Object, default: null },
  permissions: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
});
const { tenantSlug } = props;

// Initialize form using Inertia's useForm helper.
const form = useForm({
  name: props.role ? props.role.name : '',
  permissions: props.role?.permissions?.map((p: any) => p.name) || [],
});

const permissionSearch = ref('');

// Compute filtered permissions based on the search term.
const filteredPermissions = computed(() => {
  if (!permissionSearch.value.trim()) {
    return props.permissions;
  }
  const term = permissionSearch.value.toLowerCase();
  return props.permissions.filter((permission: any) =>
    permission.name.toLowerCase().includes(term)
  );
});

// Watch for changes in props.role to update the form values.
watch(() => props.role, (newVal) => {
  if (newVal) {
    form.name = newVal.name;
    form.permissions = newVal.permissions?.map((p: any) => p.name) || [];
  } else {
    form.name = '';
    form.permissions = [];
  }
});

const submit = () => {
  if (props.role) {
    if (props.isSuperAdmin) {
      form.put(route('admin.roles.update', props.role.id), {
        onSuccess: () => {
          emit('saved');
        },
      });
    } else {
      form.put(route('roles.update', [tenantSlug, props.role.id]), {
        onSuccess: () => {
          emit('saved');
        },
      });
    }
  } else {
    if (props.isSuperAdmin) {
      form.post(route('admin.roles.store'), {
        onSuccess: () => {
          emit('saved');
        },
      });
    } else {
      form.post(route('roles.store', tenantSlug), {
        onSuccess: () => {
          emit('saved');
        },
      });
    }
  }
};
</script>
