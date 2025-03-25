<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md overflow-y-auto">
      <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">
        {{ user ? 'Edit User' : 'Create User' }}
      </h2>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Name Field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Name
          </label>
          <Input
            v-model="form.name"
            placeholder="Enter name"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:ring-blue-500"
          />
        </div>
        <!-- Email Field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Email
          </label>
          <Input
            v-model="form.email"
            type="email"
            placeholder="Enter email"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:ring-blue-500"
          />
        </div>
        <!-- Password Field (only when creating a new user) -->
        <div v-if="!user">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Password
          </label>
          <Input
            v-model="form.password"
            type="password"
            placeholder="Enter password"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:ring-blue-500"
          />
        </div>
        <!-- Tenant Dropdown for SuperAdmin -->
        <div v-if="isSuperAdmin">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Tenant
          </label>
          <select
            v-model="form.tenant_id"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 px-3 py-2"
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
        </div>
        <!-- Roles Assignment with Search and Scrollable Container -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Roles
          </label>
          <Input
            v-model="roleSearch"
            placeholder="Search roles..."
            class="mb-2 w-full rounded-md border-gray-300 dark:border-gray-600"
          />
          <div class="border border-gray-300 dark:border-gray-600 rounded-md p-2 max-h-40 overflow-y-auto">
            <div
              v-for="role in filteredRoles"
              :key="role.id"
              class="flex items-center mb-1"
            >
              <input
                type="checkbox"
                :value="role.id"
                v-model="form.roles"
                class="mr-2"
              />
              <span class="text-gray-700 dark:text-gray-300">{{ role.name }}</span>
            </div>
          </div>
        </div>
        <!-- Permissions Assignment with Search and Scrollable Container -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Permissions
          </label>
          <Input
            v-model="permissionSearch"
            placeholder="Search permissions..."
            class="mb-2 w-full rounded-md border-gray-300 dark:border-gray-600"
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
                v-model="form.user_permissions"
                class="mr-2"
                :disabled="inheritedPermissions.includes(permission.name)"
                :checked="inheritedPermissions.includes(permission.name) || form.user_permissions.includes(permission.name)"
              />
              <span class="text-gray-700 dark:text-gray-300">
                {{ permission.name }}
                <span
                  v-if="inheritedPermissions.includes(permission.name)"
                  class="text-xs text-gray-500"
                >
                  (inherited)
                </span>
              </span>
            </div>
          </div>
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
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
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

const filteredRoles = computed(() => {
  if (!roleSearch.value.trim()) return props.roles;
  const term = roleSearch.value.toLowerCase();
  return props.roles.filter((role: any) =>
    role.name.toLowerCase().includes(term)
  );
});

const filteredPermissions = computed(() => {
  if (!permissionSearch.value.trim()) return props.permissions;
  const term = permissionSearch.value.toLowerCase();
  return props.permissions.filter((permission: any) =>
    permission.name.toLowerCase().includes(term)
  );
});

const inheritedPermissions = computed(() => {
  let perms: string[] = [];
  props.roles.forEach(role => {
    if (form.roles.includes(role.id) && role.permissions && Array.isArray(role.permissions)) {
      perms = perms.concat(role.permissions.map((p: any) => p.name));
    }
  });
  return Array.from(new Set(perms));
});

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
