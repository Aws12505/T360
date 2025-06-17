<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
    <Head title="Template Details" />
    <SettingsLayout :permissions="permissions">
      <div class="space-y-8">
        <DeleteConfirmation
          :show="showDeleteModal"
          :template="template"
          @close="showDeleteModal = false"
          @confirm="deleteTemplate"
        >
          <template #title>Delete Template</template>
          <template #content>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              Are you sure you want to delete this template? This action cannot be undone.
            </p>
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4">
              <p class="text-gray-700 dark:text-gray-300 font-medium line-clamp-2">
                "{{ template.coaching_message.substring(0, 100) }}..."
              </p>
            </div>
          </template>
        </DeleteConfirmation>

        <div class="flex flex-col">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">Template Details</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400 max-w-3xl">
            Full details of the coaching template with message preview and performance metrics.
          </p>
        </div>

        <Card class="overflow-hidden rounded-xl shadow-md border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-6">
            <!-- Message Preview -->
            <div class="lg:col-span-2 space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <Label class="text-lg font-bold text-gray-800 dark:text-gray-200">Message Preview</Label>
                <Badge variant="outline" class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-1.5 font-medium">
                  <span class="font-mono">{{ getWeightedCharCount(template.coaching_message) }}</span> characters
                </Badge>
              </div>

              <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-6 bg-white dark:bg-gray-800 min-h-[200px]">
                <div class="whitespace-pre-wrap text-gray-700 dark:text-gray-300 leading-relaxed font-sans text-base"
                     v-html="formatMessageWithTokens(template.coaching_message)" />
              </div>

              <div class="flex items-center pt-3">
                <div class="mr-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Created: {{ formatDate(template.created_at) }}</p>
                </div>
              </div>
            </div>
            
            <!-- Performance Metrics -->
            <div class="space-y-5">
              <Label class="text-lg font-bold text-gray-800 dark:text-gray-200">Performance Metrics</Label>
              <div class="space-y-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                <div v-for="(label, field) in metrics" :key="field" class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                  <Label class="text-gray-700 dark:text-gray-300 font-medium">{{ label }}</Label>
                  <Badge :variant="getBadgeVariant(template[field])" class="capitalize px-3 py-1.5 text-sm font-medium">
                    {{ formatOption(template[field]) }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="bg-gray-50 dark:bg-gray-800/40 px-6 py-5 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 justify-between">
            <div>
              <Link 
  :href="route('sms-coaching-templates.index', { tenantSlug: tenantSlug })" 
  class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-input bg-background hover:bg-accent hover:text-accent-foreground text-sm font-medium shadow-sm transition-colors"
>
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
  </svg>
  Back to Templates
</Link>
            </div>
            
            <div class="flex gap-3 flex-wrap">
              <Link 
  :href="route('sms-coaching-templates.edit', { 
    tenantSlug: tenantSlug, 
    id: template.id 
  })"
  class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-primary-foreground font-medium text-sm shadow-sm transition-colors"
>
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
  </svg>
  Edit Template
</Link>
<button
  @click="showDeleteModal = true"
  class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-destructive hover:bg-destructive/90 text-destructive-foreground font-medium text-sm shadow-sm transition-colors"
>
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
  </svg>
  Delete Template
</button>
            </div>
          </div>
        </Card>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';

const props = defineProps({
  template: Object,
  tenantSlug: String,
  permissions: Array
});

const showDeleteModal = ref(false);
const metrics = {
  acceptance: 'Acceptance',
  ontime: 'On-Time',
  greenzone: 'Green Zone',
  severe_alerts: 'Severe Alerts'
};

const breadcrumbs = computed(() => [
  { title: 'Dashboard', href: route('dashboard', { tenantSlug: props.tenantSlug }) },
  { title: 'Templates', href: route('sms-coaching-templates.index', { tenantSlug: props.tenantSlug }) },
  { title: 'Template Details', href: route('sms-coaching-templates.show', { tenantSlug: props.tenantSlug, id: props.template.id }) }
]);

const formatOption = (option) => option?.split('_').map(w => w[0].toUpperCase() + w.slice(1)).join(' ') ?? '';

const getBadgeVariant = (val) => {
  return val === 'good' ? 'success' : val === 'bad' ? 'destructive' : val === 'minor_improvement' ? 'warning' : 'default';
};

const formatDate = (date) => {
  const d = new Date(date);
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const placeholderMap = {
  '{driver_first_name}': 'First Name',
  '{driver_last_name}': 'Last Name',
  '{driver_ontime_score}': 'On-Time %',
  '{driver_acceptance_score}': 'Acceptance %',
  '{driver_greenzone_score}': 'Green Zone',
  '{driver_severe_alerts}': 'Severe Alerts',
  '{company_avg_ontime}': 'Avg On-Time %',
  '{company_avg_acceptance}': 'Avg Acceptance %',
  '{company_avg_greenzone}': 'Avg Green Zone'
};

const formatMessageWithTokens = (message) => {
  return message?.replace(/{[^}]+}/g, (match) => {
    const label = placeholderMap[match] || match;
    return `<span class="inline-block bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 rounded px-1.5 py-0.5 text-xs font-mono mr-1 mb-1 align-middle whitespace-nowrap">&lt;${label}&gt;</span>`;
  }) ?? '';
};

const deleteTemplate = () => {
  router.delete(route('sms-coaching-templates.destroy', {
    tenantSlug: props.tenantSlug,
    id: props.template.id
  }), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
    }
  });
};

const getWeightedCharCount = (message) => {
  if (!message) return 0;
  const placeholderMatches = message.match(/{[^}]+}/g) || [];
  const placeholderCount = placeholderMatches.length;
  const plainTextLength = message.replace(/{[^}]+}/g, '').length;
  return plainTextLength + (placeholderCount * 5);
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>