<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
        <Head title="Drivers" />
        <!-- responsive here -->
        <div class=" w-full lg:max-w-7xl lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
            
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
            <div class="flex flex-col sm:flex-row justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6">
                <!-- responsive here -->
                <h1 class="text-lg md:text-xl lg:text-2xl font-bold">Driver Management</h1>
                <div class="flex flex-wrap gap-3">
                    <!-- responsive here -->
                    <Button class="px-2 py-0 md:px-4 md:py-2" @click="openCreateModal" variant="default">
                        <!-- responsive here -->
                        <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" />
                        Create New Driver
                    </Button>
                    <!-- Add Delete Selected button -->
                    <!-- responsive here -->
                    <Button class="px-2 py-0 md:px-4 md:py-2" v-if="selectedDrivers.length > 0" @click="confirmDeleteSelected()" variant="destructive">
                        <!-- responsive here -->
                        <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" />
                        Delete Selected ({{ selectedDrivers.length }})
                    </Button>
                    <div class="relative">
                        <!-- responsive here -->
                        <Button class="px-2 py-0 md:px-4 md:py-2" @click="showUploadOptions = !showUploadOptions" variant="secondary">
                            <!-- responsive here -->
                            <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2" />
                            Upload CSV
                            <!-- responsive here -->
                            <Icon name="chevron-down" class="mr-1 h-4 w-4 md:mr-2" />
                        </Button>
                        <div v-if="showUploadOptions" class="absolute right-0 z-10 mt-1 w-48 rounded-md border bg-background shadow-lg">
                            <div class="py-1">
                                <label class="block cursor-pointer px-4 py-2 text-sm hover:bg-muted">
                                    <span>Upload CSV File</span>
                                    <input type="file" class="hidden" @change="handleImport" accept=".csv, .txt" />
                                </label>
                                <a :href="templateUrl" download="Drivers Template.csv" class="block px-4 py-2 text-sm hover:bg-muted">
                                    Download Template
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- responsive here -->
                    <Button class="px-2 py-0 md:px-4 md:py-2" @click.prevent="exportCSV" variant="outline">
                        <!-- responsive here -->
                        <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" />
                        Download CSV
                    </Button>
                </div>
            </div>

            <!-- Filters Section -->
            <Card>
                <!-- responsive here -->
                <CardHeader class=" p-2 md:p-4 lg:p-6">
                    <!-- responsive here -->
                    <CardTitle class=" text-lg md:text-xl lg:text-2xl">Filters</CardTitle>
                </CardHeader>
                <!-- responsive here -->
                <CardContent class=" p-2 md:p-4 lg:p-6 ">
                    <!-- responsive here -->
                    <div class="flex flex-col gap-1 md:gap-4">
                        <!-- responsive here -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 md:gap-4 w-full">
                            <div>
                                <Label for="search">Search</Label>
                                <!-- responsive here -->
                                <Input
                                class = "py-1 px-1  md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10"
                                    id="search"
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search by name or email..."
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <Label for="dateFrom">Hiring Date From</Label>
                                <!-- responsive here -->
                                <Input class = "py-1 px-1  md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10" id="dateFrom" v-model="filters.dateFrom" type="date" @change="applyFilters" />
                            </div>
                            <div>
                                <Label for="dateTo">Hiring Date To</Label>
                                <!-- responsive here -->
                                <Input class = "py-1 px-1  md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10" id="dateTo" v-model="filters.dateTo" type="date" @change="applyFilters" />
                            </div>
                        </div>
                        <Button @click="resetFilters" variant="ghost" size="sm">
                            <Icon name="rotate_ccw" class="mr-2 h-4 w-4" />
                            Reset
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Data Table -->
            <!-- responsive here -->
            <Card class="mx-auto max-w-[95vw] md:max-w-[64vw] lg:max-w-full overflow-x-auto">
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
                                            {{
                                                col
                                                    .replace(/_/g, ' ')
                                                    .split(' ')
                                                    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                                                    .join(' ')
                                            }}
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
                                <TableRow v-if="entries.data.length === 0">
                                    <TableCell :colspan="SuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2" class="py-8 text-center">
                                        No driver records found matching your criteria
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="driver in entries.data" :key="driver.id">
                                    <!-- Checkbox for row selection -->
                                    <TableCell class="text-center">
                                        <input
                                            type="checkbox"
                                            :value="driver.id"
                                            v-model="selectedDrivers"
                                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                    </TableCell>
                                    <!-- Company Name column for SuperAdmin -->
                                    <TableCell v-if="SuperAdmin">{{ driver.tenant?.name ?? '—' }}</TableCell>
                                    <!-- Dynamic columns based on tableColumns array -->
                                    <TableCell v-for="col in tableColumns" :key="col">
                                        <template v-if="col === 'hiring_date'">
                                            {{ formatDate(driver[col]) }}
                                        </template>
                                        <template v-else>
                                            {{ driver[col] }}
                                        </template>
                                    </TableCell>
                                    <!-- Actions column -->
                                    <TableCell>
                                        <div class="flex space-x-2">
                                            <Button @click="openEditModal(driver)" variant="warning" size="sm">
                                                <Icon name="pencil" class="mr-1 h-4 w-4" />
                                                Edit
                                            </Button>
                                            <Button @click="deleteEntry(driver.id)" variant="destructive" size="sm">
                                                <Icon name="trash" class="mr-1 h-4 w-4" />
                                                Delete
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <!-- pagination -->
                    <div class="border-t bg-muted/20 px-4 py-3" v-if="entries.links">
                        <!-- responsive here -->
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
                            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                <span>Showing {{ filteredEntries.length }} of {{ entries.data.length }} entries</span>
                                <!-- responsive here -->
                                <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 w-full sm:w-auto">
                                    <span class="text-sm">Show:</span>
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
                            </div>
                            <!-- responsive here -->
                            <div class="flex flex-wrap">
                                <Button
                                    v-for="link in entries.links"
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

            <!-- Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-4xl max-w-[95vw] mx-auto p-2 md:p-4 lg:p-6">
                    <DialogHeader class="space-y-1 md:space-y-2">
                        <DialogTitle class="text-lg md:text-xl lg:text-2xl">{{ formTitle }}</DialogTitle>
                        <DialogDescription class="text-sm md:text-base"> Fill in the details to {{ formAction.toLowerCase() }} a driver. </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitForm" class="grid grid-cols-1 gap-2 md:gap-4 sm:grid-cols-2 mt-2 md:mt-4">
                        <div v-if="SuperAdmin" class="col-span-2">
                            <Label for="tenant" class="text-sm md:text-base">Company</Label>
                            <div class="relative">
                                <select
                                    id="tenant"
                                    v-model="form.tenant_id"
                                    class="flex h-8 md:h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-2 md:px-3 py-1 md:py-2 text-xs md:text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Select Company</option>
                                    <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                        {{ tenant.name }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="h-3 w-3 md:h-4 md:w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <Label for="first_name" class="text-sm md:text-base">First Name</Label>
                            <Input id="first_name" v-model="form.first_name" type="text" required class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2" />
                        </div>

                        <div>
                            <Label for="last_name" class="text-sm md:text-base">Last Name</Label>
                            <Input id="last_name" v-model="form.last_name" type="text" required class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <Label for="email" class="text-sm md:text-base">Email Address</Label>
                            <Input id="email" v-model="form.email" type="email" required class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <Label for="mobile_phone" class="text-sm md:text-base">Mobile Phone Number</Label>
                            <Input id="mobile_phone" v-model="form.mobile_phone" type="text" required class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <Label for="hiring_date" class="text-sm md:text-base">Hiring Date</Label>
                            <Input id="hiring_date" v-model="form.hiring_date" type="date" required class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2" />
                        </div>

                        <DialogFooter class="col-span-2 mt-2 md:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-4 justify-end">
                            <Button type="button" @click="closeModal" variant="outline" class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"> Cancel </Button>
                            <Button type="submit" variant="default" class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2">
                                {{ formAction }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Confirmation Dialog -->
            <Dialog v-model:open="showDeleteModal">
                <DialogContent class="max-w-[95vw] sm:max-w-md mx-auto p-2 md:p-4 lg:p-6">
                    <DialogHeader class="space-y-1 md:space-y-2">
                        <DialogTitle class="text-lg md:text-xl lg:text-2xl">Confirm Deletion</DialogTitle>
                        <DialogDescription class="text-sm md:text-base"> Are you sure you want to delete this driver? This action cannot be undone. </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="mt-2 md:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-4 justify-end">
                        <Button type="button" @click="showDeleteModal = false" variant="outline" class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"> Cancel </Button>
                        <Button type="button" @click="confirmDelete" variant="destructive" class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"> Delete </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            
            <!-- Add Delete Selected Confirmation Dialog -->
            <Dialog v-model:open="showDeleteSelectedModal">
                <DialogContent class="max-w-[95vw] sm:max-w-md mx-auto p-2 md:p-4 lg:p-6">
                    <DialogHeader class="space-y-1 md:space-y-2">
                        <DialogTitle class="text-lg md:text-xl lg:text-2xl">Confirm Bulk Deletion</DialogTitle>
                        <DialogDescription class="text-sm md:text-base">
                            Are you sure you want to delete {{ selectedDrivers.length }} driver records? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="mt-2 md:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-4 justify-end">
                        <Button type="button" @click="showDeleteSelectedModal = false" variant="outline" class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"> Cancel </Button>
                        <Button type="button" @click="deleteSelectedDrivers()" variant="destructive" class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"> Delete Selected </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

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
    CardHeader,
    CardTitle,
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

// Remove or comment out the DataTable imports since we're not using them anymore
// import {
//   DataTable,
//   DataTableColumnHeader,
//   DataTableViewOptions
// } from '@/components/ui/data-table';

// Import or create Select components

const props = defineProps({
    entries: Object,
    tenantSlug: String,
    SuperAdmin: Boolean,
    tenants: Array,
});

// Add activeTab ref
const activeTab = ref(props.dateFilter || 'full');

const perPage = ref(props.perPage || 10);
// UI state

const selectedDrivers = ref([]);
const showDeleteSelectedModal = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const formTitle = ref('Create Driver');
const formAction = ref('Create');
const exportForm = ref(null);
const driverToDelete = ref(null);

// Sorting state
const sortColumn = ref('last_name');
const sortDirection = ref('asc');

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
        title: 'Drivers',
        href: props.tenantSlug ? route('driver.index', { tenantSlug: props.tenantSlug }) : route('driver.index.admin'),
    },
];

