<template>
    <AppLayout
      :breadcrumbs="breadcrumbs"
      :tenantSlug="tenantSlug"
      :permissions="permissions"
    >
      <Head title="Driver Profile" />
      <DriverProfile :driver="driver" :tenantSlug="tenantSlug" :driverID="driverID"/>
    </AppLayout>
  </template>
  
  <script setup>
  import { Head } from '@inertiajs/vue3'
  import AppLayout from '@/layouts/AppLayout.vue'
  import DriverProfile from '@/components/DriverProfileComponent.vue'
  import { computed } from 'vue'
  
  const props = defineProps({
    driver: Object,
    tenantSlug: String,
    permissions: Array,
    driverID: Number,
  })
  
  const breadcrumbs = computed(() => [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href: props.tenantSlug
        ? route('dashboard', { tenantSlug: props.tenantSlug })
        : route('admin.dashboard'),
    },
    {
      title: 'Drivers',
      href: props.tenantSlug
        ? route('driver.index', { tenantSlug: props.tenantSlug })
        : route('driver.index.admin'),
    },
    {
      title: props.driver.name,
      href: '#',
    },
  ])
  </script>
  