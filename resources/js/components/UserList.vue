<template>
  <div class="w-full overflow-x-auto">
    <div class="rounded-md border">
      <table class="w-full caption-bottom text-sm">
        <thead class="[&_tr]:border-b">
          <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Email</th>
            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground" v-if="permissionNames.includes('users.update')||permissionNames.includes('users.delete')">Actions</th>
          </tr>
        </thead>
        <tbody class="[&_tr:last-child]:border-0">
          <tr
            v-for="user in users.data"
            :key="user.id"
            class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
          >
            <td class="p-4 align-middle font-medium">
              {{ user.name }}
            </td>
            <td class="p-4 align-middle">
              {{ user.email }}
            </td>
            <td class="p-4 align-middle text-right" v-if="permissionNames.includes('users.update')||permissionNames.includes('users.delete')">
              <div class="flex justify-end space-x-2">
                <Button
                  v-if="permissionNames.includes('users.update')"
                  @click="$emit('edit', user)"
                  variant="outline"
                  size="sm"
                >
                  Edit
                </Button>
                <Button
                  v-if="permissionNames.includes('users.delete')"
                  @click="$emit('delete', user)"
                  variant="destructive"
                  size="sm"
                >
                  Delete
                </Button>
                <!-- Show Impersonate button only for SuperAdmin users -->
                <Link
                  v-if="isSuperAdmin"
                  :href="route('impersonate.start', user.id)"
                  as="button"
                >
                  <Button 
                    variant="secondary" 
                    size="sm"
                  >
                    Impersonate
                  </Button>
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div class="mt-6 flex justify-center" v-if="users.links">
      <nav class="flex flex-wrap items-center gap-1 justify-center">
        <Button
          v-for="link in users.links"
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
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { computed, defineProps } from 'vue';

const props = defineProps({
  users: Object,
  isSuperAdmin: { type: Boolean, default: false },
  permissions: Array,
});
const permissionNames = computed(() => {
  return props.permissions.map(permission => permission.name);
});
// Function to handle pagination navigation using Inertia.
const visitPage = (url) => {
  if (url) {
    router.get(url, {}, { only: ['users'] });
  }
};
</script>
