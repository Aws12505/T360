<template>
  <div>
    <DialogHeader>
      <DialogTitle class="text-xl sm:text-2xl font-bold">
        {{ tenant ? 'Edit Company' : 'Create Company' }}
      </DialogTitle>
    </DialogHeader>
    
    <form @submit.prevent="submit" class="space-y-4 mt-4">
      <!-- Name Field -->
      <div class="space-y-2">
        <Label for="name" class="flex items-center">
          <span>Company Name</span>
          <span class="text-destructive ml-1">*</span>
        </Label>
        <Input
          id="name"
          v-model="form.name"
          placeholder="Enter company name"
          class="w-full"
          :class="{ 'border-destructive': form.errors.name }"
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
          :class="{ 'border-destructive': form.errors.slug }"
        />
        <InputError :message="form.errors.slug" />
      </div>
      
      <!-- Timezone field -->
      <div class="space-y-2">
        <Label for="timezone" class="flex items-center">
          <span>Timezone</span>
          <span class="text-destructive ml-1">*</span>
        </Label>
        <select
          id="timezone"
          v-model="form.timezone"
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          :class="{ 'border-destructive': form.errors.timezone }"
        >
          <option v-for="(label, tz) in usTimezones" :key="tz" :value="tz">
            {{ label }}
          </option>
        </select>
        <InputError :message="form.errors.timezone" />
      </div>
      
      <!-- Action Buttons -->
      <DialogFooter class="mt-6">
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
      </DialogFooter>
    </form>
  </div>
</template>

<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Import UI components
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import InputError from '@/components/InputError.vue';
import { Loader2, X } from 'lucide-vue-next';

const props = defineProps({
  tenant: { type: Object, default: null },
});
const emit = defineEmits(['close', 'saved']);

// US-focused timezone list
const usTimezones = {
  'America/New_York': 'Eastern Time (ET)',
  'America/Chicago': 'Central Time (CT)',
  'America/Denver': 'Mountain Time (MT)',
  'America/Los_Angeles': 'Pacific Time (PT)',
  'America/Anchorage': 'Alaska Time (AKT)',
  'Pacific/Honolulu': 'Hawaii Time (HAST)',
  'America/Phoenix': 'Arizona Time (no DST)',
  'America/Indiana/Indianapolis': 'Eastern Time (Indiana)',
};

// Initialize form with tenant data if available
const form = useForm({
  name: props.tenant ? props.tenant.name : '',
  slug: props.tenant ? props.tenant.slug : '',
  timezone: props.tenant ? props.tenant.timezone : 'America/Indiana/Indianapolis',
});

// Watch for changes to tenant (edit mode)
watch(() => props.tenant, (newVal) => {
  if (newVal) {
    form.name = newVal.name;
    form.slug = newVal.slug;
    form.timezone = newVal.timezone;
  } else {
    form.name = '';
    form.slug = '';
    form.timezone = 'America/Indiana/Indianapolis';
  }
}, { immediate: true });

// Submit form
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
