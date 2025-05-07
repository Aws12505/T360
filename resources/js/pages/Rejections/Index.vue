<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">

    <Head title="Acceptance" />
    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Success Message -->
      <Alert v-if="successMessage" variant="success">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>
  <!-- Error Message -->
  <Alert v-if="errorMessage" variant="destructive">
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>{{ errorMessage }}</AlertDescription>
      </Alert>
      <!-- Actions Section -->
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Acceptance</h1>
        <div class="flex flex-wrap gap-3">
          <Button @click="openForm()" variant="default">
            <Icon name="plus" class="mr-2 h-4 w-4" />
            Add Rejection
          </Button>
          <!-- Add Delete Selected button -->
          <Button v-if="selectedRejections.length > 0" @click="confirmDeleteSelected()" variant="destructive">
            <Icon name="trash" class="mr-2 h-4 w-4" />
            Delete Selected ({{ selectedRejections.length }})
          </Button>
          <div class="relative">
            <Button @click="showUploadOptions = !showUploadOptions" variant="secondary">
              <Icon name="upload" class="mr-2 h-4 w-4" />
              Upload CSV
              <Icon name="chevron-down" class="ml-2 h-4 w-4" />
            </Button>
            <div v-if="showUploadOptions" class="absolute right-0 mt-1 w-48 bg-background rounded-md shadow-lg border z-10">
              <div class="py-1">
                <label class="cursor-pointer block px-4 py-2 text-sm hover:bg-muted">
                  <span>Upload CSV File</span>
                  <input type="file" class="hidden" @change="handleImport" accept=".csv" />
                </label>
                <a :href="templateUrl" download="Rejections Template.csv" class="block px-4 py-2 text-sm hover:bg-muted">
                  Download Template
                </a>
              </div>
            </div>
          </div>
          <Button @click.prevent="exportCSV" variant="outline">
            <Icon name="download" class="mr-2 h-4 w-4" />
            Download CSV
          </Button>
          <Button v-if="isSuperAdmin" @click="openCodeModal()" variant="outline">
            <Icon name="settings" class="mr-2 h-4 w-4" />
            Manage Reason Codes
          </Button>
        </div>
      </div>

      <!-- Hidden Export Form -->
      <form ref="exportForm" :action="exportUrl" method="GET" class="hidden"></form>

      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-4 ">
          <div class="flex flex-col gap-2">
            <div class="flex flex-wrap gap-2">
              <Button @click="selectDateFilter('yesterday')" variant="outline" size="sm"
                :class="{ 'bg-primary/10 text-primary border-primary': activeTab === 'yesterday' }">
                Yesterday
              </Button>
              <Button @click="selectDateFilter('current-week')" variant="outline" size="sm"
                :class="{ 'bg-primary/10 text-primary border-primary': activeTab === 'current-week' }">
                WtD
              </Button>
              <Button @click="selectDateFilter('6w')" variant="outline" size="sm"
                :class="{ 'bg-primary/10 text-primary border-primary': activeTab === '6w' }">
                6 Weeks
              </Button>
              <Button @click="selectDateFilter('quarterly')" variant="outline" size="sm"
                :class="{ 'bg-primary/10 text-primary border-primary': activeTab === 'quarterly' }">
                Quarterly
              </Button>
              <Button @click="selectDateFilter('full')" variant="outline" size="sm"
                :class="{ 'bg-primary/10 text-primary border-primary': activeTab === 'full' }">
                Full
              </Button>
            </div>
            <div v-if="dateRange" class="text-sm text-muted-foreground">
              <span v-if="activeTab === 'yesterday' && dateRange.start">
                Showing data from {{ formatDate(dateRange.start) }}
              </span>
              <span v-else-if="dateRange.start && dateRange.end">
                Showing data from {{ formatDate(dateRange.start) }} to {{ formatDate(dateRange.end) }}
              </span>
              <span v-else>
                {{ dateRange.label }}
              </span>
              <span v-if="weekNumberText" class="ml-1">({{ weekNumberText }})</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Filters Section -->
      <Card>
        <CardHeader class="pb-2">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <CardTitle>Filters</CardTitle>
              <div v-if="!showFilters && hasActiveFilters" class="flex flex-wrap gap-2 ml-4">
                <div v-if="filters.search" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Search: {{ filters.search }}
                </div>
                <div v-if="filters.dateFrom" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  From: {{ formatDate(filters.dateFrom) }}
                </div>
                <div v-if="filters.dateTo" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  To: {{ formatDate(filters.dateTo) }}
                </div>
                <div v-if="filters.rejectionType" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Type: <span class="capitalize">{{ filters.rejectionType }}</span>
                </div>
                <div v-if="filters.reasonCode" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Reason: {{ getReasonCodeLabel(filters.reasonCode) }}
                </div>
                <div v-if="filters.penalty" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Penalty: {{ filters.penalty }}
                </div>
                <div v-if="filters.disputed" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Disputed: {{ filters.disputed === 'true' ? 'Yes' : 'No' }}
                </div>
                <div v-if="filters.driverControllable" class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold">
                  Driver Controllable: {{ filters.driverControllable === 'true' ? 'Yes' : (filters.driverControllable === 'false' ? 'No' : 'N/A') }}
                </div>
              </div>
            </div>
            <Button variant="ghost" size="sm" @click="showFilters = !showFilters">
              {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
              <Icon :name="showFilters ? 'chevron-up' : 'chevron-down'" class="ml-2 h-4 w-4" />
            </Button>
          </div>
        </CardHeader>
        <CardContent v-if="showFilters" class="pt-2">
          <div class="flex flex-col gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
              <div>
                <Label for="search">Search</Label>
                <Input id="search" v-model="filters.search" type="text" placeholder="Search by driver name..."
                  @input="applyFilters" class="w-full" />
              </div>
              <div>
                <Label for="dateFrom">Date From</Label>
                <Input id="dateFrom" v-model="filters.dateFrom" type="date" @change="applyFilters" class="w-full" />
              </div>
              <div>
                <Label for="dateTo">Date To</Label>
                <Input id="dateTo" v-model="filters.dateTo" type="date" @change="applyFilters" class="w-full" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
              <div>
                <Label for="rejectionType">Rejection Type</Label>
                <select id="rejectionType" v-model="filters.rejectionType" @change="applyFilters"
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="">All Types</option>
                  <option value="pickup">Pickup</option>
                  <option value="delivery">Delivery</option>
                </select>
              </div>
              <div>
                <Label for="reasonCode">Reason Code</Label>
                <select id="reasonCode" v-model="filters.reasonCode" @change="applyFilters"
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="">All Reason Codes</option>
                  <option v-for="code in rejection_reason_codes" :key="code.id" :value="code.id">
                    {{ code.reason_code }}
                  </option>
                </select>
              </div>
              <div>
                <Label for="penalty">Penalty</Label>
                <Input id="penalty" v-model="filters.penalty" type="text" placeholder="Filter by penalty..."
                  @input="applyFilters" class="w-full" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
              <div>
                <Label for="disputed">Disputed</Label>
                <select id="disputed" v-model="filters.disputed" @change="applyFilters"
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="">All</option>
                  <option value="true">Yes</option>
                  <option value="false">No</option>
                </select>
              </div>
              <div>
                <Label for="driverControllable">Driver Controllable</Label>
                <select id="driverControllable" v-model="filters.driverControllable" @change="applyFilters"
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="">All</option>
                  <option value="true">Yes</option>
                  <option value="false">No</option>
                  <option value="null">N/A</option>
                </select>
              </div>
            </div>

            <div class="flex justify-end">
              <Button @click="resetFilters" variant="ghost" size="sm">
                <Icon name="rotate_ccw" class="mr-2 h-4 w-4" />
                Reset Filters
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Acceptance Dashboard -->
      <AcceptanceDashboard 
        v-if="!isSuperAdmin"
        :metricsData="acceptanceMetrics" 
        :driversData="bottomDrivers" 
        :chartData="acceptanceChartData"
        :averageAcceptance="average_acceptance"
      />

      <!-- Rejections Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table class="relative h-[500px] overflow-auto">
              <TableHeader>
                <TableRow class="sticky top-0 bg-background border-b z-10 hover:bg-background">
                  <!-- Add checkbox column for selecting all -->
                  <TableHead class="w-[50px]">
                    <div class="flex items-center justify-center">
                      <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                    </div>
                  </TableHead>
                  <TableHead v-if="isSuperAdmin">Company Name</TableHead>
                  <TableHead v-for="col in tableColumns" :key="col" class="cursor-pointer" @click="sortBy(col)">
                    <div class="flex items-center">
                      {{col.replace(/_/g, ' ').replace(/^./, str => str.toUpperCase())}}
                      <div v-if="sortColumn === col" class="ml-2">
                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2">
                          <path d="M8 15l4-4 4 4" />
                        </svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M16 9l-4 4-4-4" />
                        </svg>
                      </div>
                      <div v-else class="ml-2 opacity-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M8 10l4-4 4 4" />
                          <path d="M16 14l-4 4-4-4" />
                        </svg>
                      </div>
                    </div>
                  </TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="filteredRejections.length === 0">
                  <TableCell :colspan="isSuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2"
                    class="text-center py-8">
                    No rejections found matching your criteria
                  </TableCell>
                </TableRow>
                <TableRow v-for="rejection in filteredRejections" :key="rejection.id" class="hover:bg-muted/50">
                  <!-- Add checkbox for selecting individual row -->
                  <TableCell class="text-center">
                    <input type="checkbox" :value="rejection.id" v-model="selectedRejections"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                  </TableCell>
                  <TableCell v-if="isSuperAdmin">{{ rejection.tenant?.name || '—' }}</TableCell>
                  <TableCell v-for="col in tableColumns" :key="col" class="whitespace-nowrap">
                    <template v-if="col === 'date'">
                      {{ formatDate(rejection[col]) }}
                    </template>
                    <template v-else-if="col === 'rejection_type'">
                      <span class="capitalize">{{ rejection[col] }}</span>
                    </template>
                    <template v-else-if="col === 'reason_code'">
                      {{ rejection.reason_code?.reason_code || '—' }}
                      <span v-if="rejection.reason_code?.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                    </template>
                    <template v-else-if="col === 'disputed'">
                      {{ rejection[col] ? 'Yes' : 'No' }}
                    </template>
                    <template v-else-if="col === 'driver_controllable'">
                      {{ rejection[col] === null ? 'N/A' : (rejection[col] ? 'Yes' : 'No') }}
                    </template>
                    <template v-else>
                      {{ rejection[col] }}
                    </template>
                  </TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Button size="sm" @click="openForm(rejection)" variant="warning">
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>
                      <Button size="sm" variant="destructive" @click="deleteRejection(rejection.id)">
                        <Icon name="trash" class="mr-1 h-4 w-4" />
                        Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <div class="bg-muted/20 px-4 py-3 border-t" v-if="rejections.links">
            <div class="flex justify-between items-center">
              <div class="text-sm text-muted-foreground flex items-center gap-4">
                <span>Showing {{ filteredRejections.length }} of {{ rejections.data.length }} entries</span>

                <div class="flex items-center gap-2">
                  <span class="text-sm">Show:</span>
                  <select v-model="perPage" @change="changePerPage"
                    class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                    <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">{{ size }}</option>
                  </select>
                </div>
              </div>
              <div class="flex">
                <Button v-for="link in rejections.links" :key="link.label" @click="visitPage(link.url)"
                  :disabled="!link.url" variant="ghost" size="sm" class="mx-1"
                  :class="{ 'bg-primary/10 text-primary border-primary': link.active }">
                  <span v-html="link.label"></span>
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Rejection Form Modal -->
      <Dialog v-model:open="formModal">
        <DialogContent class="sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>{{ selectedRejection ? 'Edit' : 'Add' }} Rejection</DialogTitle>
            <DialogDescription>
              Fill in the details to {{ selectedRejection ? 'update' : 'add' }} a rejection.
            </DialogDescription>
          </DialogHeader>
          <RejectionForm :rejection="selectedRejection" :reasons="rejection_reason_codes" :tenants="tenants"
            :is-super-admin="isSuperAdmin" :tenant-slug="tenantSlug" @close="formModal = false" />
        </DialogContent>
      </Dialog>

      <!-- Code Manager Modal for Reason Codes -->
      <Dialog v-model:open="codeModal" v-if="isSuperAdmin">
        <DialogContent class="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>Manage Reason Codes</DialogTitle>
            <DialogDescription>
              Create and manage reason codes for rejections.
            </DialogDescription>
          </DialogHeader>
          <div class="mt-4 space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">Current Reason Codes</h3>
              <Button @click="openNewCodeForm" size="sm" variant="outline">
                <Icon name="plus" class="mr-2 h-4 w-4" />
                Add New Code
              </Button>
            </div>

            <div class="max-h-[400px] overflow-y-auto">
              <div v-if="!rejection_reason_codes || rejection_reason_codes.length === 0"
                class="text-center py-8 text-muted-foreground border rounded-md">
                No reason codes found
              </div>

              <div v-else class="space-y-2">
                <div v-for="code in rejection_reason_codes" :key="code.id"
                  class="flex items-center justify-between p-3 border rounded-md hover:bg-muted/50 group">
                  <div class="flex-1 cursor-pointer" @click="editCode(code)">
                    <div class="font-medium">
                      {{ code.reason_code }}
                      <span v-if="code.deleted_at" class="ml-2 text-xs text-red-500">(Deleted)</span>
                    </div>
                    <div v-if="code.description" class="text-sm text-muted-foreground mt-1">
                      {{ code.description }}
                    </div>
                  </div>
                  <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                    <template v-if="isSuperAdmin">
                      <template v-if="code.deleted_at">
                        <Button @click="restoreCode(code.id)" size="sm" variant="outline">
                          <Icon name="refresh" class="mr-2 h-4 w-4" />
                          Restore
                        </Button>
                        <Button @click="forceDeleteCode(code.id)" size="sm" variant="destructive">
                          <Icon name="trash" class="mr-2 h-4 w-4" />
                          Permanently Delete
                        </Button>
                      </template>
                      <template v-else>
                        <Button @click="confirmDeleteCode(code.id)" size="sm" variant="destructive">
                          <Icon name="trash" class="mr-2 h-4 w-4" />
                          Delete
                        </Button>
                      </template>
                    </template>
                    <template v-else>
                      <Button @click="confirmDeleteCode(code.id)" size="sm" variant="destructive">
                        <Icon name="trash" class="mr-2 h-4 w-4" />
                        Delete
                      </Button>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="showCodeForm" class="border rounded-md p-4 space-y-4">
            <h3 class="text-sm font-medium">{{ editingCode ? 'Edit' : 'Add' }} Reason Code</h3>
            <div class="space-y-3">
              <div>
                <Label for="reason_code">Code</Label>
                <Input id="reason_code" v-model="codeForm.reason_code" placeholder="Enter reason code" />
              </div>
              <div>
                <Label for="description">Description</Label>
                <Input id="description" v-model="codeForm.description" placeholder="Enter description" />
              </div>
              <div class="flex justify-end space-x-2">
                <Button @click="cancelCodeEdit" variant="ghost" size="sm">Cancel</Button>
                <Button @click="saveCode" variant="default" size="sm">Save</Button>
              </div>
            </div>
          </div>

          <DialogFooter class="mt-6">
            <Button @click="codeModal = false" variant="outline">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Delete Code Confirmation Dialog -->
      <Dialog v-model:open="codeDeleteConfirm">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this reason code? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="mt-4">
            <Button type="button" @click="codeDeleteConfirm = false" variant="outline">
              Cancel
            </Button>
            <Button type="button" @click="deleteCode(codeToDelete)" variant="destructive">
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <!-- Add Delete Selected Confirmation Dialog -->
      <Dialog v-model:open="showDeleteSelectedModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Bulk Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete {{ selectedRejections.length }} rejection records? This action cannot be
              undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="mt-4">
            <Button type="button" @click="showDeleteSelectedModal = false" variant="outline">
              Cancel
            </Button>
            <Button type="button" @click="deleteSelectedRejections()" variant="destructive">
              Delete Selected
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
// Import UI components from their correct folders
import Button from '@/components/ui/button/Button.vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import RejectionForm from '@/components/RejectionForm.vue';
import CodeManager from '@/components/CodeManager.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';
import {
  Card, CardHeader, CardTitle, CardContent,
  Table, TableHeader, TableBody, TableHead, TableRow, TableCell,
  Label, Input,
  Alert, AlertTitle, AlertDescription
} from '@/components/ui';
import AcceptanceDashboard from '@/components/acceptance/AcceptanceDashboard.vue';

const props = defineProps({
  rejections: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenantSlug: { type: String, default: null },
  rejection_reason_codes: Array,
  tenants: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  dateFilter: { type: String, default: 'yesterday' },
  dateRange: { type: Object, default: () => ({ label: 'All Time' }) },
  perPage: { type: Number, default: 10 },
  weekNumber: {
    type: Number,
    default: null
  },
  startWeekNumber: {
    type: Number,
    default: null
  },
  endWeekNumber: {
    type: Number,
    default: null
  },
  year: {
    type: Number,
    default: null
  },
  rejection_breakdown: {
    type: Object,
    default: null
  },
  line_chart_data: {
    type: Object,
    default: null
  },
  average_acceptance:{ 
    type: Number,
  },
})
const weekNumberText = computed(() => {
  // For yesterday and current-week, show single week
  if ((activeTab.value === 'yesterday' || activeTab.value === 'current-week') && props.weekNumber && props.year) {
    return `Week ${props.weekNumber}, ${props.year}`;
  }
  
  // For 6w and quarterly, show start-end week range if available
  if ((activeTab.value === '6w' || activeTab.value === 'quarterly') && 
      props.startWeekNumber && props.endWeekNumber && props.year) {
    return `Weeks ${props.startWeekNumber}-${props.endWeekNumber}, ${props.year}`;
  }
  
  return '';
});

// Set up breadcrumbs
const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard'),
  },
  {
    title: 'Acceptance',
    href: props.tenantSlug
      ? route('acceptance.index', { tenantSlug: props.tenantSlug }) : route('acceptance.index.admin'),
  }
];

