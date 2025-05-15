<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
        <Head title="Miles Driven" />
        <!-- responsive here -->
        <div class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl  lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6">
            
            <!-- Success Message -->
            <Alert v-if="successMessage" variant="success">
                <AlertTitle>Success</AlertTitle>
                <AlertDescription>{{ successMessage }}</AlertDescription>
            </Alert>

            <!-- Actions Section -->
            <!-- responsive here -->
            <div class="flex justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6">
                <!-- responsive here -->
                <h1 class="text-lg md:text-xl lg:text-2xl font-bold">Miles Driven</h1>
                <!-- responsive here -->
                <Button class="px-2 py-0 md:px-4 md:py-2" @click="openModal()" variant="default">
                    <!-- responsive here -->
                    <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" /> Add Miles Driven
                </Button>
            </div>

            <!-- Date Filter Tabs -->
            <Card>
                <!-- responsive here -->
                <CardContent class="p-2 md:p-4 lg:p-6">
                    <!-- responsive here -->
                    <div class="flex flex-col items-center md:items-start gap-2">
                        <!-- responsive here -->
                        <div class="flex flex-wrap gap-1 md:gap-2">
                            <Button
                                @click="selectDateFilter('yesterday')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === 'yesterday' }"
                                >Yesterday</Button
                            >
                            <Button
                                @click="selectDateFilter('current-week')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === 'current-week' }"
                                >WTD</Button
                            >
                            <Button
                                @click="selectDateFilter('6w')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === '6w' }"
                                >T6W</Button
                            >
                            <Button
                                @click="selectDateFilter('quarterly')"
                                variant="outline"
                                size="sm"
                                :class="{ 'border-primary bg-primary/10 text-primary': activeTab === 'quarterly' }"
                                >Quarterly</Button
                            >
                        </div>
                        <div v-if="dateRange" class="text-sm text-muted-foreground">
                            <span v-if="activeTab === 'yesterday' && dateRange.start"> Showing data from {{ formatDate(dateRange.start) }} </span>
                            <span v-else-if="dateRange.start && dateRange.end">
                                Showing data from {{ formatDate(dateRange.start) }} to {{ formatDate(dateRange.end) }}
                            </span>
                            <span v-else>{{ dateRange.label }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Filters -->
            <Card>
                <!-- responsive here -->
                <CardHeader class=" p-2 md:p-4 lg:p-6">
                    <!-- responsive here -->
                    <CardTitle class=" text-lg md:text-xl lg:text-2xl">Filters</CardTitle></CardHeader
                >
                <!-- responsive here -->
                <CardContent  class=" p-2 md:p-4 lg:p-6 ">
                    <!-- responsive here -->
                    <div class="flex flex-col gap-1 md:gap-4">
                        <!-- responsive here -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 md:gap-4 w-full">
                            <div>
                                <Label for="search">Search</Label>
                                <!-- responsive here -->
                                <Input class = "py-1 px-1  md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10" id="search" v-model="filters.search" type="text" placeholder="Search..." @input="applyFilters" />
                            </div>
                            <div>
                                <Label for="dateFrom">Date From</Label>
                                <!-- responsive here -->
                                <Input class = "py-1 px-1  md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10" id="dateFrom" v-model="filters.dateFrom" type="date" @change="applyFilters" />
                            </div>
                            <div>
                                <Label for="dateTo">Date To</Label>
                                <!-- responsive here -->
                                <Input class = "py-1 px-1  md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10" id="dateTo" v-model="filters.dateTo" type="date" @change="applyFilters" />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <Button @click="resetFilters" variant="ghost" size="sm">
                                <Icon name="rotate_ccw" class="mr-2 h-4 w-4" /> Reset Filters
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Table -->
            <!-- responsive here -->
            <Card class="mx-auto max-w-[95vw] md:max-w-[64vw] lg:max-w-full overflow-x-auto">
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table class="relative h-[500px] overflow-auto">
                            <TableHeader>
                                <TableRow class="sticky top-0 z-10 border-b bg-background hover:bg-background">
                                    <TableHead v-if="SuperAdmin" class="whitespace-nowrap">Company Name</TableHead>
                                    <TableHead class="cursor-pointer whitespace-nowrap" @click="sortBy('week_start_date')">
                                        <div class="flex items-center">
                                            Week Start Date
                                            <span class="ml-2">
                                                <Icon v-if="sortColumn === 'week_start_date' && sortDirection === 'asc'" name="chevron-up" />
                                                <Icon v-else-if="sortColumn === 'week_start_date' && sortDirection === 'desc'" name="chevron-down" />
                                                <Icon v-else name="chevron-up-down" class="opacity-50" />
                                            </span>
                                        </div>
                                    </TableHead>
                                    <TableHead class="cursor-pointer whitespace-nowrap" @click="sortBy('week_end_date')">
                                        <div class="flex items-center">
                                            Week End Date
                                            <span class="ml-2">
                                                <Icon v-if="sortColumn === 'week_end_date' && sortDirection === 'asc'" name="chevron-up" />
                                                <Icon v-else-if="sortColumn === 'week_end_date' && sortDirection === 'desc'" name="chevron-down" />
                                                <Icon v-else name="chevron-up-down" class="opacity-50" />
                                            </span>
                                        </div>
                                    </TableHead>
                                    <TableHead class="cursor-pointer whitespace-nowrap" @click="sortBy('miles')">
                                        <div class="flex items-center">
                                            Miles
                                            <span class="ml-2">
                                                <Icon v-if="sortColumn === 'miles' && sortDirection === 'asc'" name="chevron-up" />
                                                <Icon v-else-if="sortColumn === 'miles' && sortDirection === 'desc'" name="chevron-down" />
                                                <Icon v-else name="chevron-up-down" class="opacity-50" />
                                            </span>
                                        </div>
                                    </TableHead>
                                    <TableHead class="whitespace-nowrap">Notes</TableHead>
                                    <TableHead class="whitespace-nowrap">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="item in filteredEntries" :key="item.id" class="hover:bg-muted/50">
                                    <TableCell v-if="SuperAdmin">{{ item.tenant?.name }}</TableCell>
                                    <TableCell>{{ formatDate(item.week_start_date) }}</TableCell>
                                    <TableCell>{{ formatDate(item.week_end_date) }}</TableCell>
                                    <TableCell>{{ formatNumber(item.miles) }}</TableCell>
                                    <TableCell>{{ truncateText(item.notes, 30) }}</TableCell>
                                    <TableCell>
                                        <div class="flex space-x-2">
                                            <Button variant="outline" size="sm" @click="openModal(item)">
                                                <Icon name="pencil" class="mr-1 h-4 w-4" /> Edit
                                            </Button>
                                            <Button variant="destructive" size="sm" @click="confirmDelete(item)">
                                                <Icon name="trash" class="mr-1 h-4 w-4" /> Delete
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredEntries.length === 0">
                                    <TableCell :colspan="SuperAdmin ? 6 : 5" class="py-4 text-center"> No miles driven records found </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div class="border-t bg-muted/20 px-4 py-3" v-if="entries.links">
                        <!-- responsive here -->
                        <div class="items-center flex flex-col sm:flex-row justify-between gap-2">
                            <div class=" text-sm text-muted-foreground">Showing {{ filteredEntries.length }} of {{ entries.data.length }} entries</div>
                            <!-- responsive here -->
                            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 w-full sm:w-auto">
                                <div class="flex items-center gap-2">
                                    <Label for="perPage" class="text-sm">Per page:</Label>
                                    <select
                                        id="perPage"
                                        v-model="perPage"
                                        @change="changePerPage"
                                        class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm"
                                    >
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
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
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Add/Edit Modal -->
        <Dialog v-model:open="isModalOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ form.id ? 'Edit Miles Driven' : 'Add Miles Driven' }}</DialogTitle>
                    <DialogDescription>
                        {{ form.id ? 'Update the miles driven record details.' : 'Enter the details for the new miles driven record.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="grid gap-4 py-4">
                    <!-- Tenant selector -->
                    <div v-if="SuperAdmin">
                        <Label for="tenant_id">Company</Label>
                        <select id="tenant_id" v-model="form.tenant_id" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="">Select Company</option>
                            <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                        <p v-if="form.errors.tenant_id" class="mt-1 text-sm text-destructive">
                            {{ form.errors.tenant_id }}
                        </p>
                    </div>

                    <!-- Year & Week Number -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="year">Year</Label>
                            <Input id="year" type="number" v-model.number="form.year" min="2000" max="2100" @input="computeWeekSpan" />
                            <p v-if="form.errors.year" class="mt-1 text-sm text-destructive">
                                {{ form.errors.year }}
                            </p>
                        </div>
                        <div>
                            <Label for="week_number">Week #</Label>
                            <Input id="week_number" type="number" v-model.number="form.week_number" min="1" max="53" @input="computeWeekSpan" />
                            <p v-if="form.errors.week_number" class="mt-1 text-sm text-destructive">
                                {{ form.errors.week_number }}
                            </p>
                        </div>
                    </div>

                    <!-- Start/End as plain text -->
                    <div v-if="form.week_start_date" class="grid grid-cols-2 gap-4">
                        <div>
                            <Label>Start (Sunday)</Label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ formatDate(form.week_start_date) }}</p>
                        </div>
                        <div>
                            <Label>End (Saturday)</Label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ formatDate(form.week_end_date) }}</p>
                        </div>
                    </div>

                    <!-- Miles -->
                    <div>
                        <Label for="miles">Miles</Label>
                        <Input
                            id="miles"
                            v-model="form.miles"
                            type="number"
                            step="0.0001"
                            min="0"
                            max="999999.9999"
                            placeholder="Enter miles (max 999999.9999)"
                        />
                        <p v-if="form.errors.miles" class="mt-1 text-sm text-destructive">
                            {{ form.errors.miles }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <Label for="notes">Notes</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            placeholder="Optional notes"
                            class="h-24 w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm"
                        ></textarea>
                        <p v-if="form.errors.notes" class="mt-1 text-sm text-destructive">
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <DialogFooter class="mt-4">
                        <Button variant="outline" type="button" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">{{ form.id ? 'Update' : 'Create' }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="isDeleteModalOpen">
            <DialogContent class="w-[95vw] sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="text-lg md:text-xl">Confirm Deletion</DialogTitle>
                    <DialogDescription class="text-sm md:text-base"> Are you sure you want to delete this miles driven record? This action cannot be undone. </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-2 md:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-0">
                    <Button variant="outline" @click="isDeleteModalOpen = false" class="w-full sm:w-auto">Cancel</Button>
                    <Button variant="destructive" @click="deleteRecord" :disabled="deleteForm.processing" class="w-full sm:w-auto"> Delete </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup>
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
} from '@/Components/ui';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/components/Icon.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps({
    entries: Object,
    tenantSlug: String,
    SuperAdmin: Boolean,
    tenants: Array,
    dateRange: Object,
    dateFilter: { type: String, default: 'yesterday' },
});

