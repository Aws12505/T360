<template>
  <!-- Modal container with background overlay -->
  <div class="fixed inset-0 z-50">
    <!-- Add a semi-transparent background overlay -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <!-- Modal content container - centered with flex -->
    <div class="absolute inset-0 flex items-center justify-center p-2 sm:p-4">
      <div class="bg-background p-4 sm:p-6 rounded-lg shadow-xl w-full max-w-sm sm:max-w-md md:max-w-lg overflow-y-auto max-h-[95vh] animate-in fade-in zoom-in-95 duration-200 border border-border">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl sm:text-2xl font-bold text-foreground flex items-center">
            <Shield class="h-5 w-5 mr-2 text-primary" />
            {{ role ? 'Edit Role' : 'Create Role' }}
          </h2>
          <Button variant="ghost" size="icon" @click="() => emit('close')">
            <X class="h-5 w-5" />
          </Button>
        </div>
        
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Role Name Field -->
          <div class="space-y-2">
            <Label for="name" class="flex items-center">
              <span>Role Name</span>
              <span class="text-destructive ml-1">*</span>
            </Label>
            <Input
              id="name"
              v-model="form.name"
              placeholder="Enter role name"
              class="w-full"
              :class="{ 'border-destructive': form.errors.name }"
              autofocus
            />
            <InputError :message="form.errors.name" />
          </div>
          
          <!-- Permissions Assignment with Search and Scrollable Container -->
          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <Label class="flex items-center">
                <span>Permissions</span>
                <Badge variant="outline" class="ml-2">{{ selectedCount }} selected</Badge>
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
            
            <div class="h-48 sm:h-56 md:h-64 border rounded-md p-2 overflow-y-auto">
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
                    :checked="form.permissions.includes(permission.name)"
                    @update:checked="togglePermission(permission.name)"
                    class="mr-2"
                  />
                  <Label :for="`permission-${permission.name}`" class="cursor-pointer text-sm">
                    {{ formatPermissionName(permission.name) }}
                  </Label>
                </div>
              </div>
              
              <!-- Empty state when no permissions match search -->
              <div v-if="Object.keys(groupedPermissions).length === 0" class="flex flex-col items-center justify-center h-full text-muted-foreground">
                <SearchX class="h-8 w-8 mb-2" />
                <p>No permissions match your search</p>
              </div>
            </div>
            <InputError :message="form.errors.permissions" />
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
import { watch, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import { X, Loader2, Shield, Search, Folder as FolderIcon, SearchX } from 'lucide-vue-next';

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

// Count of selected permissions
const selectedCount = computed(() => form.permissions.length);

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

// Watch for changes in props.role to update the form values.
watch(() => props.role, (newVal) => {
  if (newVal) {
    form.name = newVal.name;
    form.permissions = newVal.permissions?.map((p: any) => p.name) || [];
  } else {
    form.name = '';
    form.permissions = [];
  }
}, { immediate: true });

// Add this function to toggle permissions
const togglePermission = (permissionName: string) => {
  const index = form.permissions.indexOf(permissionName);
  if (index === -1) {
    form.permissions.push(permissionName);
  } else {
    form.permissions.splice(index, 1);
  }
};

// New functions for bulk selection
const selectAllPermissions = () => {
  form.permissions = props.permissions.map(p => p.name);
};

const clearAllPermissions = () => {
  form.permissions = [];
};

const selectGroupPermissions = (groupName: string) => {
  const groupPermissions = props.permissions
    .filter(p => p.name.startsWith(groupName + '.'))
    .map(p => p.name);
  
  // Add all permissions from this group that aren't already selected
  groupPermissions.forEach(permName => {
    if (!form.permissions.includes(permName)) {
      form.permissions.push(permName);
    }
  });
};

const clearGroupPermissions = (groupName: string) => {
  form.permissions = form.permissions.filter(
    permName => !permName.startsWith(groupName + '.')
  );
};

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