// Reactive state for modals and selected rejection
const formModal = ref(false);
const codeModal = ref(false);
const selectedRejection = ref(null);
const errorMessage = ref('');
const successMessage = ref('');
const activeTab = ref(props.dateFilter || 'full');
const perPage = ref(props.perPage);
const selectedRejections = ref([]);
const showDeleteSelectedModal = ref(false);
const exportForm = ref(null);
const showFilters = ref(false); // Add this line to control filter visibility

// Code management state
const showCodeForm = ref(false);
const editingCode = ref(null);
const codeForm = ref({
  reason_code: '',
  description: ''
});
const codeDeleteConfirm = ref(false);
const codeToDelete = ref(null);

// Table columns for sorting and display
const tableColumns = [
  'date',
  'rejection_type',
  'driver_name',
  'penalty',
  'reason_code',
  'disputed',
  'driver_controllable'
];

// Sorting state
const sortColumn = ref('date');
const sortDirection = ref('desc');

// Filtering state
const filters = ref({
  search: '',
  dateFrom: '',
  dateTo: '',
  rejectionType: '',
  reasonCode: '',
  penalty: '',
  disputed: '',
  driverControllable: ''
});

// Computed property for filtered and sorted rejections
const filteredRejections = computed(() => {
  let result = [...props.rejections.data];

  // Apply search filter
  if (filters.value.search) {
    const searchTerm = filters.value.search.toLowerCase();
    result = result.filter(item =>
      item.driver_name?.toLowerCase().includes(searchTerm) ||
      item.rejection_type?.toLowerCase().includes(searchTerm) ||
      item.reason_code?.reason_code?.toLowerCase().includes(searchTerm)
    );
  }

  // Apply date filters
  if (filters.value.dateFrom) {
    result = result.filter(item =>
      item.date && item.date >= filters.value.dateFrom
    );
  }

  if (filters.value.dateTo) {
    result = result.filter(item =>
      item.date && item.date <= filters.value.dateTo
    );
  }

  // Apply rejection type filter
  if (filters.value.rejectionType) {
    result = result.filter(item =>
      item.rejection_type === filters.value.rejectionType
    );
  }

  // Apply reason code filter
  if (filters.value.reasonCode) {
    result = result.filter(item =>
      item.reason_code && item.reason_code.id === parseInt(filters.value.reasonCode)
    );
  }

  // Apply penalty filter
  if (filters.value.penalty) {
    const penaltyTerm = filters.value.penalty.toLowerCase();
    result = result.filter(item =>
      item.penalty && item.penalty.toString().toLowerCase().includes(penaltyTerm)
    );
  }

  // Apply disputed filter
  if (filters.value.disputed !== '') {
    const isDisputed = filters.value.disputed === 'true';
    result = result.filter(item => item.disputed === isDisputed);
  }

  // Apply driver controllable filter
  if (filters.value.driverControllable !== '') {
    if (filters.value.driverControllable === 'null') {
      result = result.filter(item => item.driver_controllable === null);
    } else {
      const isControllable = filters.value.driverControllable === 'true';
      result = result.filter(item => item.driver_controllable === isControllable);
    }
  }

  // Apply sorting
  result.sort((a, b) => {
    let valA = a[sortColumn.value];
    let valB = b[sortColumn.value];

    // Special handling for reason_code which is an object
    if (sortColumn.value === 'reason_code') {
      valA = a.reason_code?.reason_code || '';
      valB = b.reason_code?.reason_code || '';
    }

    // Handle null values
    if (valA === null) return 1;
    if (valB === null) return -1;

    // String comparison
    if (typeof valA === 'string') {
      valA = valA.toLowerCase();
      valB = valB.toLowerCase();
    }

    if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
    if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });

  return result;
});

