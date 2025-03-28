<template>
  <div>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
          <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        <tr
          v-for="tenant in normalizedTenants"
          :key="tenant.id"
          class="hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ tenant.name }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ tenant.slug }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
            <Button
              @click="$emit('edit', tenant)"
              class="mr-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
            >
              Edit
            </Button>
            <Button
              variant="destructive"
              @click="$emit('delete', tenant)"
              class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
            >
              Delete
            </Button>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- Pagination Section -->
    <div class="mt-4 flex justify-center" v-if="tenants.links">
      <button
        v-for="link in tenants.links"
        :key="link.label"
        @click="visitPage(link.url)"
        :disabled="!link.url"
        class="mx-1 px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50"
      >
        <span v-html="link.label"></span>
      </button>
    </div>
  </div>
</template>

<script setup>
// Import Button from the correct folder and Inertia for navigation
import Button from '@/components/ui/button/Button.vue';

const props = defineProps({
  tenants: {
    type: [Array, Object],
    default: () => ([]),
  },
});

// Normalize tenants to an array (for paginated or non-paginated data)
const normalizedTenants = Array.isArray(props.tenants)
  ? props.tenants
  : props.tenants.data || [];

// Function to navigate to a page while preserving state
const visitPage = (url) => {
  if (url) {
    Inertia.get(url, {}, { preserveState: true, replace: true });
  }
};
</script>
