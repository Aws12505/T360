<template>
  <div class="w-full overflow-x-auto">
    <div class="rounded-md border">
      <table class="w-full caption-bottom text-sm">
        <thead class="[&_tr]:border-b">
          <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Company Name</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Slug</th>
            <th class="h-12 px-4 text-right pr-16 align-middle font-medium text-muted-foreground">Actions</th>
          </tr>
        </thead>
        <tbody class="[&_tr:last-child]:border-0">
          <tr
            v-for="tenant in normalizedTenants"
            :key="tenant.id"
            class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
          >
            <td class="p-4 align-middle font-medium">
              {{ tenant.name }}
            </td>
            <td class="p-4 align-middle">
              {{ tenant.slug }}
            </td>
            <td class="p-4 align-middle text-right">
              <div class="flex justify-end space-x-2">
                <Button
                  @click="$emit('edit', tenant)"
                  variant="outline"
                  size="sm"
                >
                  Edit
                </Button>
                <Button
                  @click="$emit('delete', tenant)"
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
    <div class="mt-6 flex justify-center" v-if="tenants.links">
      <nav class="flex flex-wrap items-center gap-1 justify-center">
        <Button
          v-for="link in tenants.links"
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
  tenants: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
});

// Normalize tenants to an array (for paginated or non-paginated data)
const normalizedTenants = Array.isArray(props.tenants)
  ? props.tenants
  : props.tenants.data || [];

// Function to navigate to a page while preserving state
const visitPage = (url) => {
  if (url) {
    router.get(url, {}, { only: ['tenants']});
  }
};
</script>
