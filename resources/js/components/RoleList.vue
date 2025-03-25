<template>
  <div>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow">
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
          v-for="role in roles"
          :key="role.id"
          class="hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ role.name }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm">
            <span
              v-for="permission in role.permissions"
              :key="permission.id"
              class="mr-1 inline-block bg-gray-200 dark:bg-gray-700 rounded-full px-2 py-1 text-xs text-gray-700 dark:text-gray-300"
            >
              {{ permission.name }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
            <Button
              @click="$emit('edit', role)"
              class="mr-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
            >
              Edit
            </Button>
            <Button
              variant="destructive"
              @click="$emit('delete', role)"
              class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
            >
              Delete
            </Button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import Button from '@/components/ui/button/Button.vue';

const props = defineProps({
  roles: Array,
});
</script>
