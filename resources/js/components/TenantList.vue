<template>
  <div>
    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="py-2 px-4">Name</th>
          <th class="py-2 px-4">Slug</th>
          <th class="py-2 px-4">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="tenant in normalizedTenants" :key="tenant.id">
          <td class="border px-4 py-2">{{ tenant.name }}</td>
          <td class="border px-4 py-2">{{ tenant.slug }}</td>
          <td class="border px-4 py-2">
            <Button @click="$emit('edit', tenant)">Edit</Button>
            <Button variant="destructive" @click="$emit('delete', tenant)">Delete</Button>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- Pagination (only if pagination links exist) -->
    <div class="mt-4" v-if="tenants.links">
      <button
        v-for="link in tenants.links"
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

const props = defineProps({
  tenants: {
    type: [Array, Object],
    default: () => ([]),
  },
});

// Normalize the tenants: if tenants is an array, use it directly; if it's an object, use its data property.
const normalizedTenants = Array.isArray(props.tenants)
  ? props.tenants
  : props.tenants.data || [];

const visitPage = (url) => {
  if (url) {
    Inertia.get(url, {}, { preserveState: true, replace: true });
  }
};
</script>
