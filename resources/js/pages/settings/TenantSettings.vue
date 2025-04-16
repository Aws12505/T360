<template>
  <AppLayout :breadcrumbs="props.breadcrumbItems" :tenantSlug="props.tenantSlug">
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

        <!-- Subscription Information Section -->
        <div v-if="subscription" class="mt-8">
          <HeadingSmall 
            title="Subscription Details" 
            description="Your current subscription information"
          />
          
          <Separator class="my-4" />
          
          <div class="bg-card rounded-lg border shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Subscription Name and Description -->
              <div class="space-y-2">
                <h3 class="text-lg font-semibold">{{ subscription.name }}</h3>
                <p class="text-sm text-muted-foreground">{{ subscription.description || 'No description available' }}</p>
              </div>
              
              <!-- Subscription Price -->
              <div class="space-y-2 md:text-right">
                <div class="text-2xl font-bold">
                  {{ formatCurrency(subscription.price, subscription.currency_code) }}
                </div>
                <Badge variant="outline">
                  {{ subscription.payment_gateway }}
                </Badge>
              </div>
              
              <!-- Billing Information -->
              <div class="space-y-3 col-span-1 md:col-span-2">
                <Separator />
                <h4 class="font-medium">Billing Information</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                  <!-- Next Billing Date -->
                  <div class="bg-muted/50 p-3 rounded-md">
                    <div class="text-xs text-muted-foreground">Next Billing</div>
                    <div class="font-medium">{{ formatDate(subscription.next_billing_at) }}</div>
                  </div>
                  
                  <!-- Last Billing Date -->
                  <div class="bg-muted/50 p-3 rounded-md">
                    <div class="text-xs text-muted-foreground">Last Billing</div>
                    <div class="font-medium">{{ formatDate(subscription.last_billing_at) }}</div>
                  </div>
                  
                  <!-- Payment Method -->
                  <div class="bg-muted/50 p-3 rounded-md">
                    <div class="text-xs text-muted-foreground">Payment Method</div>
                    <div class="font-medium">{{ subscription.card_type }} •••• {{ subscription.last_four_digits }}</div>
                    <div class="text-xs text-muted-foreground">Expires {{ formatCardExpiry(subscription.expiry_month, subscription.expiry_year) }}</div>
                  </div>
                </div>
              </div>
              
              <!-- Billing Address -->
              <div v-if="subscription.billing_address" class="space-y-3 col-span-1 md:col-span-2">
                <Separator />
                <h4 class="font-medium">Billing Address</h4>
                
                <div class="bg-muted/50 p-3 rounded-md">
                  <div v-if="subscription.billing_address.street">{{ subscription.billing_address.street }}</div>
                  <div v-if="subscription.billing_address.street2">{{ subscription.billing_address.street2 }}</div>
                  <div>
                    <span v-if="subscription.billing_address.city">{{ subscription.billing_address.city }}, </span>
                    <span v-if="subscription.billing_address.state">{{ subscription.billing_address.state }} </span>
                    <span v-if="subscription.billing_address.zip">{{ subscription.billing_address.zip }}</span>
                  </div>
                  <div v-if="subscription.billing_address.country">{{ subscription.billing_address.country }}</div>
                  <div v-if="subscription.billing_address.phone" class="mt-1 text-sm text-muted-foreground">
                    Phone: {{ subscription.billing_address.phone }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
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
  },
  subscription: {
    type: Object,
    default: null
  }
});

// Make breadcrumbItems reactive with computed property
const breadcrumbItems = computed(() => [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard', 
    href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'), 
  },
]);

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

// Format currency with symbol
const formatCurrency = (amount, currencyCode = 'USD') => {
  if (!amount) return '$0.00';
  
  const formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currencyCode || 'USD',
  });
  
  return formatter.format(amount);
};

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Format card expiry
const formatCardExpiry = (month, year) => {
  if (!month || !year) return 'N/A';
  
  // Format month to be 2 digits
  const formattedMonth = month.toString().padStart(2, '0');
  
  // Format year to be last 2 digits if it's 4 digits
  const formattedYear = year.toString().length === 4 ? year.toString().slice(-2) : year.toString();
  
  return `${formattedMonth}/${formattedYear}`;
};
</script>