// Define columns for the data table
const columns = computed(() => {
    const baseColumns = [
        {
            accessorKey: 'first_name',
            header: 'First Name',
            cell: (info) => info.getValue(),
            enableSorting: true,
        },
        {
            accessorKey: 'last_name',
            header: 'Last Name',
            cell: (info) => info.getValue(),
            enableSorting: true,
        },
        {
            accessorKey: 'email',
            header: 'Email',
            cell: (info) => info.getValue(),
            enableSorting: true,
        },
        {
            accessorKey: 'mobile_phone',
            header: 'Mobile Phone',
            cell: (info) => info.getValue(),
            enableSorting: true,
        },
        {
            accessorKey: 'hiring_date',
            header: 'Hiring Date',
            cell: (info) => formatDate(info.getValue()),
            enableSorting: true,
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: (info) => {
                return {
                    driver: info.row.original,
                    edit: () => openEditModal(info.row.original),
                    delete: () => deleteEntry(info.row.original.id),
                };
            },
            enableSorting: false,
        },
    ];

    // Add company name column for SuperAdmin
    if (props.SuperAdmin) {
        baseColumns.unshift({
            accessorKey: 'tenant.name',
            header: 'Company Name',
            cell: (info) => info.row.original.tenant?.name ?? '—',
            enableSorting: true,
        });
    }

    return baseColumns;
});

