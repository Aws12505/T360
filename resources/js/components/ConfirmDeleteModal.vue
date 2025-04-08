<template>
  <!-- Modal overlay for deletion confirmation -->
  <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50 p-4">
    <div class="bg-background p-6 rounded-lg shadow-xl w-full max-w-md animate-in fade-in zoom-in-95 duration-200 border border-border">
      <div class="flex flex-col space-y-4">
        <h2 class="text-xl font-bold text-foreground">Confirm Deletion</h2>
        <p class="text-muted-foreground">{{ message }}</p>
        
        <div class="flex justify-end space-x-3 pt-2">
          <Button
            type="button"
            @click="cancel"
            variant="outline"
          >
            Cancel
          </Button>
          <Button
            type="button"
            variant="destructive"
            @click="confirmDelete"
            :disabled="form.processing"
          >
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            {{ form.processing ? 'Deleting...' : 'Delete' }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
// Import UI components
import { Button } from '@/components/ui/button';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps({
  message: { type: String, default: 'Are you sure you want to delete this item?' },
  deleteUrl: { type: String, required: true },
  tenantSlug: { type: String, default: null },
});
const emit = defineEmits(['cancel', 'confirmed']);

// Initialize an empty form to use for deletion request.
const form = useForm({});

// Function to call the delete route and emit confirmation.
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