// state
const successMessage = ref('');
const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const milesDrivenToDelete = ref(null);
const sortColumn = ref('week_start_date');
const sortDirection = ref('desc');
const filters = ref({ search: '', dateFrom: '', dateTo: '' });
const activeTab = ref(props.dateFilter);
const perPage = ref(10);

// breadcrumbs
const breadcrumbs = [
    {
        title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
        href: props.tenantSlug ? route('dashboard', { tenantSlug: props.tenantSlug }) : route('admin.dashboard'),
    },
    {
        title: 'Miles Driven',
        href: props.tenantSlug ? route('miles_driven.index', { tenantSlug: props.tenantSlug }) : route('miles_driven.index.admin'),
    },
];

// form
const form = useForm({
    id: null,
    tenant_id: props.SuperAdmin ? '' : null,
    year: '',
    week_number: '',
    week_start_date: '',
    week_end_date: '',
    miles: '',
    notes: '',
});
const deleteForm = useForm({});

// filtered + sorted entries
const filteredEntries = computed(() => {
    let result = [...props.entries.data];
    if (filters.value.search) {
        const term = filters.value.search.toLowerCase();
        result = result.filter((item) => item.tenant?.name?.toLowerCase().includes(term) || item.notes?.toLowerCase().includes(term));
    }
    if (filters.value.dateFrom) {
        result = result.filter((item) => item.week_start_date >= filters.value.dateFrom);
    }
    if (filters.value.dateTo) {
        result = result.filter((item) => item.week_end_date <= filters.value.dateTo);
    }
    result.sort((a, b) => {
        let aVal = a[sortColumn.value];
        let bVal = b[sortColumn.value];
        if (aVal === null) return 1;
        if (bVal === null) return -1;
        if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
        if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
        return 0;
    });
    return result;
});