const tableColumns = ['first_name', 'last_name', 'email', 'mobile_phone', 'hiring_date'];

const form = useForm({
    id: null,
    first_name: '',
    last_name: '',
    email: '',
    mobile_phone: '',
    hiring_date: '',
    tenant_id: null,
});

const importForm = useForm({
    csv_file: null,
});

const deleteForm = useForm({});

// Computed property for filtered and sorted entries
const filteredEntries = computed(() => {
    let result = [...props.entries.data];

    // Apply search filter
    if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        result = result.filter(
            (item) =>
                item.first_name?.toLowerCase().includes(searchTerm) ||
                item.last_name?.toLowerCase().includes(searchTerm) ||
                item.email?.toLowerCase().includes(searchTerm),
        );
    }

    // Apply date filters
    if (filters.value.dateFrom) {
        result = result.filter((item) => item.hiring_date && item.hiring_date >= filters.value.dateFrom);
    }

    if (filters.value.dateTo) {
        result = result.filter((item) => item.hiring_date && item.hiring_date <= filters.value.dateTo);
    }

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

function resetFilters() {
    filters.value = {
        search: '',
        dateFrom: '',
        dateTo: '',
    };
}

function openCreateModal() {
    form.reset();
    form.tenant_id = null;
    formTitle.value = 'Create Driver';
    formAction.value = 'Create';
    showModal.value = true;
}

function openEditModal(item) {
    form.id = item.id;
    form.first_name = item.first_name;
    form.last_name = item.last_name;
    form.email = item.email;
    form.mobile_phone = item.mobile_phone;
    form.hiring_date = item.hiring_date;
    form.tenant_id = item.tenant_id;

    formTitle.value = 'Edit Driver';
    formAction.value = 'Update';
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function submitForm() {
    const payload = {
        first_name: form.first_name,
        last_name: form.last_name,
        email: form.email,
        mobile_phone: form.mobile_phone,
        hiring_date: form.hiring_date,
        tenant_id: form.tenant_id,
    };

    if (form.id) {
        form.put(props.SuperAdmin ? route('driver.update.admin', [form.id]) : route('driver.update', [props.tenantSlug, form.id]), {
            data: payload,
            onSuccess: () => {
                successMessage.value = 'Driver updated.';
                closeModal();
            },
            onError: () => alert('Error updating driver'),
        });
    } else {
        form.post(props.SuperAdmin ? route('driver.store.admin') : route('driver.store', props.tenantSlug), {
            data: payload,
            onSuccess: () => {
                successMessage.value = 'Driver created.';
                closeModal();
            },
            onError: () => alert('Error creating driver'),
        });
    }
}

function deleteEntry(id) {
    driverToDelete.value = id;
    showDeleteModal.value = true;
}

function confirmDelete() {
    deleteForm.delete(
        props.SuperAdmin ? route('driver.destroy.admin', [driverToDelete.value]) : route('driver.destroy', [props.tenantSlug, driverToDelete.value]),
        {
            onSuccess: () => {
                successMessage.value = 'Driver deleted.';
                showDeleteModal.value = false;
            },
        },
    );
}

function handleImport(e) {
    const file = e.target.files?.[0];
    if (!file) return;

    importForm.csv_file = file;
    importForm.post(props.SuperAdmin ? route('driver.import.admin') : route('driver.import', props.tenantSlug), {
        forceFormData: true,
        onSuccess: () => (successMessage.value = 'Data imported successfully.'),
        onError: () => alert('Import failed'),
    });
}

function exportCSV() {
    if (props.entries.data.length === 0) {
        errorMessage.value = 'No data available to export';
        setTimeout(() => {
            errorMessage.value = '';
        }, 3000);
        return;
    }
    const routeName = props.SuperAdmin ? route('driver.export.admin') : route('driver.export', props.tenantSlug);
    exportForm.value?.setAttribute('action', routeName);
    exportForm.value?.submit();
}

function visitPage(url) {
    if (url) {
        // Parse the URL to add perPage parameter
        const urlObj = new URL(url);
        urlObj.searchParams.set('perPage', perPage.value);

        // Use the modified URL with the perPage parameter
        router.get(
            urlObj.href,
            {},
            {
                only: ['entries'],
                preserveState: true,
            },
        );
    }
}

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
    if (newValue) {
        setTimeout(() => {
            successMessage.value = '';
        }, 5000);
    }
});

