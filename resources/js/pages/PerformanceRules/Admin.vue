<script setup>
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import EntryForm from './Form.vue'

const props = defineProps({
  metrics: Object,
  tenantSlug: {
    type: String,
    default: null,
  },
})
const { tenantSlug } = props;
  const breadcrumbs = [
    {
        title: tenantSlug ? 'Dashboard' : 'Admin Dashboard', 
        href: tenantSlug ? route('dashboard', { tenantSlug }) : route('admin.dashboard'), 
    },
];
const showForm = ref(false)
const editing = ref({})

function openEditor() {
  editing.value = props.metrics ?? {}
  showForm.value = true
}

function closeEditor() {
  showForm.value = false
  editing.value = {}
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
      <div class="p-6 max-w-5xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Performance Metrics</h1>
        <button @click="openEditor" class="btn btn-primary">Edit Global Metrics</button>
      </div>

      <div v-if="showForm">
        <EntryForm :entry="editing" @saved="closeEditor" @cancel="closeEditor" />
      </div>

      <div v-else-if="metrics" class="bg-white p-6 rounded shadow space-y-2">
        <div v-for="(value, key) in metrics" :key="key">
          <strong>{{ key.replace(/_/g, ' ') }}:</strong> {{ Array.isArray(value) ? value.join(', ') : value }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>
