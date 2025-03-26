<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
      <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Performance Summaries</h1>
  
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
          <SummaryCard
            v-for="(summary, range) in summaries"
            :key="range"
            :range="formatRange(range)"
            :data="summary.data"
            :ratings="summary.ratings"
            :safetySummary="summary.safety_summary"
          />
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { defineProps } from 'vue'
  import SummaryCard from '@/Components/SummaryCard.vue'
  import AppLayout from '@/layouts/AppLayout.vue'
  
  const props = defineProps({
    summaries: Object,
    tenantSlug: {
      type: String,
      default: null
    },
    SuperAdmin: {
      type: Boolean,
      default: false,
    },
    tenants: {
      type: Array,
      default: () => [],
    }
  })
  
  const breadcrumbs = [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href: props.tenantSlug
        ? route('dashboard', { tenantSlug: props.tenantSlug })
        : route('admin.dashboard')
    }
  ]
  
  const formatRange = (key) => {
    const labels = {
      yesterday: 'Yesterday',
      current_week: 'Current Week',
      rolling_6_weeks: 'Rolling 6 Weeks',
      quarterly: 'Quarterly',
    }
    return labels[key] ?? key
  }
  </script>
  