// Format date string from YYYY-MM-DD to m/d/Y without using Date()
// to avoid timezone-related day shifts.
function formatDate(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${Number(month)}/${Number(day)}/${year}`;
}

// Function to handle date filter selection
function selectDateFilter(filter) {
    activeTab.value = filter;

    const routeName = props.tenantSlug ? route('driver.index', { tenantSlug: props.tenantSlug }) : route('driver.index.admin');

    router.get(
        routeName,
        {
            dateFilter: filter,
            perPage: perPage.value,
        },
        { preserveState: true },
    );
}

// Function to handle per page change
function changePerPage() {
    const routeName = props.tenantSlug ? route('driver.index', { tenantSlug: props.tenantSlug }) : route('driver.index.admin');

    router.get(
        routeName,
        {
            dateFilter: activeTab.value,
            perPage: perPage.value,
        },
        { preserveState: true },
    );
}

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
    return filteredEntries.value.length > 0 && selectedDrivers.value.length === filteredEntries.value.length;
});

// Bulk selection functions
function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedDrivers.value = props.entries.data.map((driver) => driver.id);
    } else {
        selectedDrivers.value = [];
    }
}

function confirmDeleteSelected() {
    if (selectedDrivers.value.length > 0) {
        showDeleteSelectedModal.value = true;
    }
}

function deleteSelectedDrivers() {
    const form = useForm({
        ids: selectedDrivers.value,
    });

    const routeName = props.SuperAdmin ? 'driver.destroyBulk.admin' : 'driver.destroyBulk';
    const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };

    form.delete(route(routeName, routeParams), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = `${selectedDrivers.value.length} driver records deleted successfully.`;
            selectedDrivers.value = [];
            showDeleteSelectedModal.value = false;
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
}

// Handle selection change from DataTable
function handleSelectionChange(selectedIds) {
    selectedDrivers.value = [...selectedIds];
}

// Add these new refs and computed properties
const showUploadOptions = ref(false);

// Computed property for template URL
const templateUrl = computed(() => {
    return '/storage/upload-data-temps/Drivers Template.csv';
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
</script>