// sort
function sortBy(col) {
    if (sortColumn.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = col;
        sortDirection.value = 'asc';
    }
}

// filters
function applyFilters() {}
function resetFilters() {
    filters.value = { search: '', dateFrom: '', dateTo: '' };
}

// pagination
function visitPage(url) {
    if (url) {
        // Add perPage and dateFilter parameters to the URL
        const urlObj = new URL(url);
        urlObj.searchParams.set('perPage', perPage.value);
        urlObj.searchParams.set('dateFilter', activeTab.value);

        router.get(urlObj.href, {}, { only: ['entries'] });
    }
}

// compute Sunday→Saturday from year+week
function computeWeekSpan() {
    const Y = Number(form.year);
    const W = Number(form.week_number);
    if (!Y || !W) {
        form.week_start_date = '';
        form.week_end_date = '';
        return;
    }
    const d = new Date(Date.UTC(Y, 0, 4));
    const day = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + (W - 1) * 7 + (1 - day));
    const sunday = new Date(d);
    sunday.setUTCDate(d.getUTCDate() - 1);
    const saturday = new Date(sunday);
    saturday.setUTCDate(sunday.getUTCDate() + 6);
    form.week_start_date = sunday.toISOString().slice(0, 10);
    form.week_end_date = saturday.toISOString().slice(0, 10);
}

