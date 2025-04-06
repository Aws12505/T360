<template>
  <div>
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Role Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Permissions
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="role in roles.data"
            :key="role.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
          >
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ role.name }}
            </td>
            <td class="px-6 py-4 text-sm">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="permission in role.permissions"
                  :key="permission.id"
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                >
                  {{ permission.name }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
              <div class="flex justify-center space-x-2">
                <Button
                  @click="$emit('edit', role)"
                  variant="outline"
                  class="border-blue-500 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 px-3 py-1 rounded-md transition-colors"
                >
                  Edit
                </Button>
                <Button
                  variant="destructive"
                  @click="$emit('delete', role)"
                  class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md transition-colors"
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
        <button
          v-for="link in roles.links"
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
// Import ShadCN UI Button component
import Button from '@/components/ui/button/Button.vue';
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
