<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
        <Head title="Performance" />
        <!-- responsive here -->
        <div class="mx-auto max-w-7xl space-y-8 p-6">
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
            <!-- responsive here -->
            <div class="mb-6 flex flex-col items-center justify-between gap-4 sm:flex-row">
                <!-- responsive here -->
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Performance Management</h1>
                <div class="flex flex-wrap gap-3">
                    <!-- responsive here -->
                    <Button @click="openCreateModal" variant="default">
                        <!-- responsive here -->
                        <Icon name="plus" class="mr-2 h-4 w-4" />
                        Create New Performance
                    </Button>

                    <!-- Add Delete Selected button -->
                    <!-- responsive here -->
                    <Button v-if="selectedPerformances.length > 0" @click="confirmDeleteSelected()" variant="destructive">
                        <!-- responsive here -->
                        <Icon name="trash" class="mr-2 h-4 w-4" />
                        Delete Selected ({{ selectedPerformances.length }})
                    </Button>

                    <div class="relative">
                        <!-- responsive here -->
                        <Button @click="showUploadOptions = !showUploadOptions" variant="secondary">
                            <!-- responsive here -->
                            <Icon name="upload" class="mr-2 h-4 w-4" />
                            Upload CSV
                            <Icon name="chevron-down" class="ml-2 h-4 w-4" />
                        </Button>
                        <div v-if="showUploadOptions" class="absolute right-0 z-10 mt-1 w-48 rounded-md border bg-background shadow-lg">
                            <div class="py-1">
                                <label class="block cursor-pointer px-4 py-2 text-sm hover:bg-muted">
                                    <span>Upload CSV File</span>
                                    <input type="file" class="hidden" @change="handleImport" accept=".csv" />
                                </label>
                                <a :href="templateUrl" download="Performances Template.csv" class="block px-4 py-2 text-sm hover:bg-muted">
                                    Download Template
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- responsive here -->
                    <Button @click.prevent="exportCSV" variant="outline">
                        <!-- responsive here -->
                        <Icon name="download" class="mr-2 h-4 w-4" />
                        Download CSV
                    </Button>
                </div>
            </div>

            <!-- Date Filter Tabs -->
            <Card>
                <!-- responsive here -->
                <CardContent class="p-4">
                    <!-- responsive here -->
                    <div class="flex flex-col gap-2">
                        <!-- responsive here -->
                        <div class="flex flex-wrap gap-2">
                            <Button
                                @click="selectDateFilter('yesterday')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === 'yesterday' }"
                            >
                                Yesterday
                            </Button>
                            <Button
                                @click="selectDateFilter('current-week')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === 'current-week' }"
                            >
                                WTD
                            </Button>
                            <Button
                                @click="selectDateFilter('6w')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === '6w' }"
                            >
                                T6W
                            </Button>
                            <Button
                                @click="selectDateFilter('quarterly')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === 'quarterly' }"
                            >
                                Quarterly
                            </Button>
                        </div>
                        <div v-if="dateRange" class="text-sm text-muted-foreground">
                            <span v-if="activeTab === 'yesterday' && dateRange.start"> Showing data from {{ formatDate(dateRange.start) }} </span>
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

            <!-- Filters Section - REMOVED -->
            <!-- Replaced with Date Filter Tabs above -->

            <!-- Performance Table -->
            <!-- responsive here -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table class="relative h-[600px] overflow-auto">
                            <TableHeader>
                                <TableRow class="sticky top-0 z-10 border-b bg-background hover:bg-background">
                                    <!-- Add checkbox column for selecting all -->
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
                                    <TableHead v-if="SuperAdmin">Company Name</TableHead>
                                    <TableHead v-for="col in tableColumns" :key="col" class="cursor-pointer" @click="sortBy(col)">
                                        <div class="flex items-center">
                                            <!-- Display abbreviated or custom names with title attribute for hover tooltip -->
                                            <span :title="getFullColumnName(col)">
                                                {{ getDisplayColumnName(col) }}
                                            </span>
                                            <div v-if="sortColumn === col" class="ml-2">
                                                <svg
                                                    v-if="sortDirection === 'asc'"
                                                    class="h-4 w-4"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
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
                                <TableRow v-if="filteredPerformances.length === 0">
                                    <TableCell :colspan="SuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2" class="py-8 text-center">
                                        No performance records found matching your criteria
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="item in filteredPerformances" :key="item.id" class="hover:bg-muted/50">
                                    <!-- Add checkbox for selecting individual row -->
                                    <TableCell class="text-center">
                                        <input
                                            type="checkbox"
                                            :value="item.id"
                                            v-model="selectedPerformances"
                                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                    </TableCell>
                                    <TableCell v-if="SuperAdmin">
                                        {{ item.tenant?.name ?? '—' }}
                                    </TableCell>
                                    <TableCell>{{ formatDate(item.date) }}</TableCell>
                                    <TableCell>
                                        <div>%{{ item.acceptance }}</div>
                                        <div class="text-xs italic text-gray-500">({{ formatRating(item.acceptance_rating) }})</div>
                                    </TableCell>
                                    <TableCell>%{{ item.on_time_to_origin }}</TableCell>
                                    <TableCell>%{{ item.on_time_to_destination }}</TableCell>
                                    <TableCell>
                                        <div>%{{ item.on_time }}</div>
                                        <div class="text-xs italic text-gray-500">({{ formatRating(item.on_time_rating) }})</div>
                                    </TableCell>
                                    <TableCell>
                                        <div>%{{ item.maintenance_variance_to_spend }}</div>
                                        <div class="text-xs italic text-gray-500">
                                            ({{ formatRating(item.maintenance_variance_to_spend_rating) }})
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div>{{ item.open_boc }}</div>
                                        <div class="text-xs italic text-gray-500">({{ formatRating(item.open_boc_rating) }})</div>
                                    </TableCell>
                                    <TableCell>
                                        <div>{{ item.meets_safety_bonus_criteria ? 'Yes' : 'No' }}</div>
                                        <div class="text-xs italic text-gray-500">({{ formatRating(item.meets_safety_bonus_criteria_rating) }})</div>
                                    </TableCell>
                                    <TableCell>
                                        <div>{{ item.vcr_preventable }}</div>
                                        <div class="text-xs italic text-gray-500">({{ formatRating(item.vcr_preventable_rating) }})</div>
                                    </TableCell>
                                    <TableCell>
                                        <div>{{ item.vmcr_p }}</div>
                                        <div class="text-xs italic text-gray-500">({{ formatRating(item.vmcr_p_rating) }})</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex space-x-2">
                                            <Button @click="openEditModal(item)" variant="warning" size="sm">
                                                <Icon name="pencil" class="mr-1 h-4 w-4" />
                                                Edit
                                            </Button>
                                            <Button @click="deletePerformance(item.id)" variant="destructive" size="sm">
                                                <Icon name="trash" class="mr-1 h-4 w-4" />
                                                Delete
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <!-- paginate -->
                    <div class="border-t bg-muted/20 px-4 py-3" v-if="performances.links">
                        <!-- responsive here -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                <span>Showing {{ filteredPerformances.length }} of {{ performances.data.length }} entries</span>
                                <!-- responsive here -->
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">Show:</span>
                                    <select
                                        v-model="perPage"
                                        @change="changePerPage"
                                        class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                    >
                                        <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">{{ size }}</option>
                                    </select>
                                </div>
                            </div>
                            <!-- responsive here -->
                            <div class="flex">
                                <Button
                                    v-for="link in performances.links"
                                    :key="link.label"
                                    @click="visitPage(link.url)"
                                    :disabled="!link.url"
                                    variant="ghost"
                                    size="sm"
                                    class="mx-1"
                                    :class="{ 'border-primary bg-primary/10 text-primary': link.active }"
                                >
                                    <span v-html="link.label"></span>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Modal for Create/Edit Performance -->
            <Dialog v-model:open="showModal">
                <DialogContent class="w-[90vw] sm:max-w-lg md:max-w-2xl lg:max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>{{ formTitle }}</DialogTitle>
                        <DialogDescription> Fill in the details to {{ formAction.toLowerCase() }} a performance record. </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Tenant Dropdown for SuperAdmin -->
                        <div v-if="SuperAdmin">
                            <Label for="tenant">Company Name</Label>
                            <div class="relative">
                                <select
                                    id="tenant"
                                    v-model="form.tenant_id"
                                    required
                                    class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option disabled value="">Select a Company</option>
                                    <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                        {{ tenant.name }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Form Fields in Grid Layout for larger screens -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Date Field -->
                            <div>
                                <Label for="date">Date</Label>
                                <Input id="date" v-model="form.date" type="date" required />
                            </div>
                            <!-- Acceptance Field -->
                            <div>
                                <Label for="acceptance">Acceptance</Label>
                                <Input id="acceptance" v-model="form.acceptance" type="number" step="0.01" required />
                            </div>
                            <!-- On Time to Origin Field -->
                            <div>
                                <Label for="on_time_to_origin">On Time to Origin</Label>
                                <Input id="on_time_to_origin" v-model="form.on_time_to_origin" type="number" step="0.01" required />
                            </div>
                            <!-- On Time to Destination Field -->
                            <div>
                                <Label for="on_time_to_destination">On Time to Destination</Label>
                                <Input id="on_time_to_destination" v-model="form.on_time_to_destination" type="number" step="0.01" required />
                            </div>
                            <!-- Maintenance Variance to Spend Field -->
                            <div>
                                <Label for="maintenance_variance_to_spend">Maintenance Variance to Spend</Label>
                                <Input
                                    id="maintenance_variance_to_spend"
                                    v-model="form.maintenance_variance_to_spend"
                                    type="number"
                                    step="0.001"
                                    required
                                />
                            </div>
                            <!-- Open BOC Field -->
                            <div>
                                <Label for="open_boc">Open BOC</Label>
                                <Input id="open_boc" v-model="form.open_boc" type="number" step="1" required />
                            </div>
                            <!-- VCR Preventable Field -->
                            <div>
                                <Label for="vcr_preventable">VCR Preventable</Label>
                                <Input id="vcr_preventable" v-model="form.vcr_preventable" type="number" step="1" required />
                            </div>
                            <!-- VMCR P Field -->
                            <div>
                                <Label for="vmcr_p">VMCR P</Label>
                                <Input id="vmcr_p" v-model="form.vmcr_p" type="number" step="1" required />
                            </div>
                        </div>

                        <!-- Safety Bonus Checkbox (full width) -->
                        <div class="flex items-center gap-2">
                            <input
                                id="meets_safety_bonus_criteria"
                                v-model="form.meets_safety_bonus_criteria"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 focus:ring-blue-500"
                            />
                            <Label for="meets_safety_bonus_criteria"> Meets Safety Bonus Criteria </Label>
                        </div>

                        <!-- Action Buttons -->
                        <DialogFooter class="flex-col space-y-2 sm:flex-row sm:justify-end sm:space-x-2 sm:space-y-0">
                            <Button type="button" @click="closeModal" variant="outline" class="w-full sm:w-auto"> Cancel </Button>
                            <Button type="submit" variant="default" class="w-full sm:w-auto">
                                {{ formAction }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Confirmation Dialog -->
            <Dialog v-model:open="showDeleteModal">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Confirm Deletion</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this performance record? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="mt-4">
                        <Button type="button" @click="showDeleteModal = false" variant="outline"> Cancel </Button>
                        <Button type="button" @click="confirmDelete" variant="destructive"> Delete </Button>
                    </DialogFooter>
                </DialogContent> </Dialog
            ><!-- Add Delete Selected Confirmation Dialog -->
            <Dialog v-model:open="showDeleteSelectedModal">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Confirm Bulk Deletion</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete {{ selectedPerformances.length }} performance records? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="mt-4">
                        <Button type="button" @click="showDeleteSelectedModal = false" variant="outline"> Cancel </Button>
                        <Button type="button" @click="deleteSelectedPerformances()" variant="destructive"> Delete Selected </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Hidden Export Form -->
            <form ref="exportForm" method="GET" class="hidden" />
        </div>
    </AppLayout>
</template>

<script setup>
import Icon from '@/components/Icon.vue';
import {
    Alert,
    AlertDescription,
    AlertTitle,
    Button,
    Card,
    CardContent,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    Input,
    Label,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    performances: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    tenantSlug: { type: String, default: null },
    SuperAdmin: { type: Boolean, default: false },
    tenants: { type: Array, default: () => [] },
    dateFilter: { type: String, default: 'yesterday' },
    dateRange: { type: Object, default: () => ({ label: 'All Time' }) },
    perPage: { type: Number, default: 10 },
    weekNumber: {
        type: Number,
        default: null,
    },
    startWeekNumber: {
        type: Number,
        default: null,
    },
    endWeekNumber: {
        type: Number,
        default: null,
    },
    year: {
        type: Number,
        default: null,
    },
});
const weekNumberText = computed(() => {
    // For yesterday and current-week, show single week
    if ((activeTab.value === 'yesterday' || activeTab.value === 'current-week') && props.weekNumber && props.year) {
        return `Week ${props.weekNumber}, ${props.year}`;
    }

    // For 6w and quarterly, show start-end week range if available
    if ((activeTab.value === '6w' || activeTab.value === 'quarterly') && props.startWeekNumber && props.endWeekNumber && props.year) {
        return `Weeks ${props.startWeekNumber}-${props.endWeekNumber}, ${props.year}`;
    }

    return '';
});
// UI state
const errorMessage = ref('');
const successMessage = ref('');
const showModal = ref(false);
const formTitle = ref('Create Performance');
const formAction = ref('Create');
const activeTab = ref(props.dateFilter || 'full');
const perPage = ref(props.perPage || 10);
const selectedPerformances = ref([]);
const showDeleteSelectedModal = ref(false);
const showDeleteModal = ref(false);
const performanceToDelete = ref(null);
const exportForm = ref(null); // Add this line to define exportForm as a ref

// Update the visitPage function to preserve perPage
function visitPage(url) {
    if (url) {
        // Add perPage and dateFilter parameters to the URL
        const urlObj = new URL(url);
        urlObj.searchParams.set('perPage', perPage.value);
        urlObj.searchParams.set('dateFilter', activeTab.value);

        router.get(urlObj.href, {}, { only: ['performances'] });
    }
}

// Sorting state
const sortColumn = ref('date');
const sortDirection = ref('desc');

// Filtering state
const filters = ref({
    search: '',
    dateFrom: '',
    dateTo: '',
});

const breadcrumbs = [
    {
        title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
        href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'),
    },
    {
        title: 'Performance',
        href: props.tenantSlug ? route('performance.index', { tenantSlug: props.tenantSlug }) : route('performance.index.admin'),
    },
];

const tableColumns = [
    'date',
    'acceptance',
    'on_time_to_origin',
    'on_time_to_destination',
    'on_time',
    'maintenance_variance_to_spend',
    'open_boc',
    'meets_safety_bonus_criteria',
    'vcr_preventable',
    'vmcr_p',
];

// Initialize form state using Inertia's useForm helper.
const form = useForm({
    tenant_id: null,
    date: '',
    acceptance: '',
    on_time_to_origin: '',
    on_time_to_destination: '',
    maintenance_variance_to_spend: '',
    open_boc: '',
    meets_safety_bonus_criteria: false,
    vcr_preventable: '',
    vmcr_p: '',
    id: null,
});

const deleteForm = useForm({});
const importForm = useForm({ csv_file: null });

// Computed property for filtered and sorted performances
const filteredPerformances = computed(() => {
    let result = [...props.performances.data];

    // Apply sorting
    result.sort((a, b) => {
        let valA = a[sortColumn.value];
        let valB = b[sortColumn.value];

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

// Date filter function - use this instead of the old resetFilters
function resetFilters() {
    selectDateFilter('full');
}

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

// Make sure this function is defined before it's used in the template
function selectDateFilter(filter) {
    activeTab.value = filter;

    const routeName = props.tenantSlug ? route('performance.index', { tenantSlug: props.tenantSlug }) : route('performance.index.admin');

    router.get(
        routeName,
        {
            dateFilter: filter,
            perPage: perPage.value,
        },
        { preserveState: true },
    );
}

// Remove this duplicate function - delete lines 620-630
// function selectDateFilter(filter) {
//   activeTab.value = filter

//   const routeName = props.tenantSlug
//     ? route('performance.index', { tenantSlug: props.tenantSlug })
//     : route('performance.index.admin')

//   router.get(routeName, {
//     dateFilter: filter,
//     perPage: perPage.value
//   }, { preserveState: true })
// }

function submitForm() {
    const isCreate = formAction.value === 'Create';
    const routeName = isCreate
        ? props.SuperAdmin
            ? route('performance.store.admin')
            : route('performance.store', props.tenantSlug)
        : props.SuperAdmin
          ? route('performance.update.admin', [form.id])
          : route('performance.update', [props.tenantSlug, form.id]);

    const method = isCreate ? 'post' : 'put';

    form[method](routeName, {
        onSuccess: () => {
            successMessage.value = isCreate ? 'Performance created successfully.' : 'Performance updated successfully.';
            closeModal();
        },
        onError: () => {
            alert('Something went wrong.');
        },
    });
}

function handleImport(e) {
    const file = e.target.files?.[0];
    if (!file) return;

    importForm.csv_file = file;

    const routeName = props.SuperAdmin ? route('performance.import.admin') : route('performance.import', props.tenantSlug);

    importForm.post(routeName, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            successMessage.value = 'CSV Imported successfully.';
        },
        onError: () => {
            alert('Import failed.');
        },
    });
}

function exportCSV() {
    if (filteredPerformances.value.length === 0) {
        errorMessage.value = 'No data available to export';
        setTimeout(() => {
            errorMessage.value = '';
        }, 3000);
        return;
    }
    const routeName = props.SuperAdmin ? route('performance.export.admin') : route('performance.export', props.tenantSlug);

    exportForm.value?.setAttribute('action', routeName);
    exportForm.value?.submit();
}

// Remove this duplicate function
// function visitPage(url) {
//   if (url) {
//     router.get(url, {}, { only: ['performances'] })
//   }
// }

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
    if (newValue) {
        setTimeout(() => {
            successMessage.value = '';
        }, 5000);
    }
});

// Add a date formatting function
function formatDate(dateString) {
    if (!dateString) return '';
    // Create date with timezone adjustment to prevent the one day behind issue
    const date = new Date(dateString + 'T12:00:00'); // Add time component to avoid timezone issues

    // Format as month-day-year manually
    const month = date.getMonth() + 1; // getMonth is 0-indexed
    const day = date.getDate();
    const year = date.getFullYear();

    return `${month}-${day}-${year}`;
}
// Add these functions after the other function definitions (around line 550-600)
function openCreateModal() {
    formTitle.value = 'Create Performance';
    formAction.value = 'Create';

    // Reset the form
    form.reset();
    form.clearErrors();

    // Set default tenant_id for SuperAdmin
    if (props.SuperAdmin && props.tenants.length > 0) {
        form.tenant_id = '';
    }

    showModal.value = true;
}

function openEditModal(item) {
    formTitle.value = 'Edit Performance';
    formAction.value = 'Update';

    // Reset the form first to clear any previous data
    form.reset();
    form.clearErrors();

    // Populate the form with the item data
    form.id = item.id;
    form.tenant_id = item.tenant_id;
    form.date = item.date;
    form.acceptance = item.acceptance;
    form.on_time_to_origin = item.on_time_to_origin;
    form.on_time_to_destination = item.on_time_to_destination;
    form.maintenance_variance_to_spend = item.maintenance_variance_to_spend;
    form.open_boc = item.open_boc;
    form.meets_safety_bonus_criteria = item.meets_safety_bonus_criteria;
    form.vcr_preventable = item.vcr_preventable;
    form.vmcr_p = item.vmcr_p;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    form.clearErrors();
}

function deletePerformance(id) {
    performanceToDelete.value = id;
    showDeleteModal.value = true;
}

function confirmDelete() {
    if (!performanceToDelete.value) return;

    const routeName = props.SuperAdmin
        ? route('performance.destroy.admin', [performanceToDelete.value])
        : route('performance.destroy', [props.tenantSlug, performanceToDelete.value]);

    deleteForm.delete(routeName, {
        onSuccess: () => {
            successMessage.value = 'Performance deleted successfully.';
            showDeleteModal.value = false;
            performanceToDelete.value = null;
        },
        onError: () => {
            alert('Failed to delete performance record.');
        },
    });
}

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
    return filteredPerformances.value.length > 0 && selectedPerformances.value.length === filteredPerformances.value.length;
});

