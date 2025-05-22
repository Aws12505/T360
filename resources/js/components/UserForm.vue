<template>
  <!-- Modal overlay for user form - fixed to cover entire viewport -->
  <div class="fixed inset-0 z-[100]">
    <!-- Add a semi-transparent background overlay -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="() => emit('close')"></div>
    <!-- Modal content container - centered with flex -->
    <div class="absolute inset-0 flex items-center justify-center p-2 sm:p-4">
      <div class="bg-background p-4 sm:p-6 rounded-lg shadow-xl w-full sm:max-w-lg md:max-w-2xl lg:max-w-4xl overflow-y-auto max-h-[95vh] animate-in fade-in zoom-in-95 duration-200 border border-border">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl sm:text-2xl font-bold text-foreground flex items-center">
            <UserIcon class="h-5 w-5 mr-2 text-primary" />
            {{ user ? 'Edit User' : 'Create User' }}
          </h2>
          <Button variant="ghost" size="icon" @click="() => emit('close')">
            <X class="h-5 w-5" />
          </Button>
        </div>
        
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Two-column layout for form fields -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name Field -->
            <div class="space-y-2">
              <Label for="name" class="flex items-center">
                <span>Name</span>
                <span class="text-destructive ml-1">*</span>
              </Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="Enter name"
                class="w-full"
                :class="{ 'border-destructive': form.errors.name }"
              />
              <InputError :message="form.errors.name" />
            </div>
            
            <!-- Email Field -->
            <div class="space-y-2">
              <Label for="email" class="flex items-center">
                <span>Email</span>
                <span class="text-destructive ml-1">*</span>
              </Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="Enter email"
                class="w-full"
                :class="{ 'border-destructive': form.errors.email }"
              />
              <InputError :message="form.errors.email" />
            </div>
            
            <!-- Password Field (shown only when creating a new user) -->
            <div class="space-y-2 md:col-span-2">
              <Label for="password" class="flex items-center">
                <span>Password</span>
                <span v-if="!user" class="text-destructive ml-1">*</span>
              </Label>
              <div class="relative">
                <Input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Enter password"
                  class="w-full pr-10"
                  :class="{ 'border-destructive': form.errors.password }"
                />
                <button 
                  type="button" 
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                  <Eye v-if="!showPassword" class="h-5 w-5" />
                  <EyeOff v-else class="h-5 w-5" />
                </button>
              </div>
              <InputError :message="form.errors.password" />
            </div>
            
            <!-- Tenant Dropdown for SuperAdmin users -->
            <div v-if="isSuperAdmin" class="space-y-2 md:col-span-2">
              <Label for="tenant">Company Name</Label>
              <select
                id="tenant"
                v-model="form.tenant_id"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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
              <InputError :message="form.errors.tenant_id" />
            </div>
          </div>
          
          <!-- Roles and Permissions Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <!-- Roles Assignment Section -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <Label class="flex items-center">
                  <Shield class="h-4 w-4 mr-1 text-primary" />
                  <span>Roles</span>
                  <Badge variant="outline" class="ml-2">{{ form.roles.length }} selected</Badge>
                </Label>
              </div>
              <div class="relative">
                <div class="flex items-center space-x-2">
                  <div class="relative w-full">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                      v-model="roleSearch"
                      placeholder="Search roles..."
                      class="w-full pl-9"
                    />
                  </div>
                  <Button 
                    v-if="roleSearch" 
                    type="button" 
                    variant="ghost" 
                    size="icon" 
                    @click="roleSearch = ''" 
                    class="absolute right-2 top-2"
                  >
                    <X class="h-4 w-4" />
                  </Button>
                </div>
              </div>
              <div class="h-60 border rounded-md p-2 overflow-y-auto">
                <div
                  v-for="role in filteredRoles"
                  :key="role.id"
                  class="flex items-center py-1 px-2 hover:bg-muted/50 rounded transition-colors"
                >
                  <Checkbox
                    :id="`role-${role.id}`"
                    :checked="form.roles.includes(role.id)"
                    @update:checked="toggleRole(role.id)"
                    class="mr-2"
                  />
                  <Label :for="`role-${role.id}`" class="cursor-pointer">{{ role.name }}</Label>
                </div>
                
                <!-- Empty state when no roles match search -->
                <div v-if="filteredRoles.length === 0" class="flex flex-col items-center justify-center h-full text-muted-foreground">
                  <SearchX class="h-8 w-8 mb-2" />
                  <p>No roles match your search</p>
                </div>
              </div>
              <InputError :message="form.errors.roles" />
            </div>
            
            <!-- Permissions Assignment Section -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <Label class="flex items-center">
                  <Lock class="h-4 w-4 mr-1 text-primary" />
                  <span>Permissions</span>
                  <Badge variant="outline" class="ml-2">{{ selectedPermissionsCount }} selected</Badge>
                </Label>
                <div class="flex space-x-2">
                  <Button 
                    type="button" 
                    variant="outline" 
                    size="sm" 
                    @click="selectAllPermissions"
                    class="text-xs"
                  >
                    Select All
                  </Button>
                  <Button 
                    type="button" 
                    variant="outline" 
                    size="sm" 
                    @click="clearAllPermissions"
                    class="text-xs"
                  >
                    Clear All
                  </Button>
                </div>
              </div>
              <div class="relative">
                <div class="flex items-center space-x-2">
                  <div class="relative w-full">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                      v-model="permissionSearch"
                      placeholder="Search permissions..."
                      class="w-full pl-9"
                    />
                  </div>
                  <Button 
                    v-if="permissionSearch" 
                    type="button" 
                    variant="ghost" 
                    size="icon" 
                    @click="permissionSearch = ''" 
                    class="absolute right-2 top-2"
                  >
                    <X class="h-4 w-4" />
                  </Button>
                </div>
              </div>
              <div class="h-60 border rounded-md p-2 overflow-y-auto">
                <!-- Group permissions by category -->
                <div v-for="(group, groupName) in groupedPermissions" :key="groupName" class="mb-4">
                  <div class="flex items-center justify-between mb-1">
                    <div class="font-medium text-sm text-foreground flex items-center">
                      <FolderIcon class="h-4 w-4 mr-1 text-muted-foreground" />
                      {{ formatGroupName(groupName) }}
                      <Badge variant="secondary" class="ml-2">{{ group.length }}</Badge>
                    </div>
                    <div class="flex space-x-1">
                      <Button 
                        type="button" 
                        variant="ghost" 
                        size="sm" 
                        @click="selectGroupPermissions(groupName)"
                        class="text-xs h-6 px-2"
                      >
                        Select All
                      </Button>
                      <Button 
                        type="button" 
                        variant="ghost" 
                        size="sm" 
                        @click="clearGroupPermissions(groupName)"
                        class="text-xs h-6 px-2"
                      >
                        Clear
                      </Button>
                    </div>
                  </div>
                  <div
                    v-for="permission in group"
                    :key="permission.id"
                    class="flex items-center py-1 px-2 hover:bg-muted/50 rounded transition-colors"
                  >
                    <Checkbox
                      :id="`permission-${permission.name}`"
                      :checked="form.user_permissions.includes(permission.name)"
                      @update:checked="togglePermission(permission.name)"
                      :disabled="inheritedPermissions.includes(permission.name)"
                      class="mr-2"
                    />
                    <Label :for="`permission-${permission.name}`" class="cursor-pointer text-sm">
                      {{ formatPermissionName(permission.name) }}
                      <span v-if="inheritedPermissions.includes(permission.name)" class="ml-2 text-xs px-2 py-0.5 rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        inherited
                      </span>
                    </Label>
                  </div>
                </div>
                
                <!-- Empty state when no permissions match search -->
                <div v-if="Object.keys(groupedPermissions).length === 0" class="flex flex-col items-center justify-center h-full text-muted-foreground">
                  <SearchX class="h-8 w-8 mb-2" />
                  <p>No permissions match your search</p>
                </div>
              </div>
              <InputError :message="form.errors.user_permissions" />
            </div>
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
import { Badge } from '@/components/ui/badge';
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { 
  Eye, 
  EyeOff, 
  X, 
  Loader2, 
  User as UserIcon, 
  Shield, 
  Lock, 
  Search, 
  Folder as FolderIcon,
  SearchX
} from 'lucide-vue-next';

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
  password: props.user ? props.user.password : '',
  tenant_id: props.user ? props.user.tenant_id : props.tenants.length ? props.tenants[0].id : null,
  roles: props.user && props.user.roles ? props.user.roles.map((r: any) => r.id) : [],
  user_permissions: props.user && props.user.permissions ? props.user.permissions.map((p: any) => p.name) : [],
});

