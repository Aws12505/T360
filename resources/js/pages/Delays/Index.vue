<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head title="On-Time" />

    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Success Message -->
      <Alert v-if="successMessage" variant="success">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>

      <!-- Actions Section -->
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">On-Time Management</h1>
        <div class="flex flex-wrap gap-3">
          <Button @click="openForm()" variant="default">
            <Icon name="plus" class="mr-2 h-4 w-4" />
            Add Delay
          </Button>
          <Button 
            v-if="selectedDelays.length > 0" 
            @click="confirmDeleteSelected()" 
            variant="destructive"
          >
            <Icon name="trash" class="mr-2 h-4 w-4" />
            Delete Selected ({{ selectedDelays.length }})
          </Button>
          <label class="cursor-pointer">
            <Button variant="secondary" as="span">
              <Icon name="upload" class="mr-2 h-4 w-4" />
              Import CSV
            </Button>
            <input type="file" class="hidden" @change="handleImport" accept=".csv" />
          </label>
          <Button @click.prevent="exportCSV" variant="outline">
            <Icon name="download" class="mr-2 h-4 w-4" />
            Export CSV
          </Button>
          <Button v-if="isSuperAdmin" @click="openCodeModal()" variant="outline">
            <Icon name="settings" class="mr-2 h-4 w-4" />
            Manage Delay Codes
          </Button>
        </div>
      </div>
      
      <!-- Hidden Export Form -->
      <form ref="exportForm" :action="exportUrl" method="GET" class="hidden"></form>
      
      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-4">
          <div class="flex flex-col gap-2">
            <div class="flex flex-wrap gap-2">
              <Button
                @click="selectDateFilter('yesterday')"
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'yesterday'}">
                Yesterday
              </Button>
              <Button
                @click="selectDateFilter('current-week')"
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'current-week'}">
                Current Week
              </Button>
              <Button
                @click="selectDateFilter('6w')"
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === '6w'}">
                6 Weeks
              </Button>
              <Button
                @click="selectDateFilter('quarterly')"
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'quarterly'}">
                Quarterly
              </Button>
              <Button
                @click="selectDateFilter('full')"
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'full'}">
                Full
              </Button>
            </div>
            <div v-if="dateRange" class="text-sm text-muted-foreground">
              <span v-if="dateRange.start && dateRange.end">
                Showing data from {{ formatDate(dateRange.start) }} to {{ formatDate(dateRange.end) }}
              </span>
              <span v-else>
                {{ dateRange.label }}
              </span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Filters Section -->
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-col justify-between gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 w-full">
              <div>
                <Label for="search">Search</Label>
                <Input id="search" v-model="filters.search" type="text" placeholder="Search by driver or type..." @input="applyFilters" />
              </div>
              <div>
                <Label for="dateFrom">Date From</Label>
                <Input id="dateFrom" v-model="filters.dateFrom" type="date" @change="applyFilters" />
              </div>
              <div>
                <Label for="dateTo">Date To</Label>
                <Input id="dateTo" v-model="filters.dateTo" type="date" @change="applyFilters" />
              </div>
              <div>
                <Label for="delayCode">Delay Code</Label>
                <select
                  id="delayCode"
                  v-model="filters.delayCode"
                  @change="applyFilters"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                  <option value="">All Codes</option>
                  <option v-for="code in delay_codes" :key="code.id" :value="code.id">
                    {{ code.code }}
                  </option>
                </select>
              </div>
              <div>
                <Label for="disputed">Disputed Status</Label>
                <select
                  id="disputed"
                  v-model="filters.disputed"
                  @change="applyFilters"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                  <option value="">All</option>
                  <option value="true">Disputed</option>
                  <option value="false">Not Disputed</option>
                </select>
              </div>
              <div>
                <Label for="controllable">Driver Controllable</Label>
                <select
                  id="controllable"
                  v-model="filters.controllable"
                  @change="applyFilters"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                  <option value="">All</option>
                  <option value="true">Yes</option>
                  <option value="false">No</option>
                  <option value="null">N/A</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end">
              <Button @click="resetFilters" variant="ghost" size="sm">
                <Icon name="rotate-ccw" class="mr-2 h-4 w-4" />
                Reset Filters
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Delays Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table class="relative h-[500px] overflow-auto">
              <TableHeader>
                <TableRow class="sticky top-0 bg-background border-b z-10">
                  <TableHead class="w-[50px]">
                    <div class="flex items-center justify-center">
                      <input 
                        type="checkbox" 
                        @change="toggleSelectAll" 
                        :checked="isAllSelected"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                      />
                    </div>
                  </TableHead>
                  <TableHead v-if="isSuperAdmin">Company Name</TableHead>
                  <TableHead
                    v-for="col in tableColumns"
                    :key="col"
                    class="cursor-pointer"
                    @click="sortBy(col)"
                  >
                    <div class="flex items-center">
                      {{ col.replace(/_/g, ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') }}
                      <div v-if="sortColumn === col" class="ml-2">
                        <svg v-if="sortDirection === 'asc'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M8 15l4-4 4 4" />
                        </svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                <TableRow v-if="filteredDelays.length === 0">
                  <TableCell :colspan="isSuperAdmin ? tableColumns.length + 2 : tableColumns.length + 1" class="text-center py-8">
                    No delays found matching your criteria
                  </TableCell>
                </TableRow>
                <TableRow v-for="delay in filteredDelays" :key="delay.id" class="hover:bg-muted/50">
                  <TableCell class="text-center">
                    <input 
                      type="checkbox" 
                      :value="delay.id" 
                      v-model="selectedDelays"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                  </TableCell>
                  <TableCell v-if="isSuperAdmin">{{ delay.tenant?.name || '—' }}</TableCell>
                  <TableCell v-for="col in tableColumns" :key="col" class="whitespace-nowrap">
                    <template v-if="col === 'date'">
                      {{ formatDate(delay[col]) }}
                    </template>
                    <template v-else-if="col === 'disputed'">
                      {{ delay[col] ? 'Yes' : 'No' }}
                    </template>
                    <template v-else-if="col === 'driver_controllable'">
                      {{ delay[col] === null ? 'N/A' : (delay[col] ? 'Yes' : 'No') }}
                    </template>
                    <template v-else-if="col === 'delay_code'">
                      {{ delay.delay_code?.code || '—' }}
                      <span v-if="delay.delay_code?.deleted_at" class="ml-1 text-xs text-red-500">(Deleted Code)</span>
                    </template>
                    <template v-else>
                      {{ delay[col] }}
                    </template>
                  </TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Button @click="openForm(delay)" variant="warning" size="sm">
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>
                      <Button @click="confirmDelete(delay.id)" variant="destructive" size="sm">
                        <Icon name="trash" class="mr-1 h-4 w-4" />
                        Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          <div class="bg-muted/20 px-4 py-3 border-t" v-if="delays.links">
            <div class="flex justify-between items-center">
              <div class="text-sm text-muted-foreground">
                Showing {{ filteredDelays.length }} of {{ delays.data.length }} entries
              </div>
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                  <Label for="perPage" class="text-sm">Per page:</Label>
                  <select 
                    id="perPage" 
                    v-model="perPage" 
                    @change="changePerPage"
                    class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                </div>
                <div class="flex">
                  <Button
                    v-for="link in delays.links"
                    :key="link.label"
                    @click="visitPage(link.url)"
                    :disabled="!link.url"
                    variant="ghost"
                    size="sm"
                    class="mx-1"
                    :class="{'bg-primary/10 text-primary border-primary': link.active}"
                  >
                    <span v-html="link.label"></span>
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Delay Form Modal (Pass only active delay codes) -->
      <Dialog v-model:open="formModal">
        <DialogContent class="sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>{{ selectedDelay ? 'Edit' : 'Add' }} Delay</DialogTitle>
            <DialogDescription>
              Fill in the details to {{ selectedDelay ? 'update' : 'create' }} a delay record.
            </DialogDescription>
          </DialogHeader>
          <DelayForm
            :delay="selectedDelay"
            :delay-codes="activeDelayCodes"
            :tenants="tenants"
            :is-super-admin="isSuperAdmin"
            :tenant-slug="tenantSlug"
            @close="formModal = false"
          />
        </DialogContent>
      </Dialog>

      <!-- Code Manager Modal for Delay Codes -->
      <Dialog v-model:open="codeModal">
        <DialogContent class="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>Manage Delay Codes</DialogTitle>
            <DialogDescription>
              Create and manage delay codes for your organization.
            </DialogDescription>
          </DialogHeader>
          <div class="mt-4 space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">Current Delay Codes</h3>
              <Button @click="openNewCodeForm" size="sm" variant="outline">
                <Icon name="plus" class="mr-2 h-4 w-4" />
                Add New Code
              </Button>
            </div>
            <div class="max-h-[400px] overflow-y-auto">
              <div v-if="!delay_codes || delay_codes.length === 0" class="text-center py-8 text-muted-foreground border rounded-md">
                No delay codes found
              </div>
              <div v-else class="space-y-2">
                <div
                  v-for="code in delay_codes"
                  :key="code.id"
                  class="flex items-center justify-between p-3 border rounded-md hover:bg-muted/50 group"
                >
                  <div class="flex-1 cursor-pointer" @click="editCode(code)">
                    <div class="font-medium">
                      {{ code.code }}
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
            <div v-if="showCodeForm" class="border rounded-md p-4 space-y-4">
              <h3 class="text-sm font-medium">{{ editingCode ? 'Edit' : 'Add' }} Delay Code</h3>
              <div class="space-y-3">
                <div>
                  <Label for="code">Code</Label>
                  <Input id="code" v-model="codeForm.code" placeholder="Enter code" />
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
              Are you sure you want to delete this delay code? This action cannot be undone.
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

      <!-- Delete Delay Confirmation Dialog -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this delay record? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="mt-4">
            <Button type="button" @click="showDeleteModal = false" variant="outline">
              Cancel
            </Button>
            <Button type="button" @click="deleteDelay(delayToDelete)" variant="destructive">
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <!-- Delete Selected Delays Confirmation Dialog -->
<Dialog v-model:open="showDeleteSelectedModal">
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Confirm Bulk Deletion</DialogTitle>
      <DialogDescription>
        Are you sure you want to delete {{ selectedDelays.length }} delay records? This action cannot be undone.
      </DialogDescription>
    </DialogHeader>
    <DialogFooter class="mt-4">
      <Button type="button" @click="showDeleteSelectedModal = false" variant="outline">
        Cancel
      </Button>
      <Button type="button" @click="deleteSelectedDelays()" variant="destructive">
        Delete Selected
      </Button>
    </DialogFooter>
  </DialogContent>
</Dialog>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import DelayForm from '@/components/DelayForm.vue';
import Icon from '@/components/Icon.vue';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Table,
  TableHeader,
  TableBody,
  TableHead,
  TableRow,
  TableCell,
  Label,
  Input,
  Alert,
  AlertTitle,
  AlertDescription,
} from '@/components/ui';

const props = defineProps({
  delays: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenantSlug: { type: String, default: null },
  delay_codes: Array,
  tenants: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  dateRange: { type: Object, default: null },
  dateFilter: { type: String, default: 'full' },
});

const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'),
  },
  {
    title: 'On-Time',
    href: props.tenantSlug ? route('ontime.index', { tenantSlug: props.tenantSlug }) : route('ontime.index.admin'),
  },
];

