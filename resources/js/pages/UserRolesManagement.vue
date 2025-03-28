<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
// Import the layout
import AppLayout from '@/layouts/AppLayout.vue';
// Import the type for breadcrumbs
import { type BreadcrumbItem } from '@/types';

// Import ShadCN UI components from their correct paths
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';

// Import custom components for lists and forms
import UserList from '@/components/UserList.vue';
import RoleList from '@/components/RoleList.vue';
import UserForm from '@/components/UserForm.vue';
import TenantList from '@/components/TenantList.vue';
import TenantForm from '@/components/TenantForm.vue';
import RoleForm from '@/components/RoleForm.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';

// Define props received from the backend via Inertia
const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  roles: Array,
  tenants: Array,
  permissions: Array,
  search: String,
  tenantSlug: {
    type: String,
    default: null,
  },
});
const { tenantSlug } = props;

// Set up breadcrumbs (if tenantSlug exists, use it to create a tenant-specific dashboard link)
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? `/${tenantSlug}/dashboard` : '/dashboard',
  },
];

// Reactive state variables
const search = ref(props.search || '');
const showUserModal = ref(false);
const showRoleModal = ref(false);
const showTenantModal = ref(false);
const selectedUser = ref(null);
const selectedRole = ref(null);
const selectedTenant = ref(null);

// Determine if the current user is a SuperAdmin by checking if tenants array is non-empty (from backend)
const isSuperAdmin = ref(props.tenants.length > 0);

// Reactive variables for deletion confirmation modal
const showConfirmDelete = ref(false);
const deleteUrl = ref('');
const deleteMessage = ref('');

// Computed property for filtering the paginated users based on the search term.
// Returns an object with the same structure as props.users but with filtered data.
const filteredUsers = computed(() => {
  if (!search.value.trim()) {
    return props.users;
  }
  const term = search.value.toLowerCase();
  const filteredData = props.users.data.filter((user: any) => {
    return (
      user.name.toLowerCase().includes(term) ||
      user.email.toLowerCase().includes(term)
    );
  });
  return { ...props.users, data: filteredData };
});

// Functions to open and close modals for user, role, and tenant management.
const openUserModal = () => {
  selectedUser.value = null;
  showUserModal.value = true;
};

const closeUserModal = () => {
  showUserModal.value = false;
};

const openTenantModal = () => {
  selectedTenant.value = null;
  showTenantModal.value = true;
};

const closeTenantModal = () => {
  showTenantModal.value = false;
};

const openRoleModal = () => {
  selectedRole.value = null;
  showRoleModal.value = true;
};

const closeRoleModal = () => {
  showRoleModal.value = false;
};

// Functions for editing items – set the selected item and open the corresponding modal.
const editUser = (user: any) => {
  selectedUser.value = user;
  showUserModal.value = true;
};

const editTenant = (tenant: any) => {
  selectedTenant.value = tenant;
  showTenantModal.value = true;
};

const editRole = (role: any) => {
  selectedRole.value = role;
  showRoleModal.value = true;
};

// Functions for handling deletion confirmation.
// The deleteUrl is set based on whether the current user is a SuperAdmin.
const deleteUser = (user: any) => {
  deleteUrl.value = isSuperAdmin.value
    ? route('admin.users.destroy', user)
    : route('users.destroy', [tenantSlug, user]);
  deleteMessage.value = `Are you sure you want to delete user ${user.name}?`;
  showConfirmDelete.value = true;
};

const deleteRole = (role: any) => {
  deleteUrl.value = isSuperAdmin.value
    ? route('admin.roles.destroy', role)
    : route('roles.destroy', [tenantSlug, role]);
  deleteMessage.value = `Are you sure you want to delete role ${role.name}?`;
  showConfirmDelete.value = true;
};

const deleteTenant = (tenant: any) => {
  deleteUrl.value = route('admin.tenants.destroy', tenant);
  deleteMessage.value = `Are you sure you want to delete tenant ${tenant.name}?`;
  showConfirmDelete.value = true;
};

// Function to handle deletion confirmation action.
const onDeleteConfirmed = () => {
  showConfirmDelete.value = false;
  // In a real application, you would make an API call here or refresh data.
};

// Function to refresh data after a successful save operation.
const refreshData = () => {
  showRoleModal.value = false;
  showUserModal.value = false;
  showTenantModal.value = false;
};
</script>

<template>
  <!-- Set the page head -->
  <Head title="Users Management" />

  <!-- Main layout with breadcrumbs passed to the AppLayout component -->
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="container mx-auto p-6">
      <!-- Page title -->
      <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">
        User and Role Management
      </h1>

      <!-- Search bar -->
      <div class="mb-6">
        <Input
          v-model="search"
          placeholder="Search users..."
          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Users Section -->
      <section class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Users</h2>
          <Button
            @click="openUserModal"
            variant="default"
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium rounded px-4 py-2 transition"
          >
            Create New User
          </Button>
        </div>
        <UserList :users="filteredUsers" :isSuperAdmin="isSuperAdmin" @edit="editUser" @delete="deleteUser" />
      </section>

      <!-- Roles Section -->
      <section class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Roles</h2>
          <Button
            @click="openRoleModal"
            variant="default"
            class="bg-green-600 hover:bg-green-700 text-white font-medium rounded px-4 py-2 transition"
          >
            Create New Role
          </Button>
        </div>
        <RoleList :roles="roles" @edit="editRole" @delete="deleteRole" />
      </section>

      <!-- Tenants Section (only visible for SuperAdmin) -->
      <section v-if="isSuperAdmin" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Tenants</h2>
          <Button
            @click="openTenantModal"
            variant="default"
            class="bg-purple-700 hover:bg-purple-800 text-white font-medium rounded px-4 py-2 transition"
          >
            Create New Tenant
          </Button>
        </div>
        <TenantList :tenants="tenants" @edit="editTenant" @delete="deleteTenant" />
      </section>

      <!-- Modals for creating/editing users, roles, and tenants -->
      <UserForm
        v-if="showUserModal"
        :user="selectedUser"
        :roles="roles"
        :permissions="permissions"
        :tenants="tenants"
        :isSuperAdmin="isSuperAdmin"
        :tenantSlug="tenantSlug"
        @close="closeUserModal"
        @saved="refreshData"
      />
      <TenantForm
        v-if="showTenantModal"
        :tenants="tenants"
        :tenant="selectedTenant"
        @close="closeTenantModal"
        @saved="refreshData"
      />
      <RoleForm
        v-if="showRoleModal"
        :role="selectedRole"
        :permissions="permissions"
        :isSuperAdmin="isSuperAdmin"
        :tenantSlug="tenantSlug"
        @close="closeRoleModal"
        @saved="refreshData"
      />
      <!-- Confirmation modal for deletion -->
      <ConfirmDeleteModal
        v-if="showConfirmDelete"
        :deleteUrl="deleteUrl"
        :message="deleteMessage"
        :tenantSlug="tenantSlug"
        @cancel="showConfirmDelete = false"
        @confirmed="onDeleteConfirmed"
      />
    </div>
  </AppLayout>
</template>