// Sort function
function sortBy(column) {
  if (sortColumn.value === column) {
    // Toggle direction if clicking the same column
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column and default to ascending
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
}

// Filter functions
function applyFilters() {
  // This function is triggered by input/change events
  // The filtering is handled by the computed property
}

// Function to reset filters - consolidated version
function resetFilters() {
  filters.value = {
    search: '',
    dateFrom: '',
    dateTo: '',
    rejectionType: '',
    reasonCode: '',
    penalty: '',
    disputed: '',
    driverControllable: ''
  };

  // Reset date filter to full
  selectDateFilter('full');
}

// Function to open the rejection form modal (for create or edit)
const openForm = (rejection = null) => {
  selectedRejection.value = rejection;
  formModal.value = true;
};

// Function to open the reason codes manager modal
const openCodeModal = () => {
  codeModal.value = true;
  showCodeForm.value = false;
  editingCode.value = null;
};

// Code management functions
const openNewCodeForm = () => {
  codeForm.value = {
    reason_code: '',
    description: ''
  };
  editingCode.value = null;
  showCodeForm.value = true;
};

const editCode = (code) => {
  codeForm.value = {
    reason_code: code.reason_code,
    description: code.description || ''
  };
  editingCode.value = code.id;
  showCodeForm.value = true;
};

const cancelCodeEdit = () => {
  showCodeForm.value = false;
  editingCode.value = null;
};

const confirmDeleteCode = (id) => {
  codeToDelete.value = id;
  codeDeleteConfirm.value = true;
};

const saveCode = () => {
  const form = useForm({
    reason_code: codeForm.value.reason_code,
    description: codeForm.value.description
  });

  const routeName = editingCode.value
    ? (props.isSuperAdmin ? 'rejection_reason_codes.update.admin' : 'rejection_reason_codes.update')
    : (props.isSuperAdmin ? 'rejection_reason_codes.store.admin' : 'rejection_reason_codes.store');

  const routeParams = editingCode.value
    ? props.isSuperAdmin ? { code: editingCode.value } : { tenantSlug: props.tenantSlug, code: editingCode.value }
    : props.isSuperAdmin ? {} : { tenantSlug: props.tenantSlug };

  const method = editingCode.value ? form.put : form.post;

  method.call(form, route(routeName, routeParams), {
    onSuccess: () => {
      successMessage.value = editingCode.value ? 'Reason code updated successfully.' : 'Reason code created successfully.';
      showCodeForm.value = false;
      editingCode.value = null;
      router.reload({ only: ['rejection_reason_codes'] });
    },
    onError: (errors) => {
      // Handle errors
      console.error(errors);
    }
  });
};

const deleteCode = (id) => {
  const form = useForm({});
  const routeName = props.isSuperAdmin ? 'rejection_reason_codes.destroy.admin' : 'rejection_reason_codes.destroy';
  const routeParams = props.isSuperAdmin ? { id: id } : { tenantSlug: props.tenantSlug, code: id };

  form.delete(route(routeName, routeParams), {
    onSuccess: () => {
      successMessage.value = 'Reason code deleted successfully.';
      codeDeleteConfirm.value = false;
      router.reload({ only: ['rejection_reason_codes'] });
    }
  });
};

// Function to delete a rejection using Inertia form helper
const deleteRejection = (id) => {
  if (!confirm('Are you sure you want to delete this rejection?')) return;

  const form = useForm({});
  const routeName = props.isSuperAdmin ? 'acceptance.destroy.admin' : 'acceptance.destroy';
  const routeParams = props.isSuperAdmin ? { rejection: id } : { tenantSlug: props.tenantSlug, rejection: id };

  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Rejection deleted successfully.';
    }
  });
};

