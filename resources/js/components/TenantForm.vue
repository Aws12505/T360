<template>
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded shadow-lg w-96 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">
          {{ tenant ? 'Edit Tenant' : 'Create Tenant' }}
        </h2>
        <form @submit.prevent="submit">
          <!-- Name Field -->
          <div class="mb-3">
            <label class="block mb-1">Name</label>
            <Input v-model="form.name" placeholder="Enter tenant name" />
          </div>
          <!-- Slug Field -->
          <div class="mb-3">
            <label class="block mb-1">Slug</label>
            <Input v-model="form.slug" placeholder="Enter tenant slug" />
          </div>
          <!-- Action Buttons -->
          <div class="flex justify-end space-x-2">
            <Button type="button" @click="() => emit('close')">Cancel</Button>
            <Button type="submit">Save</Button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { watch } from 'vue';
  import { useForm } from '@inertiajs/vue3';
  import Input from '@/components/ui/input/Input.vue';
  import Button from '@/components/ui/button/Button.vue';
  
  const props = defineProps({
    tenant: { type: Object, default: null },
  });
  
  const emit = defineEmits(['close', 'saved']);
  
  // Initialize the form with name and slug.
  const form = useForm({
    name: props.tenant ? props.tenant.name : '',
    slug: props.tenant ? props.tenant.slug : '',
  });
  
  // Update form values if the tenant prop changes.
  watch(() => props.tenant, (newVal) => {
    if (newVal) {
      form.name = newVal.name;
      form.slug = newVal.slug;
    } else {
      form.name = '';
      form.slug = '';
    }
  }, { immediate: true });
  
  const submit = () => {
    if (props.tenant) {
      // Update tenant (adjust route as needed)
      form.put(route('admin.tenants.update', props.tenant), {
        onSuccess: () => {
          emit('saved');
        },
      });
    } else {
      // Create tenant
      form.post(route('admin.tenants.store'), {
        onSuccess: () => {
          emit('saved');
        },
      });
    }
  };
  </script>
  