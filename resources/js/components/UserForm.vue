<template>
  <!-- Modal overlay for user form -->
  <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50 p-4">
    <div class="bg-background p-6 rounded-lg shadow-xl w-full max-w-md overflow-y-auto max-h-[90vh] animate-in fade-in zoom-in-95 duration-200 border border-border">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-foreground">
          {{ user ? 'Edit User' : 'Create User' }}
        </h2>
        <Button variant="ghost" size="icon" @click="() => emit('close')">
          <X class="h-5 w-5" />
        </Button>
      </div>
      
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Name Field -->
        <div class="space-y-2">
          <Label for="name">Name</Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="Enter name"
            class="w-full"
          />
          <InputError :message="form.errors.name" />
        </div>
        
        <!-- Email Field -->
        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Enter email"
            class="w-full"
          />
          <InputError :message="form.errors.email" />
        </div>
        
        <!-- Password Field (shown only when creating a new user) -->
        <div v-if="!user" class="space-y-2">
          <Label for="password">Password</Label>
          <Input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Enter password"
            class="w-full"
          />
          <InputError :message="form.errors.password" />
        </div>
        
        <!-- Tenant Dropdown for SuperAdmin users -->
        <div v-if="isSuperAdmin" class="space-y-2">
          <Label for="tenant">Company Name</Label>
          <Select v-model="form.tenant_id">
            <SelectTrigger>
              <SelectValue placeholder="Select a company" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem :value="null">None</SelectItem>
              <SelectItem
                v-for="tenant in tenants"
                :value="tenant.id"
                :key="tenant.id"
              >
                {{ tenant.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <InputError :message="form.errors.tenant_id" />
        </div>
        
        <!-- Roles Assignment Section -->
        <div class="space-y-2">
          <Label>Roles</Label>
          <div class="relative">
            <div class="flex items-center space-x-2">
              <Input
                v-model="roleSearch"
                placeholder="Search roles..."
                class="w-full"
              />
              <Button 
                v-if="roleSearch" 
                type="button" 
                variant="ghost" 
                size="icon" 
                @click="roleSearch = ''" 
                class="absolute right-2"
              >
                <X class="h-4 w-4" />
              </Button>
            </div>
          </div>
          <div class="h-40 border rounded-md p-2 overflow-y-auto">
            <div
              v-for="role in filteredRoles"
              :key="role.id"
              class="flex items-center py-1 px-2 hover:bg-muted/50 rounded transition-colors"
            >
              <Checkbox
                :id="`role-${role.id}`"
                :value="role.id"
                v-model:checked="form.roles"
                class="mr-2"
              />
              <Label :for="`role-${role.id}`" class="cursor-pointer">{{ role.name }}</Label>
            </div>
          </div>
          <InputError :message="form.errors.roles" />
        </div>
        
        <!-- Permissions Assignment Section -->
        <div class="space-y-2">
          <Label>Permissions</Label>
          <div class="relative">
            <div class="flex items-center space-x-2">
              <Input
                v-model="permissionSearch"
                placeholder="Search permissions..."
                class="w-full"
              />
              <Button 
                v-if="permissionSearch" 
                type="button" 
                variant="ghost" 
                size="icon" 
                @click="permissionSearch = ''" 
                class="absolute right-2"
              >
                <X class="h-4 w-4" />
              </Button>
            </div>
          </div>
          <div class="h-40 border rounded-md p-2 overflow-y-auto">
            <div
              v-for="permission in filteredPermissions"
              :key="permission.id"
              class="flex items-center py-1 px-2 hover:bg-muted/50 rounded transition-colors"
            >
              <Checkbox
                :id="`permission-${permission.name}`"
                :value="permission.name"
                v-model:checked="form.user_permissions"
                :disabled="inheritedPermissions.includes(permission.name)"
                class="mr-2"
              />
              <Label :for="`permission-${permission.name}`" class="cursor-pointer">
                {{ permission.name }}
                <span v-if="inheritedPermissions.includes(permission.name)" class="ml-2 text-xs px-2 py-0.5 rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                  inherited
                </span>
              </Label>
            </div>
          </div>
          <InputError :message="form.errors.user_permissions" />
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
          <Button
            type="button"
            @click="() => emit('close')"
            variant="outline"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
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
// Import UI components
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { X, Loader2 } from 'lucide-vue-next';

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
  const permissions = new Set();
  props.roles.forEach(role => {
    if (form.roles.includes(role.id) && role.permissions) {
      role.permissions.forEach(permission => {
        permissions.add(permission.name);
      });
    }
  });
  
  return Array.from(permissions);
});

// Watch for changes to the user prop to update the form
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
