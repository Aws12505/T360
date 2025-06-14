<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
    <Head title="Edit Template" />
    <SettingsLayout :permissions="permissions">
      <div class="space-y-6">
        <HeadingSmall 
          title="Edit Coaching Template" 
          description="Modify an existing SMS coaching template." 
        />
        <Form :form="form" @submit="submit" />
      </div>
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
  tenantSlug: {
    type: String,
    required: true
  },
  permissions: {
    type: Array,
    required: true
  },
});

const form = useForm({
  ...props.template,
  tenant_id: props.template.tenant_id || null
});

const submit = () => {
  form.put(route('sms-coaching-templates.update', {
    tenantSlug: props.tenantSlug,
    sms_coaching_template: props.template.id
  }), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
    onError: () => {
      if (form.errors.tenant_id) {
        form.reset('tenant_id');
      }
    }
  });
};

const breadcrumbs = computed(() => [
  { 
    title: 'Dashboard', 
    href: route('dashboard', { tenantSlug: props.tenantSlug }) 
  },
  { 
    title: 'Templates', 
    href: route('sms-coaching-templates.index', { tenantSlug: props.tenantSlug }) 
  },
  { 
    title: 'Edit Template', 
    href: route('sms-coaching-templates.edit', { 
      tenantSlug: props.tenantSlug,
      id: props.template.id // use 'id' because you used ->parameters(['sms-coaching-templates' => 'id'])
    }) 
  }
]);
</script>