// open modal
function openModal(item = null) {
    form.reset();
    form.clearErrors();
    if (item) {
        form.id = item.id;
        form.tenant_id = item.tenant_id;
        form.week_start_date = item.week_start_date;
        form.week_end_date = item.week_end_date;
        form.miles = item.miles;
        form.notes = item.notes;
        // derive year/week from existing start date
        const sd = new Date(item.week_start_date);
        const tmp = new Date(Date.UTC(sd.getUTCFullYear(), sd.getUTCMonth(), sd.getUTCDate()));
        tmp.setUTCDate(tmp.getUTCDate() + 4 - (tmp.getUTCDay() || 7));
        const Y = tmp.getUTCFullYear();
        const W = Math.ceil(((tmp - Date.UTC(Y, 0, 1)) / 86400000 + 1) / 7) + 1;
        form.year = Y;
        form.week_number = W;
    }
    isModalOpen.value = true;
}

// delete
function confirmDelete(item) {
    if (!confirm('Are you sure you want to delete this miles driven record?')) return;
    milesDrivenToDelete.value = item;
    isDeleteModalOpen.value = true;
}

// submit
function submitForm() {
    if (form.id) {
        const name = props.SuperAdmin ? 'miles_driven.update.admin' : 'miles_driven.update';
        const params = props.SuperAdmin ? { milesDriven: form.id } : { tenantSlug: props.tenantSlug, milesDriven: form.id };
        form.put(route(name, params), {
            onSuccess: () => {
                isModalOpen.value = false;
                successMessage.value = 'Miles driven record updated successfully.';
            },
        });
    } else {
        const name = props.SuperAdmin ? 'miles_driven.store.admin' : 'miles_driven.store';
        const params = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };
        form.post(route(name, params), {
            onSuccess: () => {
                isModalOpen.value = false;
                successMessage.value = 'Miles driven record created successfully.';
            },
        });
    }
}

// delete record
function deleteRecord() {
    const name = props.SuperAdmin ? 'miles_driven.destroy.admin' : 'miles_driven.destroy';
    const params = props.SuperAdmin
        ? { milesDriven: milesDrivenToDelete.value.id }
        : { tenantSlug: props.tenantSlug, milesDriven: milesDrivenToDelete.value.id };
    deleteForm.delete(route(name, params), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            successMessage.value = 'Miles driven record deleted successfully.';
        },
    });
}

// date-filter tabs
function selectDateFilter(filter) {
    activeTab.value = filter;
    const name = props.tenantSlug ? 'miles_driven.index' : 'miles_driven.index.admin';
    const params = props.tenantSlug ? { tenantSlug: props.tenantSlug } : {};
    router.get(route(name, params), { dateFilter: filter, perPage: perPage.value }, { preserveState: true });
}
function changePerPage() {
    const name = props.tenantSlug ? 'miles_driven.index' : 'miles_driven.index.admin';
    const params = props.tenantSlug ? { tenantSlug: props.tenantSlug } : {};
    router.get(route(name, params), { dateFilter: activeTab.value, perPage: perPage.value }, { preserveState: true });
}

// helpers
function formatDate(s) {
    if (!s) return '';
    const d = new Date(s);
    const utc = new Date(d.getTime() + d.getTimezoneOffset() * 60000);
    return utc.toLocaleDateString();
}
// Update the formatNumber helper function
function formatNumber(n) {
    return Number(n).toLocaleString(undefined, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
}
function truncateText(t, m) {
    if (!t) return '';
    return t.length > m ? t.slice(0, m) + '…' : t;
}

// auto-hide success
watch(successMessage, (v) => {
    if (v) setTimeout(() => (successMessage.value = ''), 5000);
});
</script>
