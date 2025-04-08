<template>
  <div>
    <div class="rounded-md border">
      <table class="w-full caption-bottom text-sm">
        <thead class="[&_tr]:border-b">
          <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role Name</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Permissions</th>
            <th class="h-12 px-4 text-center align-middle font-medium text-muted-foreground">Actions</th>
          </tr>
        </thead>
        <tbody class="[&_tr:last-child]:border-0">
          <tr
            v-for="role in roles.data"
            :key="role.id"
            class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
          >
            <td class="p-4 align-middle font-medium">
              {{ role.name }}
            </td>
            <td class="p-4 align-middle">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="permission in role.permissions"
                  :key="permission.id"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground"
                >
                  {{ permission.name }}
                </span>
              </div>
            </td>
            <td class="p-4 align-middle text-center">
              <div class="flex justify-center space-x-2">
                <Button
                  @click="$emit('edit', role)"
                  variant="outline"
                  size="sm"
                >
                  Edit
                </Button>
                <Button
                  @click="$emit('delete', role)"
                  variant="destructive"
                  size="sm"
                >
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
      <nav class="flex items-center space-x-1">
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
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  roles: Object,
});

const visitPage = (url) => {
  if (url) {
    router.get(url, {}, { only: ['roles'] });
  }
};
</script>