const roleSearch = ref('');
const permissionSearch = ref('');
// Add password visibility state
const showPassword = ref(false);

// Compute filtered roles based on search term.
const filteredRoles = computed(() => {
  if (!roleSearch.value.trim()) return props.roles;
  const term = roleSearch.value.toLowerCase();
  return props.roles.filter((role: any) =>
    role.name.toLowerCase().includes(term)
  );
});

// Count of selected permissions
const selectedPermissionsCount = computed(() => form.user_permissions.length);

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
    form.password = newVal.password;
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

// Add these toggle functions
const toggleRole = (roleId) => {
  const index = form.roles.indexOf(roleId);
  if (index === -1) {
    form.roles.push(roleId);
  } else {
    form.roles.splice(index, 1);
  }
};

const togglePermission = (permissionName) => {
  if (inheritedPermissions.value.includes(permissionName)) {
    return; // Don't toggle inherited permissions
  }
  
  const index = form.user_permissions.indexOf(permissionName);
  if (index === -1) {
    form.user_permissions.push(permissionName);
  } else {
    form.user_permissions.splice(index, 1);
  }
};

// New functions for bulk selection
const selectAllPermissions = () => {
  // Add all permissions that aren't inherited
  props.permissions.forEach(permission => {
    if (!inheritedPermissions.value.includes(permission.name) && 
        !form.user_permissions.includes(permission.name)) {
      form.user_permissions.push(permission.name);
    }
  });
};

