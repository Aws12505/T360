<template>
    <form @submit.prevent="emit('submit')" class="space-y-6">
      <div class="space-y-2">
        <Label for="coaching_message">Coaching Message</Label>
        <QuillEditor
          id="coaching_message"
          v-model:content="form.coaching_message"
          content-type="html"
          class="border rounded-md"
        />
        <InputError :message="form.errors.coaching_message" />
      </div>
  
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="(label, field) in fields" :key="field" class="space-y-2">
          <Label :for="field">{{ label }}</Label>
          <select
            :id="field"
            v-model="form[field]"
            class="w-full border rounded-md p-2"
          >
            <option value="">Select...</option>
            <option v-for="option in options" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
          <InputError :message="form.errors[field]" />
        </div>
      </div>
  
      <div class="space-y-2">
        <Label for="tenant_id">Tenant ID</Label>
        <Input id="tenant_id" type="number" v-model="form.tenant_id" />
        <InputError :message="form.errors.tenant_id" />
      </div>
  
      <Button type="submit" :disabled="form.processing">
        <span v-if="form.processing">Saving...</span>
        <span v-else>Save</span>
      </Button>
    </form>
  </template>
  
  <script setup>
  import { defineProps, defineEmits } from 'vue';
  import { Input } from '@/components/ui/input';
  import { Label } from '@/components/ui/label';
  import InputError from '@/components/InputError.vue';
  import { Button } from '@/components/ui/button';
  import { QuillEditor } from '@vueup/vue-quill';
  import '@vueup/vue-quill/dist/vue-quill.snow.css';
  
  const props = defineProps({ form: Object });
  const emit = defineEmits(['submit']);
  
  const fields = {
    acceptance: 'Acceptance',
    ontime: 'On-Time',
    greenzone: 'Green Zone',
    severe_alerts: 'Severe Alerts',
  };
  
  const options = ['good', 'bad', 'minor_improvement'];
  </script>
  