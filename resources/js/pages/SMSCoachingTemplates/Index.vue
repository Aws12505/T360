<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
      <Head title="SMS Coaching Templates" />
      <SettingsLayout :permissions="permissions">
        <div class="space-y-6">
          <HeadingSmall title="Templates" description="Manage SMS coaching templates." />
          <div class="flex justify-end">
            <Link :href="route('sms-coaching-templates.create',[tenantSlug])" class="btn">New Template</Link>
          </div>
          <div class="overflow-x-auto">
            <table class="table-auto w-full">
              <thead>
                <tr>
                  <th>Coaching Message</th>
                  <th>Acceptance</th>
                  <th>On-Time</th>
                  <th>Green Zone</th>
                  <th>Severe Alerts</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="template in templates" :key="template.id">
                  <td v-html="template.coaching_message" class="max-w-[300px] truncate" />
                  <td>{{ template.acceptance }}</td>
                  <td>{{ template.ontime }}</td>
                  <td>{{ template.greenzone }}</td>
                  <td>{{ template.severe_alerts }}</td>
                  <td class="space-x-2">
  <Link :href="route('sms-coaching-templates.show', [tenantSlug, template.id])">View</Link>
  <Link :href="route('sms-coaching-templates.edit', [tenantSlug, template.id])">Edit</Link>
</td>
                </tr>
              </tbody>
            </table>
          </div>
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
    templates: Array,
    tenantSlug: String,
    permissions: Array
  });
  
  const breadcrumbs = computed(() => [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard')
    }
  ]);
  </script>
  