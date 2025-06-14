<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug" :permissions="permissions">
    <Head title="SMS Coaching Templates" />
    <SettingsLayout :permissions="permissions">
      <!-- Delete Confirmation Dialog -->
      <DeleteConfirmation
  :show="showDeleteDialog"
  :template="selectedTemplate"
  @close="showDeleteDialog = false"
  @confirm="deleteTemplate"
>
        <template #title>Delete Template</template>
        <template #content>
          <p class="text-sm text-muted-foreground mb-4">
            Are you sure you want to delete this template? This action cannot be undone.
          </p>
          <div class="p-4 bg-muted/10 rounded-md">
            <p class="text-sm font-medium text-foreground/90 line-clamp-2">
              {{ selectedTemplate?.coaching_message ? stripAndFormat(selectedTemplate.coaching_message) : 'No message' }}
            </p>
          </div>
        </template>
      </DeleteConfirmation>

      <div class="space-y-6">
        <!-- Header with enhanced button styling -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">SMS Coaching Templates</h1>
            <p class="mt-1 text-sm text-muted-foreground max-w-3xl">
              Manage templates for performance feedback SMS coaching. Create templates for different driver performance scenarios.
            </p>
          </div>
          <Link 
            :href="route('sms-coaching-templates.create', { tenantSlug })" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium shadow-sm transition-all h-10"
          >
            <Plus class="h-7.5 w-7.5" />
            <span class="whitespace-nowrap">New Template</span>
          </Link>
        </div>

        <!-- Table with enhanced action buttons -->
        <Card class="overflow-hidden border rounded-xl shadow-sm">
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <Table>
                <TableHeader class="bg-muted/30">
                  <TableRow>
                    <TableHead class="min-w-[160px]">Message Preview</TableHead>
                    <TableHead class="hidden sm:table-cell">Acceptance</TableHead>
                    <TableHead class="hidden sm:table-cell">On-Time</TableHead>
                    <TableHead class="hidden md:table-cell">Green Zone</TableHead>
                    <TableHead class="hidden md:table-cell">Severe Alerts</TableHead>
                    <TableHead class="text-right min-w-[120px]">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow 
                    v-for="template in templates.data" 
                    :key="template.id" 
                    class="hover:bg-muted/10 transition-colors duration-150"
                  >
                    <TableCell class="max-w-[160px] lg:max-w-[240px] py-3">
                      <div class="text-sm font-medium text-foreground line-clamp-2">
                        {{ template.coaching_message ? stripAndFormat(template.coaching_message) : 'No message' }}
                      </div>
                    </TableCell>
                    <TableCell class="hidden sm:table-cell py-3">
                      <Badge :variant="getBadgeVariant(template.acceptance)" class="whitespace-nowrap capitalize text-xs">
                        {{ formatOption(template.acceptance) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="hidden sm:table-cell py-3">
                      <Badge :variant="getBadgeVariant(template.ontime)" class="whitespace-nowrap capitalize text-xs">
                        {{ formatOption(template.ontime) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="hidden md:table-cell py-3">
                      <Badge :variant="getBadgeVariant(template.greenzone)" class="whitespace-nowrap capitalize text-xs">
                        {{ formatOption(template.greenzone) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="hidden md:table-cell py-3">
                      <Badge :variant="getBadgeVariant(template.severe_alerts)" class="whitespace-nowrap capitalize text-xs">
                        {{ formatOption(template.severe_alerts) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="py-3">
                      <div class="flex justify-end space-x-3">
                        <Link
                          :href="route('sms-coaching-templates.show', { tenantSlug, id: template.id })"
                          class="inline-flex items-center justify-center p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors"
                          title="View Details"
                        >
                          <Eye class="h-7.5 w-7.5" />
                        </Link>
                        <Link
                          :href="route('sms-coaching-templates.edit', { tenantSlug, id: template.id })"
                          class="inline-flex items-center justify-center p-1 rounded-lg text-primary hover:bg-primary/10 transition-colors"
                          title="Edit Template"
                        >
                          <Pencil class="h-7.5 w-7.5" />
                        </Link>
                        <button
                          @click="openDeleteDialog(template)"
                          class="inline-flex items-center justify-center p-1 rounded-lg text-destructive hover:bg-destructive/10 transition-colors"
                          title="Delete Template"
                        >
                          <Trash2 class="h-7.5 w-7.5" />
                        </button>
                      </div>
                    </TableCell>
                  </TableRow>
                  <TableRow v-if="templates.data.length === 0">
                    <TableCell :colspan="6" class="py-12 text-center">
                      <div class="flex flex-col items-center justify-center space-y-4">
                        <div class="bg-muted/20 p-4 rounded-full">
                          <FileText class="h-10 w-10 text-muted-foreground" />
                        </div>
                        <div class="space-y-1.5">
                          <h3 class="text-lg font-medium text-foreground">No templates found</h3>
                          <p class="text-sm text-muted-foreground max-w-md">
                            Get started by creating a new SMS coaching template for your drivers.
                          </p>
                        </div>
                        <Link 
                          :href="route('sms-coaching-templates.create', { tenantSlug })" 
                          class="inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 h-10"
                        >
                          <Plus class="h-5 w-5" />
                          Create Template
                        </Link>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            
            <!-- Enhanced Pagination -->
            <div v-if="templates.meta && templates.data.length > 0" class="bg-muted/20 px-4 py-3 border-t flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="text-sm text-muted-foreground">
                Showing <span class="font-medium">{{ templates.meta.from }}</span> to 
                <span class="font-medium">{{ templates.meta.to }}</span> of 
                <span class="font-medium">{{ templates.meta.total }}</span> templates
              </div>
              <div class="flex space-x-2">
                <Link
                  v-if="templates.links.prev"
                  :href="templates.links.prev"
                  class="px-4 py-2 rounded-md text-sm font-medium text-foreground/80 hover:bg-muted/50 transition-colors border border-border h-10"
                  preserve-scroll
                >
                  &larr; Previous
                </Link>
                <Link
                  v-if="templates.links.next"
                  :href="templates.links.next"
                  class="px-4 py-2 rounded-md text-sm font-medium text-foreground/80 hover:bg-muted/50 transition-colors border border-border h-10"
                  preserve-scroll
                >
                  Next &rarr;
                </Link>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import DeleteConfirmation from '@/Components/DeleteConfirmation.vue';
import { 
  Card,
  CardContent,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
  Badge
} from '@/components/ui';
import { Plus, Eye, Pencil, Trash2, FileText } from 'lucide-vue-next';

const props = defineProps({
  templates: {
    type: Object,
    required: true,
    default: () => ({
      data: [],
      meta: {},
      links: {}
    })
  },
  tenantSlug: String,
  permissions: Array
});

const breadcrumbs = computed(() => [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard')
  },
  {
    title: 'SMS Templates',
    href: route('sms-coaching-templates.index', { tenantSlug: props.tenantSlug })
  }
]);

const formatOption = (option) => {
  if (!option) return '';
  return option
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const stripHtml = (html) => {
  if (!html) return '';
  return html.replace(/<[^>]*>/g, '');
};

const getBadgeVariant = (value) => {
  switch(value) {
    case 'good': return 'success';
    case 'bad': return 'destructive';
    case 'minor_improvement': return 'warning';
    default: return 'default';
  }
};

const showDeleteDialog = ref(false);
const selectedTemplate = ref(null);

const openDeleteDialog = (template) => {
  selectedTemplate.value = template;
  showDeleteDialog.value = true;
};

const deleteTemplate = () => {
  if (selectedTemplate.value) {
    router.delete(route('sms-coaching-templates.destroy', {
      tenantSlug: props.tenantSlug,
      id: selectedTemplate.value.id
    }), {
      preserveScroll: true,
      onSuccess: () => {
        showDeleteDialog.value = false;
      }
    });
  }
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

const stripAndFormat = (message) => {
  if (!message) return '';
  return message.replace(/{[^}]+}/g, (match) => {
    return `<${placeholderMap[match] || match}>`;
  });
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  th, td {
    padding: 0.5rem;
  }
}
</style>