// Function to handle pagination
const visitPage = (url) => {
  if (url) {
    // Add perPage parameter to the URL
    const urlObj = new URL(url);
    urlObj.searchParams.set('perPage', perPage.value);
    router.get(urlObj.href, {}, { preserveScroll: true, preserveState: true, only: ['rejections'] });
  }
};

// Function to handle date filter selection
function selectDateFilter(filter) {
  activeTab.value = filter;

  const routeName = props.tenantSlug
    ? route('acceptance.index', { tenantSlug: props.tenantSlug })
    : route('acceptance.index.admin');

  router.get(routeName, {
    dateFilter: filter,
    perPage: perPage.value
  }, { preserveState: true });
}

// Function to handle per page change
function changePerPage() {
  const routeName = props.tenantSlug
    ? route('acceptance.index', { tenantSlug: props.tenantSlug })
    : route('acceptance.index.admin');

  router.get(routeName, {
    dateFilter: activeTab.value,
    perPage: perPage.value
  }, { preserveState: true });
}

// Remove this duplicate function declaration
// function visitPage(url) { ... }

// Format date string from YYYY-MM-DD to m/d/Y
function formatDate(dateStr) {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  }
});

// Acceptance dashboard data from API
const acceptanceMetrics = computed(() => {
  if (!props.rejection_breakdown?.by_category) return null;
  
  const categoryData = props.rejection_breakdown.by_category;
  
  return {
    category_more_than_6_load_count: categoryData.category_more_than_6_load_count || '0',
    category_more_than_6_block_count: categoryData.category_more_than_6_block_count || '0',
    category_within_6_load_count: categoryData.category_within_6_load_count || '0',
    category_within_6_block_count: categoryData.category_within_6_block_count || '0',
    category_after_start_load_count: categoryData.category_after_start_load_count || '0',
    category_after_start_block_count: categoryData.category_after_start_block_count || '0',
    total_load_rejections: categoryData.total_load_rejections || '0',
    total_block_rejections: categoryData.total_block_rejections || '0',
    total_load_penalty: categoryData.total_load_penalty || '0',
    total_block_penalty: categoryData.total_block_penalty || '0',
    by_category: true
  };
});