// Bulk selection functions
function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedPerformances.value = filteredPerformances.value.map((performance) => performance.id);
    } else {
        selectedPerformances.value = [];
    }
}

function confirmDeleteSelected() {
    if (selectedPerformances.value.length > 0) {
        showDeleteSelectedModal.value = true;
    }
}

function deleteSelectedPerformances() {
    const form = useForm({
        ids: selectedPerformances.value,
    });

    const routeName = props.SuperAdmin ? 'performance.destroyBulk.admin' : 'performance.destroyBulk';
    const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };

    form.delete(route(routeName, routeParams), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = `${selectedPerformances.value.length} performance records deleted successfully.`;
            selectedPerformances.value = [];
            showDeleteSelectedModal.value = false;
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
}

const formatRating = (rating) => {
    if (!rating) return 'Not Available';

    switch (rating) {
        case 'gold':
            return 'Gold Tier';
        case 'silver':
            return 'Silver Tier';
        case 'not_eligible':
            return 'Not Eligible';
        case 'fantastic_plus':
            return 'Fantastic +';
        case 'fantastic':
            return 'Fantastic';
        case 'good':
            return 'Good';
        case 'fair':
            return 'Fair';
        case 'poor':
            return 'Poor';
        default:
            return rating;
    }
};
// Add these new refs and computed properties
const showUploadOptions = ref(false);

