<template>
  <div>
    <DialogHeader>
      <DialogTitle class="text-xl sm:text-2xl font-bold flex items-center">
        <UserIcon class="h-5 w-5 mr-2 text-primary" />
        {{ user ? "Edit User" : "Create User" }}
      </DialogTitle>
    </DialogHeader>

    <form @submit.prevent="submit" class="space-y-4 mt-4">
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

        <!-- Password Field -->
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
              :placeholder="
                user ? 'Leave blank to keep current password' : 'Enter password'
              "
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
            <option v-for="tenant in tenants" :value="tenant.id" :key="tenant.id">
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
          <!-- Match header height/alignment with Permissions header -->
          <div class="flex items-center justify-between min-h-[32px]">
            <Label class="flex items-center">
              <Shield class="h-4 w-4 mr-1 text-primary" />
              <span>Roles</span>
              <Badge variant="outline" class="ml-2"
                >{{ form.roles.length }} selected</Badge
              >
            </Label>
            <!-- spacer to align with the right-side buttons in Permissions -->
            <div class="flex space-x-2 opacity-0 pointer-events-none">
              <Button type="button" variant="outline" size="sm" class="text-xs"
                >Select All</Button
              >
              <Button type="button" variant="outline" size="sm" class="text-xs"
                >Clear All</Button
              >
            </div>
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
                :model-value="form.roles.includes(role.id)"
                @update:model-value="(val: boolean) => setRole(role.id, val)"
                class="mr-2"
              />
              <Label :for="`role-${role.id}`" class="cursor-pointer">{{
                role.name
              }}</Label>
            </div>

            <div
              v-if="filteredRoles.length === 0"
              class="flex flex-col items-center justify-center h-full text-muted-foreground"
            >
              <SearchX class="h-8 w-8 mb-2" />
              <p>No roles match your search</p>
            </div>
          </div>

          <InputError :message="form.errors.roles" />
        </div>

        <!-- Permissions Assignment Section -->
        <div class="space-y-2">
          <!-- Make sure this header matches Roles header alignment -->
          <div class="flex items-center justify-between min-h-[32px]">
            <Label class="flex items-center">
              <Lock class="h-4 w-4 mr-1 text-primary" />
              <span>Permissions</span>
              <Badge variant="outline" class="ml-2"
                >{{ selectedPermissionsCount }} selected</Badge
              >
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
            <div
              v-for="(group, groupName) in groupedPermissions"
              :key="groupName"
              class="mb-4"
            >
              <div class="flex items-center justify-between mb-1">
                <div class="font-medium text-sm text-foreground flex items-center">
                  <FolderIcon class="h-4 w-4 mr-1 text-muted-foreground" />
                  {{ formatGroupName(String(groupName)) }}
                  <Badge variant="secondary" class="ml-2">{{ group.length }}</Badge>
                </div>

                <div class="flex space-x-1">
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="selectGroupPermissions(String(groupName))"
                    class="text-xs h-6 px-2"
                  >
                    Select All
                  </Button>
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="clearGroupPermissions(String(groupName))"
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
                  :model-value="form.user_permissions.includes(permission.name)"
                  @update:model-value="(val: boolean) => setPermission(permission.name, val)"
                  :disabled="inheritedPermissions.includes(permission.name)"
                  class="mr-2"
                />
                <Label
                  :for="`permission-${permission.name}`"
                  class="cursor-pointer text-sm"
                >
                  {{ formatPermissionName(permission.name) }}
                  <span
                    v-if="inheritedPermissions.includes(permission.name)"
                    class="ml-2 text-xs px-2 py-0.5 rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 text-gray-600 dark:text-gray-300"
                  >
                    inherited
                  </span>
                </Label>
              </div>
            </div>

            <div
              v-if="Object.keys(groupedPermissions).length === 0"
              class="flex flex-col items-center justify-center h-full text-muted-foreground"
            >
              <SearchX class="h-8 w-8 mb-2" />
              <p>No permissions match your search</p>
            </div>
          </div>

          <InputError :message="form.errors.user_permissions" />
        </div>
      </div>

      <DialogFooter class="mt-6">
        <Button type="button" @click="() => emit('close')" variant="outline">
          Cancel
        </Button>
        <Button type="submit" :disabled="form.processing">
          <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
          {{ form.processing ? "Saving..." : "Save" }}
        </Button>
      </DialogFooter>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

