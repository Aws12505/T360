<template>
  <!-- Modal overlay -->
  <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50 p-4">
    <!-- Modal container -->
    <div class="bg-background p-6 rounded-lg shadow-xl w-full max-w-md overflow-y-auto max-h-[90vh] animate-in fade-in zoom-in-95 duration-200 border border-border">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-foreground">
          {{ role ? 'Edit Role' : 'Create Role' }}
        </h2>
        <Button variant="ghost" size="icon" @click="() => emit('close')">
          <X class="h-5 w-5" />
        </Button>
      </div>
      
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Role Name Field -->
        <div class="space-y-2">
          <Label for="name">Role Name</Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="Enter role name"
            class="w-full"
          />
          <InputError :message="form.errors.name" />
        </div>
        
        <!-- Permissions Assignment with Search and Scrollable Container -->
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
                :checked="form.permissions.includes(permission.name)"
                @update:checked="togglePermission(permission.name)"
                class="mr-2"
              />
              <Label :for="`permission-${permission.name}`" class="cursor-pointer">
                {{ permission.name }}
              </Label>
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
</template>

<script setup lang="ts">
import { watch, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import { X, Loader2 } from 'lucide-vue-next';

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
}, { immediate: true });

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

// Add this function to toggle permissions
const togglePermission = (permissionName: string) => {
  const index = form.permissions.indexOf(permissionName);
  if (index === -1) {
    form.permissions.push(permissionName);
  } else {
    form.permissions.splice(index, 1);
  }
};
</script>
