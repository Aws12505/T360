<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
      <Head title="SMS Thresholds" />
  
      <SettingsLayout :permissions="permissions">
        <div class="space-y-6">
          <HeadingSmall 
            title="SMS Score Thresholds" 
            description="Manage performance thresholds for On-Time, Acceptance, Green Zone, and Severe Alerts."
          />
  
          <Separator />
  
          <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
            <template v-for="group in groups" :key="group.key">
              <div>
                <h3 class="text-sm font-medium mb-2 capitalize text-muted-foreground">
                  {{ group.label }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div v-for="field in group.fields" :key="field.key" class="grid gap-2">
                    <Label :for="field.key">{{ field.label }}</Label>
                    <Input
                      :id="field.key"
                      v-model="form[field.key]"
                      type="number"
                      step="0.01"
                      class="w-full"
                    />
                    <p v-if="form.errors[field.key]" class="text-sm text-destructive">
                      {{ form.errors[field.key] }}
                    </p>
                  </div>
                </div>
              </div>
            </template>
  
            <div class="flex items-center gap-4">
              <Button type="submit" :disabled="form.processing">
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Changes</span>
              </Button>
              <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
              </Transition>
            </div>
          </form>
        </div>
      </SettingsLayout>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  import { Head, useForm } from '@inertiajs/vue3'
  import AppLayout from '@/layouts/AppLayout.vue'
  import SettingsLayout from '@/layouts/settings/Layout.vue'
  import HeadingSmall from '@/components/HeadingSmall.vue'
  import { Button } from '@/components/ui/button'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Separator } from '@/components/ui/separator'
  import { useToast } from '@/components/ui/toast/use-toast'
  
  const props = defineProps({
    thresholds: Object,
    tenantSlug: String,
    permissions: Array,
  })
  
  const breadcrumbs = computed(() => [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'),
    },
  ])
  
  const { toast } = useToast()
  
  const form = useForm({
    on_time_good: props.thresholds?.on_time_good ?? '',
    on_time_bad: props.thresholds?.on_time_bad ?? '',
    on_time_minor_improvement: props.thresholds?.on_time_minor_improvement ?? '',
  
    acceptance_good: props.thresholds?.acceptance_good ?? '',
    acceptance_bad: props.thresholds?.acceptance_bad ?? '',
    acceptance_minor_improvement: props.thresholds?.acceptance_minor_improvement ?? '',
  
    greenzone_score_good: props.thresholds?.greenzone_score_good ?? '',
    greenzone_score_bad: props.thresholds?.greenzone_score_bad ?? '',
    greenzone_score_minor_improvement: props.thresholds?.greenzone_score_minor_improvement ?? '',
  
    severe_alerts_good: props.thresholds?.severe_alerts_good ?? '',
    severe_alerts_bad: props.thresholds?.severe_alerts_bad ?? '',
    severe_alerts_minor_improvement: props.thresholds?.severe_alerts_minor_improvement ?? '',
  })
  
  const groups = [
    {
      key: 'on_time',
      label: 'On-Time (%)',
      fields: [
        { key: 'on_time_good', label: 'Good' },
        { key: 'on_time_bad', label: 'Bad' },
        { key: 'on_time_minor_improvement', label: 'Minor Improvement' },
      ],
    },
    {
      key: 'acceptance',
      label: 'Acceptance (%)',
      fields: [
        { key: 'acceptance_good', label: 'Good' },
        { key: 'acceptance_bad', label: 'Bad' },
        { key: 'acceptance_minor_improvement', label: 'Minor Improvement' },
      ],
    },
    {
      key: 'greenzone_score',
      label: 'Green Zone Score (out of 1050)',
      fields: [
        { key: 'greenzone_score_good', label: 'Good' },
        { key: 'greenzone_score_bad', label: 'Bad' },
        { key: 'greenzone_score_minor_improvement', label: 'Minor Improvement' },
      ],
    },
    {
      key: 'severe_alerts',
      label: 'Severe Alerts',
      fields: [
        { key: 'severe_alerts_good', label: 'Good' },
        { key: 'severe_alerts_bad', label: 'Bad' },
        { key: 'severe_alerts_minor_improvement', label: 'Minor Improvement' },
      ],
    },
  ]
  
  const submit = () => {
    form.post(route('thresholds.update',props.tenantSlug), {
      onSuccess: () => {
        toast({
          title: 'Success',
          description: 'Thresholds saved successfully',
        })
      },
      preserveScroll: true,
    })
  }
  </script>
  