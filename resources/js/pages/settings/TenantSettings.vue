<template>
  <AppLayout :breadcrumbs="breadcrumbItems" :tenantSlug="tenantSlug">
    <Head title="Company Settings" />

    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall 
          title="Company Settings" 
          description="Update your company information and logo"
        />
        
        <Separator />
        
        <!-- Company Logo Preview Section -->
        <div v-if="tenant.image_path" class="flex flex-col items-center space-y-3 p-4 border rounded-md bg-muted/50">
          <div class="text-sm font-medium">Current Company Logo</div>
          <div class="w-32 h-32 rounded-md overflow-hidden border border-border flex items-center justify-center bg-white">
            <img 
              :src="`/storage/${tenant.image_path}`" 
              alt="Company Logo" 
              class="max-w-full max-h-full object-contain"
            />
          </div>
          <div class="text-xs text-muted-foreground">
            Logo is optimized and converted to SVG for better quality
          </div>
        </div>
        
        <form @submit.prevent="updateTenant" class="space-y-6" enctype="multipart/form-data">
          <!-- Company Name -->
          <div class="grid gap-2">
            <Label for="name">Company Name</Label>
            <Input 
              id="name" 
              v-model="form.name" 
              placeholder="Enter company name"
              class="mt-1 block w-full"
            />
            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
          </div>
          
          <!-- Slug -->
          <div class="grid gap-2">
            <Label for="slug">Slug</Label>
            <Input 
              id="slug" 
              v-model="form.slug" 
              placeholder="Enter company slug"
              class="mt-1 block w-full"
            />
            <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
          </div>
          
          <!-- Company Logo -->
          <div class="grid gap-2">
            <Label for="image">Update Company Logo</Label>
            <div class="flex items-start space-x-4">
              <div v-if="imagePreview" class="w-24 h-24 rounded-md overflow-hidden border border-border flex items-center justify-center bg-white">
                <img 
                  :src="imagePreview" 
                  alt="New Company Logo Preview" 
                  class="max-w-full max-h-full object-contain"
                />
              </div>
              <div class="flex-1">
                <Input 
                  id="image" 
                  type="file" 
                  @change="handleImageChange" 
                  accept="image/jpeg,image/png,image/gif,image/svg+xml"
                  class="mt-1 block w-full"
                />
                <p class="text-xs text-muted-foreground mt-1">
                  Accepted formats: JPEG, PNG, GIF, SVG. Max size: 2MB. Will be optimized and converted to SVG.
                </p>
                <p v-if="form.errors.image" class="text-sm text-destructive">{{ form.errors.image }}</p>
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-4">
            <Button type="submit" :disabled="form.processing">
              <span v-if="form.processing">Updating...</span>
              <span v-else>Save Changes</span>
            </Button>
            
            <Transition
              enter-active-class="transition ease-in-out"
              enter-from-class="opacity-0"
              leave-active-class="transition ease-in-out"
              leave-to-class="opacity-0"
            >
              <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
            </Transition>
          </div>
        </form>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/components/ui/toast/use-toast';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps({
  tenant: {
    type: Object,
    required: true
  },
  tenantSlug: {
    type: String,
    required: true
  }
});

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Company Settings',
    href: `/${props.tenantSlug}/settings/tenant`,
  },
];

const { toast } = useToast();
const imagePreview = ref<string | null>(null);

// Initialize form with tenant data
const form = useForm({
  name: props.tenant.name,
  slug: props.tenant.slug,
  image: null as File | null,
});

// Handle image selection and preview
const handleImageChange = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    form.image = input.files[0];
    
    // Create preview URL
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(input.files[0]);
  }
};

// Submit form to update tenant
const updateTenant = () => {
  form.post(route('tenant.settings.update', props.tenantSlug), {
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Company settings updated successfully",
      });
    },
    preserveScroll: true,
  });
};
</script>