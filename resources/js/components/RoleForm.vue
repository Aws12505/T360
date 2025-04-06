<template>
  <!-- Modal overlay -->
  <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50 p-4">
    <!-- Modal container with improved spacing and shadows -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-md overflow-y-auto max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ role ? 'Edit Role' : 'Create Role' }}
        </h2>
        <button 
          @click="() => emit('close')" 
          class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Role Name Field -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Role Name
          </label>
          <Input
            v-model="form.name"
            placeholder="Enter role name"
            class="w-full"
          />
          <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
        </div>
        
        <!-- Permissions Assignment with Search and Scrollable Container -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Permissions
          </label>
          <div class="relative">
            <Input
              v-model="permissionSearch"
              placeholder="Search permissions..."
              class="w-full mb-2"
            />
            <div v-if="permissionSearch" class="absolute right-2 top-1/2 -translate-y-1/2">
              <button 
                type="button" 
                @click="permissionSearch = ''" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <div class="border border-gray-300 dark:border-gray-600 rounded-md p-2 max-h-40 overflow-y-auto">
            <div
              v-for="permission in filteredPermissions"
              :key="permission.id"
              class="flex items-center py-1 px-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition-colors"
            >
              <input
                type="checkbox"
                :value="permission.name"
                v-model="form.permissions"
                class="mr-2 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
              <span class="text-gray-700 dark:text-gray-300">
                {{ permission.name }}
              </span>
            </div>
          </div>
          <div v-if="form.errors.permissions" class="text-sm text-red-500">{{ form.errors.permissions }}</div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
          <Button
            type="button"
            @click="() => emit('close')"
            variant="outline"
            class="border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
            class="bg-primary hover:bg-primary/90 text-white"
          >
            {{ form.processing ? 'Saving...' : 'Save' }}
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
