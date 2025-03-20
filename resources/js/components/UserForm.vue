<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-6 rounded shadow-lg w-96 max-h-[90vh] overflow-y-auto">
      <h2 class="text-xl font-bold mb-4">
        {{ user ? 'Edit User' : 'Create User' }}
      </h2>
      <form @submit.prevent="submit">
        <!-- Name Field -->
        <div class="mb-3">
          <label class="block mb-1">Name</label>
          <Input v-model="form.name" placeholder="Enter name" />
        </div>
        <!-- Email Field -->
        <div class="mb-3">
          <label class="block mb-1">Email</label>
          <Input v-model="form.email" type="email" placeholder="Enter email" />
        </div>
        <!-- Password Field (only when creating a new user) -->
        <div class="mb-3" v-if="!user">
          <label class="block mb-1">Password</label>
          <Input v-model="form.password" type="password" placeholder="Enter password" />
        </div>
        <!-- Tenant Dropdown for SuperAdmin with a "None" option -->
        <div class="mb-3" v-if="isSuperAdmin">
          <label class="block mb-1">Tenant</label>
          <select v-model="form.tenant_id" class="w-full border px-2 py-1">
            <option :value="null">None</option>
            <option v-for="tenant in tenants" :value="tenant.id" :key="tenant.id">
              {{ tenant.name }}
            </option>
          </select>
        </div>
        <!-- Roles Assignment with Search and Scrollable Container -->
        <div class="mb-3">
          <label class="block mb-1">Roles</label>
          <Input v-model="roleSearch" placeholder="Search roles..." class="mb-2" />
          <div class="border p-2 max-h-40 overflow-y-auto">
            <div v-for="role in filteredRoles" :key="role.id" class="flex items-center mb-1">
              <input
                type="checkbox"
                :value="role.id"
                v-model="form.roles"
                class="mr-2"
              />
              <span>{{ role.name }}</span>
            </div>
          </div>
        </div>
        <!-- Permissions Assignment with Search and Scrollable Container -->
        <div class="mb-3">
          <label class="block mb-1">Permissions</label>
          <Input v-model="permissionSearch" placeholder="Search permissions..." class="mb-2" />
          <div class="border p-2 max-h-40 overflow-y-auto">
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
              <span>
                {{ permission.name }}
                <span v-if="inheritedPermissions.includes(permission.name)" class="text-xs text-gray-500">(inherited)</span>
              </span>
            </div>
          </div>
        </div>
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-2">
          <Button type="button" @click="() => emit('close')">Cancel</Button>
          <Button type="submit">Save</Button>
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

// Define props and emits
const props = defineProps({
  user: { type: Object, default: null },
  tenants: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
  permissions: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: {
    type: String,
    default: null,
  },
});
const emit = defineEmits(['close', 'saved']);
const { tenantSlug } = props;

// Initialize the form with useForm.
// On edit, prepopulate roles and direct permissions.
const form = useForm({
  name: props.user ? props.user.name : '',
  email: props.user ? props.user.email : '',
  password: '',
  tenant_id: props.user
    ? props.user.tenant_id
    : props.tenants.length
    ? props.tenants[0].id
    : null,
  roles: props.user && props.user.roles ? props.user.roles.map((r: any) => r.id) : [],
  user_permissions: props.user && props.user.permissions ? props.user.permissions.map((p: any) => p.name) : [],
});

// Local search states for roles and permissions
const roleSearch = ref('');
const permissionSearch = ref('');

// Computed filtered roles
const filteredRoles = computed(() => {
  if (!roleSearch.value.trim()) return props.roles;
  const term = roleSearch.value.toLowerCase();
  return props.roles.filter((role: any) =>
    role.name.toLowerCase().includes(term)
  );
});

// Computed filtered permissions
const filteredPermissions = computed(() => {
  if (!permissionSearch.value.trim()) return props.permissions;
  const term = permissionSearch.value.toLowerCase();
  return props.permissions.filter((permission: any) =>
    permission.name.toLowerCase().includes(term)
  );
});

// Computed inherited permissions based on selected roles.
// Assumes each role object has a "permissions" property (an array of objects with a "name").
const inheritedPermissions = computed(() => {
  let perms: string[] = [];
  props.roles.forEach(role => {
    if (form.roles.includes(role.id) && role.permissions && Array.isArray(role.permissions)) {
      perms = perms.concat(role.permissions.map((p: any) => p.name));
    }
  });
  return Array.from(new Set(perms));
});

// Watch for changes in the user prop and update form values (with immediate update)
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

// Submit function uses useForm's post/put methods and selects routes based on isSuperAdmin.
const submit = () => {
  if (props.user) {
    if (props.isSuperAdmin) {
      form.put(route('admin.users.update', props.user), {
        onSuccess: () => {
          emit('saved');
        },
      });
    } else {
      form.put(route('users.update', [tenantSlug,props.user]), {
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
      form.post(route('users.store',tenantSlug), {
        onSuccess: () => {
          emit('saved');
        },
      });
    }
  }
};
</script>