// UI components
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { Badge } from "@/components/ui/badge";
import { DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import InputError from "@/components/InputError.vue";

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
  SearchX,
} from "lucide-vue-next";

type Role = { id: number; name: string; permissions?: { name: string }[] };
type Permission = { id: number; name: string };
type Tenant = { id: number; name: string };

const props = defineProps({
  user: { type: Object as any, default: null },
  tenants: { type: Array as () => Tenant[], default: () => [] },
  roles: { type: Array as () => Role[], default: () => [] },
  permissions: { type: Array as () => Permission[], default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  tenantSlug: { type: String, default: null },
});

const emit = defineEmits(["close", "saved"]);

const roleSearch = ref("");
const permissionSearch = ref("");
const showPassword = ref(false);

// Inertia form
const form = useForm({
  name: "",
  email: "",
  password: "",
  tenant_id: null as number | null,
  roles: [] as number[],
  user_permissions: [] as string[],
});

// Single source of truth for explicit permissions selections
const selectedPerms = ref<Set<string>>(new Set());

const loadFromUser = (u: any | null) => {
  if (u) {
    form.name = u.name ?? "";
    form.email = u.email ?? "";
    form.password = ""; // DO NOT preload password
    form.tenant_id = u.tenant_id ?? null;
    form.roles = u.roles ? u.roles.map((r: any) => r.id) : [];
    const initialPerms = u.permissions ? u.permissions.map((p: any) => p.name) : [];
    form.user_permissions = initialPerms;
    selectedPerms.value = new Set(initialPerms);
  } else {
    form.name = "";
    form.email = "";
    form.password = "";
    form.tenant_id = props.tenants.length ? props.tenants[0].id : null;
    form.roles = [];
    form.user_permissions = [];
    selectedPerms.value = new Set();
  }
};

watch(
  () => props.user,
  (u) => loadFromUser(u),
  { immediate: true }
);

// Compute filtered roles based on search term
const filteredRoles = computed(() => {
  if (!roleSearch.value.trim()) return props.roles;
  const term = roleSearch.value.toLowerCase();
  return props.roles.filter((role: any) => role.name.toLowerCase().includes(term));
});

const selectedPermissionsCount = computed(() => form.user_permissions.length);

// Inherited permissions from selected roles (not editable directly)
const inheritedPermissions = computed<string[]>(() => {
  const set = new Set<string>();
  props.roles.forEach((role: any) => {
    if (form.roles.includes(role.id) && role.permissions) {
      role.permissions.forEach((p: any) => set.add(p.name));
    }
  });
  return Array.from(set);
});

/** Roles: set instead of toggle */
const setRole = (roleId: number, checked: boolean) => {
  const idx = form.roles.indexOf(roleId);
  if (checked && idx === -1) form.roles.push(roleId);
  if (!checked && idx !== -1) form.roles.splice(idx, 1);
};

/** Permissions: set instead of toggle; keep Set + form in sync */
const setPermission = (name: string, checked: boolean) => {
  if (inheritedPermissions.value.includes(name)) return;

  if (checked) selectedPerms.value.add(name);
  else selectedPerms.value.delete(name);

  form.user_permissions = Array.from(selectedPerms.value);
};

const selectAllPermissions = () => {
  props.permissions.forEach((p: any) => {
    if (!inheritedPermissions.value.includes(p.name)) selectedPerms.value.add(p.name);
  });
  form.user_permissions = Array.from(selectedPerms.value);
};

const clearAllPermissions = () => {
  selectedPerms.value.clear();
  form.user_permissions = [];
};

/**
 * FIX: "General" group buttons weren't working because your formatGroupName maps
 * receive_updates -> "General" for display, but the real group key might be
 * "receive_updates" OR literally "general" (depending on your permission naming).
 *
 * We normalize the clicked groupName here so both cases work.
 */
const normalizeGroupKey = (groupName: string) => {
  const key = groupName.trim().toLowerCase();
  if (key === "general") return "receive_updates";
  return groupName;
};

const selectGroupPermissions = (groupName: string) => {
  const key = normalizeGroupKey(groupName);

  props.permissions
    .filter((p: any) => p.name === key || p.name.startsWith(key + "."))
    .forEach((p: any) => {
      if (!inheritedPermissions.value.includes(p.name)) selectedPerms.value.add(p.name);
    });

  form.user_permissions = Array.from(selectedPerms.value);
};

const clearGroupPermissions = (groupName: string) => {
  const key = normalizeGroupKey(groupName);

  Array.from(selectedPerms.value).forEach((name) => {
    if (name === key || name.startsWith(key + ".")) selectedPerms.value.delete(name);
  });

  form.user_permissions = Array.from(selectedPerms.value);
};

const groupedPermissions = computed<Record<string, Permission[]>>(() => {
  const filtered = permissionSearch.value.trim()
    ? props.permissions.filter((p: any) =>
        p.name.toLowerCase().includes(permissionSearch.value.toLowerCase())
      )
    : props.permissions;

  const groups: Record<string, Permission[]> = {};
  filtered.forEach((permission: any) => {
    // IMPORTANT: "general" permissions like "receive_updates" have no dot.
    // Your previous grouping made them their own groupName = "receive_updates".
    // That's OK, but group selection must handle "exact match" as well as startsWith.
    const parts = permission.name.split(".");
    const groupName = parts[0];
    if (!groups[groupName]) groups[groupName] = [];
    groups[groupName].push(permission);
  });
  return groups;
});

const formatGroupName = (name: string) => {
  if (name === "tenant-settings") return "Tenant Settings";
  if (name === "repair-orders") return "Repair Orders";
  if (name === "safety-data") return "Safety Data";
  if (name === "miles-driven") return "Miles Driven";
  if (name === "support-tickets") return "Support Tickets";
  if (name === "sms-coaching") return "SMS Coaching";
  if (name === "support-responses") return "Support Responses";
  if (name === "receive_updates") return "General";
  if (name === "general") return "General";
  return name.charAt(0).toUpperCase() + name.slice(1);
};

const formatPermissionName = (name: string) => {
  const parts = name.split(".");
  if (parts.length > 1) {
    return parts[1].charAt(0).toUpperCase() + parts[1].slice(1).replace(/-/g, " ");
  }
  // For "receive_updates" (no dot), show a nicer label
  return name.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
};

const submit = () => {
  // ensure form.user_permissions matches our set
  form.user_permissions = Array.from(selectedPerms.value);

  const doSubmit = () => {
    if (props.user) {
      if (props.isSuperAdmin) {
        form.put(route("admin.users.update", props.user), {
          onSuccess: () => emit("saved"),
        });
      } else {
        form.put(route("users.update", [props.tenantSlug, props.user]), {
          onSuccess: () => emit("saved"),
        });
      }
    } else {
      if (props.isSuperAdmin) {
        form.post(route("admin.users.store"), { onSuccess: () => emit("saved") });
      } else {
        form.post(route("users.store", props.tenantSlug), {
          onSuccess: () => emit("saved"),
        });
      }
    }
  };

  // If editing and password blank, remove it from payload
  if (props.user && !form.password) {
    form.transform((data: any) => {
      const { password, ...rest } = data;
      return rest;
    });
  } else {
    form.transform((data: any) => data);
  }

  doSubmit();
};
</script>
