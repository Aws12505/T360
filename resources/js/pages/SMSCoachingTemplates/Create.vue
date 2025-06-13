<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
      <Head title="Create Template" />
      <SettingsLayout :permissions="permissions">
        <HeadingSmall title="New Coaching Template" description="Create a new SMS coaching template." />
        <Form :form="form" @submit="submit" />
      </SettingsLayout>
    </AppLayout>
  </template>
  
  <script setup>
  import { useForm, Head } from '@inertiajs/vue3';
  import { computed } from 'vue';
  import AppLayout from '@/layouts/AppLayout.vue';
  import SettingsLayout from '@/layouts/settings/Layout.vue';
  import HeadingSmall from '@/components/HeadingSmall.vue';
  import Form from './Form.vue';
  
  const props = defineProps({
    tenantSlug: String,
    permissions: Array,
  });
  
  const form = useForm({
    coaching_message: '',
    acceptance: '',
    ontime: '',
    greenzone: '',
    severe_alerts: '',
    tenant_id: '',
  });
  
  const submit = () => {
    form.post(route('sms-coaching-templates.store', props.tenantSlug));
  };
  
  const breadcrumbs = computed(() => [
    { title: 'Templates', href: route('sms-coaching-templates.index',props.tenantSlug) }
  ]);
  </script>
  