const bottomDrivers = computed(() => {
  return props.rejection_breakdown?.bottom_five_drivers || [];
});

const acceptanceChartData = computed(() => {
  if (!props.line_chart_data || props.line_chart_data.length === 0) {
    return {
      labels: [],
      datasets: [{
        label: 'Acceptance Performance',
        data: [],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.3,
      }]
    };
  }
  
  return {
    labels: props.line_chart_data.map(item => item.date),
    datasets: [{
      label: 'Acceptance Performance',
      data: props.line_chart_data.map(item => item.acceptancePerformance),
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.3,
    }]
  };
});

// Add these new methods to handle soft-deleted reason codes
function restoreCode(id) {
  router.post(route('rejection_reason_codes.restore.admin', { id }), {}, {
    onSuccess: () => {
      successMessage.value = 'Reason code restored successfully';
      setTimeout(() => successMessage.value = '', 3000);
    }
  });
}

function forceDeleteCode(id) {
  if (confirm('Are you sure you want to permanently delete this reason code? This action cannot be undone.')) {
    router.delete(route('rejection_reason_codes.forceDelete.admin', { id }), {
      onSuccess: () => {
        successMessage.value = 'Reason code permanently deleted';
        setTimeout(() => successMessage.value = '', 3000);
      }
    });
  }
}

