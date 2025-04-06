<template>
  <!-- Modal overlay for user form -->
  <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50 p-4">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-md overflow-y-auto max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ user ? 'Edit User' : 'Create User' }}
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
        <!-- Name Field -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Name
          </label>
          <Input
            v-model="form.name"
            placeholder="Enter name"
            class="w-full"
          />
          <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
        </div>
        
        <!-- Email Field -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Email
          </label>
          <Input
            v-model="form.email"
            type="email"
            placeholder="Enter email"
            class="w-full"
          />
          <div v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</div>
        </div>
        
        <!-- Password Field (shown only when creating a new user) -->
        <div v-if="!user" class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Password
          </label>
          <Input
            v-model="form.password"
            type="password"
            placeholder="Enter password"
            class="w-full"
          />
          <div v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</div>
        </div>
        
        <!-- Tenant Dropdown for SuperAdmin users -->
        <div v-if="isSuperAdmin" class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Company Name
          </label>
          <select
            v-model="form.tenant_id"
            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary"
          >
            <option :value="null">None</option>
            <option
              v-for="tenant in tenants"
              :value="tenant.id"
              :key="tenant.id"
            >
              {{ tenant.name }}
            </option>
          </select>
          <div v-if="form.errors.tenant_id" class="text-sm text-red-500">{{ form.errors.tenant_id }}</div>
        </div>
        
        <!-- Roles Assignment Section -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Roles
          </label>
          <div class="relative">
            <Input
              v-model="roleSearch"
              placeholder="Search roles..."
              class="w-full mb-2"
            />
            <div v-if="roleSearch" class="absolute right-2 top-1/2 -translate-y-1/2">
              <button 
                type="button" 
                @click="roleSearch = ''" 
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
              v-for="role in filteredRoles"
              :key="role.id"
              class="flex items-center py-1 px-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition-colors"
            >
              <input
                type="checkbox"
                :value="role.id"
                v-model="form.roles"
                class="mr-2 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
              <span class="text-gray-700 dark:text-gray-300">{{ role.name }}</span>
            </div>
          </div>
          <div v-if="form.errors.roles" class="text-sm text-red-500">{{ form.errors.roles }}</div>
        </div>
        
        <!-- Permissions Assignment Section -->
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
                v-model="form.user_permissions"
                class="mr-2 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                :disabled="inheritedPermissions.includes(permission.name)"
                :checked="inheritedPermissions.includes(permission.name) || form.user_permissions.includes(permission.name)"
              />
              <span class="text-gray-700 dark:text-gray-300">
                {{ permission.name }}
                <span v-if="inheritedPermissions.includes(permission.name)" class="text-xs text-gray-500 ml-1">
                  (inherited)
                </span>
              </span>
            </div>
          </div>
          <div v-if="form.errors.user_permissions" class="text-sm text-red-500">{{ form.errors.user_permissions }}</div>
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
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components from correct paths
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';

const props = defineProps({
  user: { type: Object, default: null },
  tenants: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
  permissions: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
});
const emit = defineEmits(['close', 'saved']);

// Initialize the form with existing user data (or defaults)
const form = useForm({
  name: props.user ? props.user.name : '',
  email: props.user ? props.user.email : '',
  password: '',
  tenant_id: props.user ? props.user.tenant_id : props.tenants.length ? props.tenants[0].id : null,
  roles: props.user && props.user.roles ? props.user.roles.map((r: any) => r.id) : [],
  user_permissions: props.user && props.user.permissions ? props.user.permissions.map((p: any) => p.name) : [],
});

const roleSearch = ref('');
const permissionSearch = ref('');

// Compute filtered roles based on search term.
const filteredRoles = computed(() => {
  if (!roleSearch.value.trim()) return props.roles;
  const term = roleSearch.value.toLowerCase();
  return props.roles.filter((role: any) =>
    role.name.toLowerCase().includes(term)
  );
});

// Compute filtered permissions based on search term.
const filteredPermissions = computed(() => {
  if (!permissionSearch.value.trim()) return props.permissions;
  const term = permissionSearch.value.toLowerCase();
  return props.permissions.filter((permission: any) =>
    permission.name.toLowerCase().includes(term)
  );
});

// Compute inherited permissions from selected roles.
const inheritedPermissions = computed(() => {
  let perms: string[] = [];
  props.roles.forEach(role => {
    if (form.roles.includes(role.id) && role.permissions && Array.isArray(role.permissions)) {
      perms = perms.concat(role.permissions.map((p: any) => p.name));
    }
  });
  return Array.from(new Set(perms));
});

// Watch for changes in the user prop to update the form accordingly.
watch(() => props.user, (newVal) => {
  if (newVal) {
    form.name = newVal.name;
    form.email = newVal.email;
    form.tenant_id = newVal.tenant_id;
    form.roles = newVal.roles ? newVal.roles.map((r: any) => r.id) : [];
    form.user_permissions = newVal.permissions ? newVal.permissions.map((p: any) => p.name) : [];
  } else {
    form.name = '';
    form.email = '';
    form.password = '';
    form.tenant_id = props.tenants.length ? props.tenants[0].id : null;
    form.roles = [];
    form.user_permissions = [];
  }
}, { immediate: true });

const submit = () => {
  if (props.user) {
    if (props.isSuperAdmin) {
      form.put(route('admin.users.update', props.user), {
        onSuccess: () => {
          emit('saved');
        },
      });
    } else {
      form.put(route('users.update', [props.tenantSlug, props.user]), {
        onSuccess: () => {
          emit('saved');
        },
      });
    }
  } else {
    if (props.isSuperAdmin) {
      form.post(route('admin.users.store'), {
        onSuccess: () => {
          emit('saved');
        },
      });
    } else {
      form.post(route('users.store', props.tenantSlug), {
        onSuccess: () => {
          emit('saved');
        },
      });
    }
  }
};
</script>
