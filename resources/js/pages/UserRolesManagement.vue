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
import { Search } from 'lucide-vue-next';

// Import custom components for lists and forms
import UserList from '@/components/UserList.vue';
import RoleList from '@/components/RoleList.vue';
import UserForm from '@/components/UserForm.vue';
import TenantList from '@/components/TenantList.vue';
import TenantForm from '@/components/TenantForm.vue';
import RoleForm from '@/components/RoleForm.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';

// Add these imports for enhanced UI components
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Plus, Users, Shield, Building } from 'lucide-vue-next';

// Define props received from the backend via Inertia
const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  roles:{
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenants: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  permissions: Array,
  search: String,
  tenantSlug: {
    type: String,
    default: null,
  },
  SuperAdmin: Boolean,
});
const { tenantSlug } = props;

// Set up breadcrumbs (if tenantSlug exists, use it to create a tenant-specific dashboard link)
const breadcrumbs = [
  {
    title: tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: tenantSlug ? `/${tenantSlug}/dashboard` : '/dashboard',
  },
  {
    title: 'User Management',
    href: tenantSlug? `/${tenantSlug}/admin/user-management` : '/admin/user-management',
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
const isSuperAdmin = props.SuperAdmin;

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
  deleteUrl.value = isSuperAdmin
    ? route('admin.users.destroy', user)
    : route('users.destroy', [tenantSlug, user]);
  deleteMessage.value = `Are you sure you want to delete user ${user.name}?`;
  showConfirmDelete.value = true;
};

const deleteRole = (role: any) => {
  deleteUrl.value = isSuperAdmin
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

// Computed properties to extract arrays
const rolesArray = computed(() => props.roles.data);
const tenantsArray = computed(() => props.tenants.data);

</script>

<template >
  <!-- Set the page head -->
  <Head title="Users Management" />

  <!-- Main layout with breadcrumbs passed to the AppLayout component -->
  <AppLayout  :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="props.permissions">
    
    <div class="container w-full  md:max-w-2xl lg:max-w-3xl xl:max-w-6xl  lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
      <!-- Page header with title and description -->
      <div class="space-y-2">
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-900 dark:text-gray-100">
          User and Role Management
        </h1>
        <p class="text-gray-500 dark:text-gray-400">
          Manage users, roles, and permissions for your application.
        </p>
      </div>

      <Separator />

      <!-- Search bar with icon -->
      <div class="relative max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="h-5 w-5 text-gray-400" />
        </div>
        <Input
          v-model="search"
          placeholder="Search users..."
          class="w-full pl-10 rounded-md"
        />
      </div>

      <!-- Users Section -->
      <Card >
        <CardHeader class="p-2 md:p-4 lg:p-6 flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xl font-semibold">
            <div class="flex items-center space-x-2">
              <Users class="h-5 w-5" />
              <span>Users</span>
            </div>
          </CardTitle>
          <Button
            @click="openUserModal"
            variant="default"
            size="sm"
            class="gap-1"
          >
            <Plus class="h-4 w-4" />
            <span>Add User</span>
          </Button>
        </CardHeader>
        <CardContent class="p-2 md:p-4 lg:p-6">
          <UserList :users="filteredUsers" :isSuperAdmin="isSuperAdmin" @edit="editUser" @delete="deleteUser" />
        </CardContent>
      </Card>

      <!-- Roles Section -->
      <Card>
        <CardHeader class="p-2 md:p-4 lg:p-6 flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xl font-semibold">
            <div class="flex items-center space-x-2">
              <Shield class="h-5 w-5" />
              <span>Roles</span>
            </div>
          </CardTitle>
          <Button
            @click="openRoleModal"
            variant="default"
            size="sm"
            class="gap-1"
          >
            <Plus class="h-4 w-4" />
            <span>Add Role</span>
          </Button>
        </CardHeader>
        <CardContent class="p-2 md:p-4 lg:p-6">
          <RoleList :roles="roles" @edit="editRole" @delete="deleteRole" />
        </CardContent>
      </Card>

      <!-- Tenants Section (only visible for SuperAdmin) -->
      <Card v-if="isSuperAdmin">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-xl font-semibold">
            <div class="flex items-center space-x-2">
              <Building class="h-5 w-5" />
              <span>Companies</span>
            </div>
          </CardTitle>
          <Button
            @click="openTenantModal"
            variant="default"
            size="sm"
            class="gap-1"
          >
            <Plus class="h-4 w-4" />
            <span>Add Company</span>
          </Button>
        </CardHeader>
        <CardContent>
          <TenantList :tenants="tenants" @edit="editTenant" @delete="deleteTenant" />
        </CardContent>
      </Card>

      <!-- Modals for creating/editing users, roles, and tenants -->
      <!-- User Form Modal -->
      <Transition name="fade">
        <UserForm
          v-if="showUserModal"
          :user="selectedUser"
          :roles="rolesArray"
          :permissions="permissions"
          :tenants="tenantsArray"
          :isSuperAdmin="isSuperAdmin"
          :tenantSlug="tenantSlug"
          @close="closeUserModal"
          @saved="refreshData"
        />
      </Transition>
      
      <!-- Tenant Form Modal -->
      <Transition name="fade" v-if="showTenantModal">
        <div  class="fixed inset-0 z-[100]">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeTenantModal"></div>
          <div class="absolute inset-0 flex items-center justify-center p-2 sm:p-4">
            <TenantForm
              :tenant="selectedTenant"
              @close="closeTenantModal"
              @saved="refreshData"
              class="bg-background p-4 sm:p-6 rounded-lg shadow-xl w-full sm:max-w-lg md:max-w-xl overflow-y-auto max-h-[95vh] animate-in fade-in zoom-in-95 duration-200 border border-border"
            />
          </div>
        </div>
      </Transition>
      
      <!-- Role Form Modal -->
      <Transition name="fade">
        <div v-if="showRoleModal" class="fixed inset-0 z-[100]">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeRoleModal"></div>
          <div class="absolute inset-0 flex items-center justify-center p-2 sm:p-4">
            <RoleForm
              :role="selectedRole"
              :permissions="permissions"
              :isSuperAdmin="isSuperAdmin"
              :tenantSlug="tenantSlug"
              @close="closeRoleModal"
              @saved="refreshData"
              class="bg-background p-4 sm:p-6 rounded-lg shadow-xl w-full sm:max-w-lg md:max-w-xl overflow-y-auto max-h-[95vh] animate-in fade-in zoom-in-95 duration-200 border border-border"
            />
          </div>
        </div>
      </Transition>
      
      <!-- Confirmation modal for deletion -->
      <Transition name="fade">
        <div v-if="showConfirmDelete" class="fixed inset-0 z-[100]">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showConfirmDelete = false"></div>
          <div class="absolute inset-0 flex items-center justify-center p-2 sm:p-4">
            <ConfirmDeleteModal
              :deleteUrl="deleteUrl"
              :message="deleteMessage"
              :tenantSlug="tenantSlug"
              @cancel="showConfirmDelete = false"
              @confirmed="onDeleteConfirmed"
              class="bg-background p-4 sm:p-6 rounded-lg shadow-xl w-full sm:max-w-md overflow-y-auto max-h-[95vh] animate-in fade-in zoom-in-95 duration-200 border border-border"
            />
          </div>
        </div>
      </Transition>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.98);
}
</style>