// Computed property for active reason codes (for the form dropdown)
const activeReasonCodes = computed(() => {
  return props.rejection_reason_codes.filter(code => !code.deleted_at);
});

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
  return filteredRejections.value.length > 0 && selectedRejections.value.length === filteredRejections.value.length;
});

// Bulk selection functions
function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedRejections.value = filteredRejections.value.map(rejection => rejection.id);
  } else {
    selectedRejections.value = [];
  }
}

function confirmDeleteSelected() {
  if (selectedRejections.value.length > 0) {
    showDeleteSelectedModal.value = true;
  }
}

function deleteSelectedRejections() {
  const form = useForm({
    ids: selectedRejections.value
  });

  const routeName = props.isSuperAdmin ? 'acceptance.destroyBulk.admin' : 'acceptance.destroyBulk';
  const routeParams = props.isSuperAdmin ? {} : { tenantSlug: props.tenantSlug };

  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = `${selectedRejections.value.length} rejection records deleted successfully.`;
      selectedRejections.value = [];
      showDeleteSelectedModal.value = false;
    },
    onError: (errors) => {
      console.error(errors);
    }
  });
}

// Add these functions for import/export
function handleImport(event) {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('csv_file', file);

  const routeName = props.isSuperAdmin
    ? route('acceptance.import.admin')
    : route('acceptance.import', { tenantSlug: props.tenantSlug });

  router.post(routeName, formData, {
    onSuccess: () => {
      successMessage.value = 'Delays imported successfully';
      event.target.value = ''; // Reset the file input
    },
    onError: (errors) => {
      console.error(errors);
      event.target.value = ''; // Reset the file input
    }
  });
}

