<template>
  <!-- Modal overlay -->
  <div class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50 p-4">
    <!-- Modal container -->
    <div class="bg-background p-6 rounded-lg shadow-xl w-full max-w-md overflow-y-auto max-h-[90vh] animate-in fade-in zoom-in-95 duration-200 border border-border">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-foreground">
          {{ tenant ? 'Edit Company' : 'Create Company' }}
        </h2>
        <Button variant="ghost" size="icon" @click="() => emit('close')">
          <X class="h-5 w-5" />
        </Button>
      </div>
      
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Name Field -->
        <div class="space-y-2">
          <Label for="name">Company Name</Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="Enter company name"
            class="w-full"
          />
          <InputError :message="form.errors.name" />
        </div>
        
        <!-- Slug Field (only for editing) -->
        <div v-if="tenant" class="space-y-2">
          <Label for="slug">Slug</Label>
          <Input
            id="slug"
            v-model="form.slug"
            placeholder="Enter company slug"
            class="w-full"
          />
          <InputError :message="form.errors.slug" />
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
          <Button
            type="button"
            @click="() => emit('close')"
            variant="outline"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            {{ form.processing ? 'Saving...' : 'Save' }}
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const props = defineProps({
  tenant: { type: Object, default: null },
});
const emit = defineEmits(['close', 'saved']);

// Initialize form with tenant data if available
const form = useForm({
  name: props.tenant ? props.tenant.name : '',
  slug: props.tenant ? props.tenant.slug : '',
});

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
    form.put(route('admin.tenants.update', props.tenant), {
      onSuccess: () => {
        emit('saved');
      },
    });
  } else {
    form.post(route('admin.tenants.store'), {
      onSuccess: () => {
        emit('saved');
      },
    });
  }
};
</script>
