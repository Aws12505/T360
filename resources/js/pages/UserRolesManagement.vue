<template>
  <Head title="Users Management" />

  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="container mx-auto p-4">
      <h1 class="text-2xl font-bold mb-4">User and Role Management</h1>

      <!-- Dynamic search bar for users -->
      <div class="mb-4">
        <Input
          v-model="search"
          placeholder="Search users..."
        />
      </div>

      <!-- Users Section -->
      <section>
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-xl font-semibold">Users</h2>
          <Button @click="openUserModal">Create New User</Button>
        </div>
        <!-- Pass filteredUsers instead of the original users prop -->
        <UserList :users="filteredUsers" :isSuperAdmin="isSuperAdmin" @edit="editUser" @delete="deleteUser" />
      </section>

      <!-- Roles Section -->
      <section class="mt-8">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-xl font-semibold">Roles</h2>
          <Button @click="openRoleModal">Create New Role</Button>
        </div>
        <RoleList :roles="roles" @edit="editRole" @delete="deleteRole" />
      </section>

       <!-- Tenants Section -->
        <div v-if="isSuperAdmin" >
       <section class="mt-8">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-xl font-semibold">Tenants</h2>
          <Button @click="openTenantModal">Create New Tenant</Button>
        </div>
        <TenantList :tenants="tenants" @edit="editTenant" @delete="deleteTenant" />
      </section>
    </div>
      <!-- Modals for creating/editing users and roles -->
      <UserForm
        v-if="showUserModal"
        :user="selectedUser"
        :roles="roles"
        :permissions="permissions"
        :tenants="tenants"
        :isSuperAdmin="isSuperAdmin"
        :tenantSlug ="tenantSlug"
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
        :tenantSlug ="tenantSlug"
        @close="closeRoleModal"
        @saved="refreshData"
      />

      <!-- Confirmation modal for deletion -->
      <ConfirmDeleteModal
        v-if="showConfirmDelete"
        :deleteUrl="deleteUrl"
        :message="deleteMessage"
        :tenantSlug ="tenantSlug"
        @cancel="showConfirmDelete = false"
        @confirmed="onDeleteConfirmed"
      />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// Import shadcn-vue components (assumed to be available)
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';

// Import our custom components for listing and forms
import UserList from '@/components/UserList.vue';
import RoleList from '@/components/RoleList.vue';
import UserForm from '@/components/UserForm.vue';
import TenantList from '@/components/TenantList.vue';
import TenantForm from '@/components/TenantForm.vue';
import RoleForm from '@/components/RoleForm.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';

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
// Destructure tenantSlug for easier usage
const { tenantSlug } = props;

// Set up breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? `/${tenantSlug}/dashboard` : '/admin/dashboard',
  },
];

// Reactive properties
const search = ref(props.search || '');
const showUserModal = ref(false);
const showRoleModal = ref(false);
const showTenantModal = ref(false);
const selectedUser = ref(null);
const selectedRole = ref(null);
const selectedTenant = ref(null);

// Determine if current user is SuperAdmin (tenant_id === null)
const isSuperAdmin = ref(props.tenants.length > 0);

// Reactive properties for delete confirmation
const showConfirmDelete = ref(false);
const deleteUrl = ref('');
const deleteMessage = ref('');

// Client-side filtering: return the original paginated object with a filtered data array
const filteredUsers = computed(() => {
  if (!search.value.trim()) {
    return props.users;
  }
  const term = search.value.toLowerCase();
  // Filter the data array from the paginated object
  const filteredData = props.users.data.filter((user: any) => {
    return (
      user.name.toLowerCase().includes(term) ||
      user.email.toLowerCase().includes(term)
    );
  });
  // Return a new object preserving the original pagination links, but with filtered data.
  return { ...props.users, data: filteredData };
});

// Functions to open/close modals and set selected item
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

// Updated delete functions that open a confirmation modal
const deleteUser = (user: any) => {
  deleteUrl.value = isSuperAdmin.value
    ? route('admin.users.destroy', user)
    : route('users.destroy', [tenantSlug,user]);
  deleteMessage.value = `Are you sure you want to delete user ${user.name}?`;
  showConfirmDelete.value = true;
};

const deleteRole = (role: any) => {
  deleteUrl.value = isSuperAdmin.value
    ? route('admin.roles.destroy', role)
    : route('roles.destroy', [tenantSlug,role]);
  deleteMessage.value = `Are you sure you want to delete role ${role.name}?`;
  showConfirmDelete.value = true;
};
const deleteTenant = (tenant: any) => {
  deleteUrl.value = route('admin.tenants.destroy', tenant);
  deleteMessage.value = `Are you sure you want to delete tenant ${tenant.name}?`;
  showConfirmDelete.value = true;
};
const onDeleteConfirmed = () => {
  showConfirmDelete.value = false;
  // In a real app, call an API or refresh data here.
};

const refreshData = () => {
  // Close modals immediately; refresh data as needed.
  
  showRoleModal.value = false;
  showUserModal.value = false;
  showTenantModal.value = false;
};
</script>
