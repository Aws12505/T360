<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
      <Head title="Edit Template" />
      <SettingsLayout :permissions="permissions">
        <HeadingSmall title="Edit Coaching Template" description="Modify an existing SMS coaching template." />
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
    template: Object,
    tenantSlug: String,
    permissions: Array,
  });
  
  const form = useForm({ ...props.template });
  
  const submit = () => {
    form.put(route('sms-coaching-templates.update', props.template.id));
  };
  
  const breadcrumbs = computed(() => [
    { title: 'Templates', href: route('sms-coaching-templates.index') }
  ]);
  </script>
  