// UI state
const formModal = ref(false);
const codeModal = ref(false);
const selectedDelay = ref(null);
const successMessage = ref('');
const showDeleteModal = ref(false);
const delayToDelete = ref(null);
const selectedDelays = ref([]);
const showDeleteSelectedModal = ref(false);
const exportForm = ref(null); // Add this line to define the exportForm ref

// Code management state
const showCodeForm = ref(false);
const editingCode = ref(null);
const codeForm = ref({ code: '', description: '' });
const codeDeleteConfirm = ref(false);
const codeToDelete = ref(null);

// Sorting state
const sortColumn = ref('date');
const sortDirection = ref('desc');

// Filtering state
const filters = ref({
  search: '',
  dateFrom: '',
  dateTo: '',
  delayCode: '',
  disputed: '',
  controllable: '',
});

// Table columns
const tableColumns = ['date', 'delay_type', 'driver_name', 'penalty', 'delay_code', 'disputed', 'driver_controllable'];

// Computed property: Filtered and sorted delays
const filteredDelays = computed(() => {
  let result = [...props.delays.data];

  // Search filter
  if (filters.value.search) {
    const term = filters.value.search.toLowerCase();
    result = result.filter(item =>
      item.driver_name?.toLowerCase().includes(term) ||
      item.delay_type?.toLowerCase().includes(term) ||
      item.delay_code?.code?.toLowerCase().includes(term)
    );
  }

  // Date filters
  if (filters.value.dateFrom) {
    result = result.filter(item => item.date && item.date >= filters.value.dateFrom);
  }
  if (filters.value.dateTo) {
    result = result.filter(item => item.date && item.date <= filters.value.dateTo);
  }

  // Delay code filter
  if (filters.value.delayCode) {
    result = result.filter(item => item.delay_code?.id === parseInt(filters.value.delayCode));
  }

  // Disputed filter
  if (filters.value.disputed !== '') {
    const isDisputed = filters.value.disputed === 'true';
    result = result.filter(item => item.disputed === isDisputed);
  }

  // Controllable filter
  if (filters.value.controllable !== '') {
    if (filters.value.controllable === 'null') {
      result = result.filter(item => item.driver_controllable === null);
    } else {
      const isControllable = filters.value.controllable === 'true';
      result = result.filter(item => item.driver_controllable === isControllable);
    }
  }

  // Sorting logic
  result.sort((a, b) => {
    let aVal, bVal;
    if (sortColumn.value === 'delay_code') {
      aVal = a.delay_code?.code || '';
      bVal = b.delay_code?.code || '';
    } else {
      aVal = a[sortColumn.value];
      bVal = b[sortColumn.value];
    }
    if (aVal === null) return 1;
    if (bVal === null) return -1;
    if (typeof aVal === 'string') {
      aVal = aVal.toLowerCase();
      bVal = bVal.toLowerCase();
    }
    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
  return result;
});

// Computed property: Only active (non-trashed) delay codes for the DelayForm dropdown
const activeDelayCodes = computed(() => {
  return props.delay_codes.filter(code => !code.deleted_at);
});

// Sorting handler
function sortBy(column) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
}

// Filter handlers
function applyFilters() {
  // Automatic via computed property
}
function resetFilters() {
  filters.value = {
    search: '',
    dateFrom: '',
    dateTo: '',
    delayCode: '',
    disputed: '',
    controllable: '',
  };
}

// Pagination & Date Filter Functions
const perPage = ref(props.delays.per_page || 10);
const activeTab = ref(props.dateFilter || 'full');

function changePerPage() {
  const routeName = props.tenantSlug ? route('ontime.index', { tenantSlug: props.tenantSlug }) : route('ontime.index.admin');
  router.get(routeName, { dateFilter: activeTab.value, perPage: perPage.value }, { preserveState: true });
}
function selectDateFilter(filter) {
  activeTab.value = filter;
  const routeName = props.tenantSlug ? route('ontime.index', { tenantSlug: props.tenantSlug }) : route('ontime.index.admin');
  router.get(routeName, { dateFilter: filter, perPage: perPage.value }, { preserveState: true });
}
function visitPage(url) {
  if (url) {
    // Add perPage parameter to the URL
    const urlObj = new URL(url);
    urlObj.searchParams.set('perPage', perPage.value);
    router.get(urlObj.href, {}, { preserveScroll: true, preserveState: true, only: ['delays'] });
  }
}
function formatDate(dateStr) {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}

// Delay Form Functions
const openForm = (delay = null) => {
  selectedDelay.value = delay;
  formModal.value = true;
};

// Add the missing confirmDelete function
const confirmDelete = (id) => {
  delayToDelete.value = id;
  showDeleteModal.value = true;
};

// Code Management Functions
const openCodeModal = () => {
  codeModal.value = true;
  showCodeForm.value = false;
  editingCode.value = null;
};
const openNewCodeForm = () => {
  codeForm.value = { code: '', description: '' };
  editingCode.value = null;
  showCodeForm.value = true;
};
const editCode = (code) => {
  codeForm.value = { code: code.code, description: code.description || '' };
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

// Add the missing deleteCode function
const deleteCode = (id) => {
  const form = useForm({});
  const routeName = props.isSuperAdmin ? 'delay_codes.destroy.admin' : 'delay_codes.destroy';
  const routeParams = props.isSuperAdmin ? { id: id } : { tenantSlug: props.tenantSlug, delay_code: id };
  
  form.delete(route(routeName, routeParams), {
    onSuccess: () => {
      successMessage.value = 'Delay code deleted successfully.';
      codeDeleteConfirm.value = false;
      router.reload({ only: ['delay_codes'] });
    },
    onError: (errors) => {
      console.error(errors);
    }
  });
};

const saveCode = () => {
  const form = useForm({ code: codeForm.value.code, description: codeForm.value.description });
  const routeName = editingCode.value
    ? (props.isSuperAdmin ? 'delay_codes.update.admin' : 'delay_codes.update')
    : (props.isSuperAdmin ? 'delay_codes.store.admin' : 'delay_codes.store');
  const routeParams = editingCode.value
    ? (props.isSuperAdmin ? { id: editingCode.value } : { tenantSlug: props.tenantSlug, id: editingCode.value })
    : (props.isSuperAdmin ? {} : { tenantSlug: props.tenantSlug });
  const method = editingCode.value ? form.put : form.post;
  method.call(form, route(routeName, routeParams), {
    onSuccess: () => {
      successMessage.value = editingCode.value ? 'Delay code updated successfully.' : 'Delay code created successfully.';
      showCodeForm.value = false;
      editingCode.value = null;
      router.reload({ only: ['delay_codes'] });
    },
    onError: (errors) => {
      console.error(errors);
    },
  });
};
const restoreCode = (id) => {
  const form = useForm({});
  form.post(route(props.isSuperAdmin ? 'delay_codes.restore.admin' : 'delay_codes.restore', { id }), {
    onSuccess: () => {
      successMessage.value = 'Delay code restored successfully.';
      router.reload({ only: ['delay_codes'] });
    },
    onError: (errors) => {
      console.error(errors);
    },
  });
};
const forceDeleteCode = (id) => {
  const form = useForm({});
  form.delete(route(props.isSuperAdmin ? 'delay_codes.forceDelete.admin' : 'delay_codes.forceDelete', { id }), {
    onSuccess: () => {
      successMessage.value = 'Delay code permanently deleted successfully.';
      router.reload({ only: ['delay_codes'] });
    },
    onError: (errors) => {
      console.error(errors);
    },
  });
};
const deleteDelay = (id) => {
  const form = useForm({});
  const routeName = props.isSuperAdmin ? 'ontime.destroy.admin' : 'ontime.destroy';
  const routeParams = props.isSuperAdmin ? { delay: id } : { tenantSlug: props.tenantSlug, delay: id };
  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Delay record deleted successfully.';
      showDeleteModal.value = false;
    },
  });
};

