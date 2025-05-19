<!-- FeedbackShow.vue -->
<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
      <Head :title="`Feedback #${feedback.id}`" />
  
      <div class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
        <Alert v-if="successMessage" variant="success" class="animate-in fade-in duration-300">
          <AlertTitle class="flex items-center gap-2">
            <Icon name="check-circle" class="h-5 w-5 text-green-500" />
            Success
          </AlertTitle>
          <AlertDescription>{{ successMessage }}</AlertDescription>
        </Alert>
  
        <Card class="shadow-sm hover:shadow transition-all duration-300">
          <CardHeader class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-3 border-b">
            <div>
              <div class="flex items-center gap-2">
                <Icon name="message-circle" class="h-5 w-5 text-primary" />
                <CardTitle class="text-xl">{{ feedback.subject }}</CardTitle>
              </div>
              <p class="text-sm text-muted-foreground mt-1 flex items-center gap-1">
                <Icon name="calendar" class="h-3.5 w-3.5" />
                {{ formatDateTime(feedback.created_at) }}
                <span class="flex items-center gap-1 ml-2">
                  <Icon name="user" class="h-3.5 w-3.5" />
                  by {{ feedback.user.name }}
                </span>
              </p>
            </div>
          </CardHeader>
          <CardContent class="pt-4">
            <div class="bg-muted/30 p-4 rounded-md border border-border/50">
              <p class="whitespace-pre-line">{{ feedback.message }}</p>
            </div>
          </CardContent>
        </Card>
  
        <div class="flex justify-center mt-8">
          <Link
            :href="props.tenantSlug
              ? route('support.feedback.index', { tenantSlug: tenantSlug })
              : route('support.feedback.index.admin')"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground shadow-sm"
          >
            <Icon name="arrow-left" class="mr-2 h-4 w-4" />
            Back to Feedback
          </Link>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { Head, Link } from '@inertiajs/vue3';
  import AppLayout from '@/layouts/AppLayout.vue';
  import Icon from '@/components/Icon.vue';
  import { Card, CardHeader, CardTitle, CardContent, Alert, AlertTitle, AlertDescription } from '@/components/ui';
  
  const props = defineProps({
    feedback: Object,
    tenantSlug: { type: String, default: null }
  });
  
  const successMessage = ref('');
  const breadcrumbs = [
    { title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard', href: props.tenantSlug ? route('dashboard',{tenantSlug:props.tenantSlug}) : route('admin.dashboard') },
    { title: 'User Feedback', href: props.tenantSlug ? route('support.feedback.index',{tenantSlug:props.tenantSlug}) : route('support.feedback.index.admin') },
    { title: `#${props.feedback.id}`, href: '' }
  ];
  
  function formatDateTime(dt) {
    const d = new Date(dt);
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    return `${d.getMonth()+1}-${d.getDate()}-${d.getFullYear()} ${hh}:${mm}`;
  }
  
  // Mark seen happens on backend
  onMounted(() => {});
  </script>
  