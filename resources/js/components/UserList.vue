<template>
  <div>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
          <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        <tr
          v-for="user in users.data"
          :key="user.id"
          class="hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ user.name }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ user.email }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
            <Button
              @click="$emit('edit', user)"
              class="mr-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
            >
              Edit
            </Button>
            <Button
              variant="destructive"
              @click="$emit('delete', user)"
              class="mr-2 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
            >
              Delete
            </Button>
            <!-- Show Impersonate button only for SuperAdmin users -->
            <Link
              v-if="isSuperAdmin"
              :href="route('impersonate.start', user.id)"
              as="button"
            >
              <Button variant="secondary" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded">
                Impersonate
              </Button>
            </Link>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- Pagination -->
    <div class="mt-4 flex justify-center" v-if="users.links">
      <button
        v-for="link in users.links"
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
import Button from '@/components/ui/button/Button.vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'

const props = defineProps({
  users: Object,
  isSuperAdmin: { type: Boolean, default: false },
});

// Function to handle pagination navigation using Inertia.
const visitPage = (url) => {
  if (url) {
    router.get(url, {}, {  replace: true });
  }
};
</script>
