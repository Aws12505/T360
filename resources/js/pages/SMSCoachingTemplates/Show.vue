<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
      <Head title="Template Details" />
      <SettingsLayout :permissions="permissions">
        <HeadingSmall title="Template Details" description="Full details of the coaching template." />
        <div class="space-y-4">
          <div><strong>Message:</strong></div>
          <div class="border p-4 rounded-md prose" v-html="template.coaching_message" />
          <p><strong>Acceptance:</strong> {{ template.acceptance }}</p>
          <p><strong>On-Time:</strong> {{ template.ontime }}</p>
          <p><strong>Green Zone:</strong> {{ template.greenzone }}</p>
          <p><strong>Severe Alerts:</strong> {{ template.severe_alerts }}</p>
          <p><strong>Tenant ID:</strong> {{ template.tenant_id }}</p>
        </div>
        <div class="mt-4">
          <Link :href="route('sms-coaching-templates.index')" class="btn">Back</Link>
        </div>
      </SettingsLayout>
    </AppLayout>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  import { Head, Link } from '@inertiajs/vue3';
  import AppLayout from '@/layouts/AppLayout.vue';
  import SettingsLayout from '@/layouts/settings/Layout.vue';
  import HeadingSmall from '@/components/HeadingSmall.vue';
  
  const props = defineProps({
    template: Object,
    tenantSlug: String,
    permissions: Array
  });
  
  const breadcrumbs = computed(() => [
    { title: 'Templates', href: route('sms-coaching-templates.index') }
  ]);
  </script>
  