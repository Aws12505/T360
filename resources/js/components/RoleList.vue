<template>
  <div class="w-full overflow-x-auto">
    <div class="rounded-md border">
      <table class="w-full caption-bottom text-sm">
        <thead class="[&_tr]:border-b bg-muted/50">
          <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role Name</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Permissions</th>
            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground" v-if="permissionsNames.includes('roles.update') || permissionsNames.includes('roles.delete')">Actions</th>
          </tr>
        </thead>
        <tbody class="[&_tr:last-child]:border-0">
          <tr
            v-for="role in roles.data"
            :key="role.id"
            class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
          >
            <td class="p-4 align-middle font-medium">
              <div class="flex items-center">
                <Shield class="h-4 w-4 mr-2 text-primary" />
                {{ role.name }}
              </div>
            </td>
            <td class="p-4 align-middle">
              <div class="flex flex-wrap gap-1">
                <!-- Show permission count with badge -->
                <div class="mb-2 w-full">
                  <Badge variant="outline" class="mr-2">
                    {{ getTotalPermissionCount(role.permissions) }} permissions
                  </Badge>
                  <Button 
                    variant="ghost" 
                    size="sm" 
                    @click="toggleExpandRole(role.id)"
                    class="text-xs"
                  >
                    {{ expandedRoles.includes(role.id) ? 'Hide details' : 'Show details' }}
                  </Button>
                </div>
                
                <!-- Expandable permission details -->
                <div v-if="expandedRoles.includes(role.id)" class="w-full">
                  <span
                    v-for="(group, groupName) in groupedPermissions(role.permissions)"
                    :key="groupName"
                    class="inline-flex flex-col mb-2 w-full"
                  >
                    <span class="text-xs font-semibold text-muted-foreground mb-1 flex items-center">
                      <FolderIcon class="h-3 w-3 mr-1" />
                      {{ formatGroupName(groupName) }}
                      <Badge variant="secondary" class="ml-2">{{ group.length }}</Badge>
                    </span>
                    <div class="flex flex-wrap gap-1 ml-4">
                      <span
                        v-for="permission in group"
                        :key="permission.id"
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground"
                      >
                        {{ formatPermissionName(permission.name) }}
                      </span>
                    </div>
                  </span>
                </div>
              </div>
            </td>
            <td class="p-4 align-middle text-right" v-if="permissionsNames.includes('roles.update')||permissionsNames.includes('roles.delete')">
              <div class="flex justify-end space-x-2">
                <Button
                v-if="permissionsNames.includes('roles.update')"
                  @click="$emit('edit', role)"
                  variant="outline"
                  size="sm"
                  class="flex items-center"
                >
                  <PencilIcon class="h-3.5 w-3.5 mr-1" />
                  Edit
                </Button>
                <Button
                v-if="permissionsNames.includes('roles.delete')"
                  @click="$emit('delete', role)"
                  variant="destructive"
                  size="sm"
                  class="flex items-center"
                >
                  <TrashIcon class="h-3.5 w-3.5 mr-1" />
                  Delete
                </Button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div class="mt-6 flex justify-center" v-if="roles.links">
      <nav class="flex flex-wrap items-center gap-1 justify-center">
        <Button
          v-for="link in roles.links"
          :key="link.label"
          @click="visitPage(link.url)"
          :disabled="!link.url"
          :variant="link.active ? 'default' : 'outline'"
          size="sm"
          class="min-w-[40px]"
          v-html="link.label"
        ></Button>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { Shield, Pencil as PencilIcon, Trash as TrashIcon, Folder as FolderIcon } from 'lucide-vue-next';
import { computed, defineProps } from 'vue';

const props = defineProps({
  roles: Object,
  permissions: Array,
});
const permissionsNames = computed(() => {
  return props.permissions.map(permission => permission.name);
});
// Track expanded roles
const expandedRoles = ref([]);

const toggleExpandRole = (roleId) => {
  const index = expandedRoles.value.indexOf(roleId);
  if (index === -1) {
    expandedRoles.value.push(roleId);
  } else {
    expandedRoles.value.splice(index, 1);
  }
};

const getTotalPermissionCount = (permissions) => {
  return permissions.length;
};

const visitPage = (url) => {
  if (url) {
    router.get(url, {}, { only: ['roles'] });
  }
};

// Group permissions by their category
const groupedPermissions = (permissions) => {
  const groups = {};
  
  permissions.forEach(permission => {
    const parts = permission.name.split('.');
    const groupName = parts[0];
    
    if (!groups[groupName]) {
      groups[groupName] = [];
    }
    
    groups[groupName].push(permission);
  });
  
  return groups;
};

// Format group name for display
const formatGroupName = (name) => {
  if (name === 'tenant-settings') return 'Tenant Settings';
  if (name === 'repair-orders') return 'Repair Orders';
  if (name === 'safety-data') return 'Safety Data';
  if (name === 'miles-driven') return 'Miles Driven';
  if (name === 'support-tickets') return 'Support Tickets';
  if (name === 'sms-coaching') return 'SMS Coaching';
  if (name === 'support-responses') return 'Support Responses';
  
  return name.charAt(0).toUpperCase() + name.slice(1);
};

// Format permission name for display
const formatPermissionName = (name) => {
  const parts = name.split('.');
  if (parts.length > 1) {
    return parts[1].charAt(0).toUpperCase() + parts[1].slice(1).replace(/-/g, ' ');
  }
  return name;
};
</script>