function exportCSV() {
  if (filteredRejections.value.length===0) {
    errorMessage.value = "No data available to export";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
    return;
  }
  // Submit the hidden form to trigger the download
  if (exportForm.value) {
    exportForm.value.submit();
  }
}
// Computed property for export URL
const exportUrl = computed(() => {
  return props.tenantSlug
    ? route('acceptance.export', { tenantSlug: props.tenantSlug })
    : route('acceptance.export.admin');
});


// Add these new refs and computed properties
const showUploadOptions = ref(false);

// Computed property for template URL
const templateUrl = computed(() => {
  return '/storage/upload-data-temps/Rejections Template.csv';
});

// Close dropdown when clicking outside
onMounted(() => {
  const handleClickOutside = (e) => {
    if (showUploadOptions.value && !e.target.closest('.relative')) {
      showUploadOptions.value = false;
    }
  };
  
  document.addEventListener('click', handleClickOutside);
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
  });
});
// Add these to your script section
const hasActiveFilters = computed(() => {
  return filters.value.search || 
         filters.value.dateFrom || 
         filters.value.dateTo || 
         filters.value.rejectionType || 
         filters.value.reasonCode || 
         filters.value.penalty || 
         filters.value.disputed || 
         filters.value.driverControllable;
});

// Helper method to get the reason code text
function getReasonCodeLabel(codeId) {
  if (!codeId) return '';
  const code = props.rejection_reason_codes.find(c => c.id == codeId);
  return code ? code.reason_code : '';
}
</script>