// Auto-hide success message
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = '';
    }, 5000);
  }
});

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
  return filteredDelays.value.length > 0 && selectedDelays.value.length === filteredDelays.value.length;
});

// Bulk selection functions
function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedDelays.value = filteredDelays.value.map(delay => delay.id);
  } else {
    selectedDelays.value = [];
  }
}

function confirmDeleteSelected() {
  if (selectedDelays.value.length > 0) {
    showDeleteSelectedModal.value = true;
  }
}

function deleteSelectedDelays() {
  const form = useForm({
    ids: selectedDelays.value
  });
  
  const routeName = props.isSuperAdmin ? 'ontime.destroyBulk.admin' : 'ontime.destroyBulk';
  const routeParams = props.isSuperAdmin ? {} : { tenantSlug: props.tenantSlug };
  
  form.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = `${selectedDelays.value.length} delay records deleted successfully.`;
      selectedDelays.value = [];
      showDeleteSelectedModal.value = false;
      // Reload the page to refresh the data
      router.reload();
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
    ? route('ontime.import.admin') 
    : route('ontime.import', { tenantSlug: props.tenantSlug });
  
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
  // Submit the hidden form to trigger the download
  if (exportForm.value) {
    exportForm.value.submit();
  }
}
// Computed property for export URL
const exportUrl = computed(() => {
  return props.tenantSlug 
    ? route('ontime.export', { tenantSlug: props.tenantSlug }) 
    : route('ontime.export.admin');
});
</script>




