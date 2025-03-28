<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Performance Summaries</h1>
      <!-- Display summaries as a responsive grid of cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <SummaryCard
          v-for="(summary, range) in summaries"
          :key="range"
          :range="formatRange(range)"
          :data="summary.data"
          :ratings="summary.ratings"
          :safetySummary="summary.safety_summary"
          :rejectionBreakdown="rejectionBreakdowns?.[range]"
          :delayBreakdown="delayBreakdowns?.[range]"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'
import SummaryCard from '@/components/SummaryCard.vue'
import AppLayout from '@/layouts/AppLayout.vue'

/**
 * Props:
 * - summaries: Object containing performance summary data.
 * - rejectionBreakdowns: Object with breakdown data for rejections.
 * - delayBreakdowns: Object with breakdown data for delays.
 * - tenantSlug: Optional tenant identifier.
 * - SuperAdmin: Boolean flag indicating SuperAdmin status.
 * - tenants: Array of tenant objects.
 */
const props = defineProps({
  summaries: Object,
  rejectionBreakdowns: Object,
  delayBreakdowns: Object,
  tenantSlug: String,
  SuperAdmin: Boolean,
  tenants: Array,
})

// Build breadcrumbs using tenantSlug to determine the correct dashboard route.
const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
]

// Helper function to format summary range keys into human-friendly labels.
const formatRange = (key: string) => {
  const labels: Record<string, string> = {
    yesterday: 'Yesterday',
    current_week: 'Current Week',
    rolling_6_weeks: 'Rolling 6 Weeks',
    quarterly: 'Quarterly',
  }
  return labels[key] ?? key
}
</script>