const clearAllPermissions = () => {
  form.user_permissions = [];
};

const selectGroupPermissions = (groupName: string) => {
  const groupPermissions = props.permissions
    .filter(p => p.name.startsWith(groupName + '.'))
    .map(p => p.name);
  
  // Add all permissions from this group that aren't already selected and aren't inherited
  groupPermissions.forEach(permName => {
    if (!form.user_permissions.includes(permName) && !inheritedPermissions.value.includes(permName)) {
      form.user_permissions.push(permName);
    }
  });
};

const clearGroupPermissions = (groupName: string) => {
  form.user_permissions = form.user_permissions.filter(
    permName => !permName.startsWith(groupName + '.')
  );
};

// Group permissions by their category
const groupedPermissions = computed(() => {
  const filtered = permissionSearch.value.trim() 
    ? props.permissions.filter((permission: any) => 
        permission.name.toLowerCase().includes(permissionSearch.value.toLowerCase())
      )
    : props.permissions;
    
  const groups: Record<string, any[]> = {};
  
  filtered.forEach((permission: any) => {
    const parts = permission.name.split('.');
    const groupName = parts[0];
    
    if (!groups[groupName]) {
      groups[groupName] = [];
    }
    
    groups[groupName].push(permission);
  });
  
  return groups;
});

// Format group name for display
const formatGroupName = (name: string) => {
  if (name === 'tenant-settings') return 'Tenant Settings';
  if (name === 'repair-orders') return 'Repair Orders';
  if (name === 'safety-data') return 'Safety Data';
  if (name === 'miles-driven') return 'Miles Driven';
  if (name === 'support-tickets') return 'Support Tickets';
  if (name === 'sms-coaching') return 'SMS Coaching';
  if (name === 'support-responses') return 'Support Responses';
  if (name === 'receive_updates') return 'General';
  
  return name.charAt(0).toUpperCase() + name.slice(1);
};

// Format permission name for display
const formatPermissionName = (name: string) => {
  const parts = name.split('.');
  if (parts.length > 1) {
    return parts[1].charAt(0).toUpperCase() + parts[1].slice(1).replace(/-/g, ' ');
  }
  return name;
};
</script>
