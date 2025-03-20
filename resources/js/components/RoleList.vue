<template>
    <div>
      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th class="py-2 px-4">Role Name</th>
            <th class="py-2 px-4">Permissions</th>
            <th class="py-2 px-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="role in roles" :key="role.id">
            <td class="border px-4 py-2">{{ role.name }}</td>
            <td class="border px-4 py-2">
              <!-- Assuming role.permissions is an array of permission objects -->
              <span v-for="permission in role.permissions" :key="permission.id" class="mr-1">
                {{ permission.name }}
              </span>
            </td>
            <td class="border px-4 py-2">
              <Button @click="$emit('edit', role)">Edit</Button>
              <Button variant="destructive" @click="$emit('delete', role)">Delete</Button>
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
  