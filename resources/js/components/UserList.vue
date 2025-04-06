<template>
  <div>
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
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
            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
          >
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ user.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
              {{ user.email }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
              <div class="flex justify-center space-x-2">
                <Button
                  @click="$emit('edit', user)"
                  variant="outline"
                  class="border-blue-500 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 px-3 py-1 rounded-md transition-colors"
                >
                  Edit
                </Button>
                <Button
                  variant="destructive"
                  @click="$emit('delete', user)"
                  class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md transition-colors"
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
                    class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-3 py-1 rounded-md transition-colors"
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
      <nav class="flex items-center space-x-1">
        <button
          v-for="link in users.links"
          :key="link.label"
          @click="visitPage(link.url)"
          :disabled="!link.url"
          :class="[
            'px-3 py-1 rounded-md text-sm font-medium transition-colors',
            link.active 
              ? 'bg-primary text-white' 
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
            !link.url && 'opacity-50 cursor-not-allowed'
          ]"
          v-html="link.label"
        ></button>
      </nav>
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
    router.get(url, {}, {  only: ['users'] });
  }
};
</script>
