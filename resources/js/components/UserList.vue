<template>
  <div>
    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="py-2 px-4">Name</th>
          <th class="py-2 px-4">Email</th>
          <th class="py-2 px-4">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users.data" :key="user.id">
          <td class="border px-4 py-2">{{ user.name }}</td>
          <td class="border px-4 py-2">{{ user.email }}</td>
          <td class="border px-4 py-2">
            <Button @click="$emit('edit', user)">Edit</Button>
            <Button variant="destructive" @click="$emit('delete', user)">Delete</Button>
            <Link v-if="isSuperAdmin" :href="route('impersonate.start', user.id)" as="button">
              <Button variant="Secondary">Impersonate</Button>
            </Link>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- Pagination -->
    <div class="mt-4" v-if="users.links">
      <button
        v-for="link in users.links"
        :key="link.label"
        @click="visitPage(link.url)"
        :disabled="!link.url"
        class="mx-1 px-2 py-1 border rounded"
      >
        <span v-html="link.label"></span>
      </button>
    </div>
  </div>
</template>

<script setup>
import Button from '@/components/ui/button/Button.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  users: Object,
  isSuperAdmin: { type: Boolean, default: false },
});

const visitPage = (url) => {
  if (url) {
    Inertia.get(url, {}, { preserveState: true, replace: true });
  }
};
</script>