// Computed property for template URL
const templateUrl = computed(() => {
    return '/storage/upload-data-temps/Performances Template.csv';
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

// Helper function to get display name for columns
const getDisplayColumnName = (column) => {
    const displayNames = {
        on_time_to_origin: 'OTO',
        on_time_to_destination: 'OTD',
        maintenance_variance_to_spend: 'MVtS',
        open_boc: 'Open BOC',
        vcr_preventable: 'VCR-P',
        vmcr_p: 'VMCR-P',
    };

    return (
        displayNames[column] ||
        column
            .replace(/_/g, ' ')
            .split(' ')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ')
    );
};

// Helper function to get full column name for tooltip
const getFullColumnName = (column) => {
    const fullNames = {
        on_time_to_origin: 'On Time to Origin',
        on_time_to_destination: 'On Time to Destination',
        maintenance_variance_to_spend: 'Maintenance Variance to Spend',
        open_boc: 'Open BOC',
        vcr_preventable: 'VCR Preventable',
        vmcr_p: 'VMCR P',
    };

    return (
        fullNames[column] ||
        column
            .replace(/_/g, ' ')
            .split(' ')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ')
    );
};

function changePerPage() {
    const routeName = props.tenantSlug ? route('performance.index', { tenantSlug: props.tenantSlug }) : route('performance.index.admin');

    router.get(
        routeName,
        {
            dateFilter: activeTab.value,
            perPage: perPage.value,
        },
        { preserveState: true },
    );
}
</script>
