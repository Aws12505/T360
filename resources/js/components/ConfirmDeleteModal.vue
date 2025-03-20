<template>
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded shadow-lg w-96">
        <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
        <p class="mb-4">{{ message }}</p>
        <div class="flex justify-end space-x-2">
          <Button type="button" @click="cancel">Cancel</Button>
          <Button type="button" variant="destructive" @click="confirmDelete">Delete</Button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { defineProps, defineEmits } from 'vue';
  import { useForm } from '@inertiajs/vue3';
  import Button from '@/components/ui/button/Button.vue';
  
  const props = defineProps({
    message: { type: String, default: 'Are you sure you want to delete this item?' },
    deleteUrl: { type: String, required: true },
  });
  const emit = defineEmits(['cancel', 'confirmed']);
  
  // Initialize an empty form to use for deletion.
  const form = useForm({});
  
  // When the user confirms deletion, call the delete method.
  const confirmDelete = () => {
    form.delete(props.deleteUrl, {
      onSuccess: () => {
        emit('confirmed');
      },
    });
  };
  
  const cancel = () => {
    emit('cancel');
  };
  </script>
  