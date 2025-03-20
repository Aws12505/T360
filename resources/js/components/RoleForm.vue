<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-6 rounded shadow-lg w-96 max-h-[90vh] overflow-y-auto">
      <h2 class="text-xl font-bold mb-4">
        {{ role ? 'Edit Role' : 'Create Role' }}
      </h2>
      <form @submit.prevent="submit">
        <!-- Role Name Field -->
        <div class="mb-3">
          <label class="block mb-1">Role Name</label>
          <Input v-model="form.name" placeholder="Enter role name" />
        </div>
        <!-- Permissions Assignment with Search and Scrollable Container -->
        <div class="mb-3">
          <label class="block mb-1">Permissions</label>
          <Input
            v-model="permissionSearch"
            placeholder="Search permissions..."
            class="mb-2"
          />
          <div class="border p-2 max-h-40 overflow-y-auto">
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
              <span>{{ permission.name }}</span>
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
import { watch, ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';

// Define emits and props
const emit = defineEmits(['saved', 'close']);

const props = defineProps({
  role: { type: Object, default: null },
  permissions: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: {
    type: String,
    default: null,
  },
});
const { tenantSlug } = props;

// Initialize the form with useForm, using optional chaining to avoid calling map on undefined.
const form = useForm({
  name: props.role ? props.role.name : '',
  permissions: props.role?.permissions?.map((p: any) => p.name) || [],
});

// Local search state for permissions
const permissionSearch = ref('');

// Computed filtered permissions
const filteredPermissions = computed(() => {
  if (!permissionSearch.value.trim()) {
    return props.permissions;
  }
  const term = permissionSearch.value.toLowerCase();
  return props.permissions.filter((permission: any) =>
    permission.name.toLowerCase().includes(term)
  );
});

// Watch for changes in the role prop and update the form accordingly.
watch(() => props.role, (newVal) => {
  if (newVal) {
    form.name = newVal.name;
    form.permissions = newVal.permissions?.map((p: any) => p.name) || [];
  } else {
    form.name = '';
    form.permissions = [];
  }
});

// Submit function uses useForm's post/put methods and chooses routes based on isSuperAdmin.
const submit = () => {
  if (props.role) {
    if (props.isSuperAdmin) {
      form.put(route('admin.roles.update', props.role.id), {
        onSuccess: () => {
          emit('saved');
        },
      });
    } else {
      form.put(route('roles.update', [tenantSlug,props.role.id,]), {
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
      form.post(route('roles.store',tenantSlug), {
        onSuccess: () => {
          emit('saved');
        },
      });
    }
  }
};
</script>
