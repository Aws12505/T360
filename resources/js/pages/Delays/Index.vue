<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
        <Head title="On-Time" />
        <!-- responsive here -->
        <div class="mx-auto w-full space-y-8 p-6">
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
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">On-Time Management</h1>
                <div class="flex flex-wrap gap-3">
                    <!-- responsive here -->
                    <Button @click="openForm()" variant="default">
                        <!-- responsive here -->
                        <Icon name="plus" class="mr-2 h-4 w-4" />
                        Add Delay
                    </Button>
                    <!-- responsive here -->
                    <Button v-if="selectedDelays.length > 0" @click="confirmDeleteSelected()" variant="destructive">
                        <!-- responsive here -->
                        <Icon name="trash" class="mr-2 h-4 w-4" />
                        Delete Selected ({{ selectedDelays.length }})
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
                                <a :href="templateUrl" download="Delays Template.csv" class="block px-4 py-2 text-sm hover:bg-muted">
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
                    <!-- responsive here -->
                    <Button v-if="isSuperAdmin" @click="openCodeModal()" variant="outline">
                        <!-- responsive here -->
                        <Icon name="settings" class="mr-2 h-4 w-4" />
                        Manage Delay Codes
                    </Button>
                </div>
            </div>

            <!-- Hidden Export Form -->
            <form ref="exportForm" :action="exportUrl" method="GET" class="hidden"></form>

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

            <!-- No Data Message -->
            <div v-if="!hasData" class="flex flex-col items-center justify-center rounded-lg border bg-muted/20 py-16">
                <Icon name="database-x" class="mb-4 h-16 w-16 text-muted-foreground" />
                <h2 class="text-center text-2xl font-bold text-muted-foreground">There is No Data to give Information about.</h2>
            </div>

            <!-- Content Section - Only show if data exists -->
            <template v-if="hasData">
                <!-- Filters Section -->
                <Card>
                    <!-- responsive here -->
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <!-- responsive here -->
                                <CardTitle>Filters</CardTitle>
                                <div v-if="!showFilters && hasActiveFilters" class="ml-4 flex flex-wrap gap-2">
                                    <div
                                        v-if="filters.search"
                                        class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        Search: {{ filters.search }}
                                    </div>

                                    <div
                                        v-if="filters.delayCode"
                                        class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        Code: {{ getDelayCodeLabel(filters.delayCode) }}
                                    </div>
                                    <div
                                        v-if="filters.delayCategory"
                                        class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        Category: {{ formatDelayCategory(filters.delayCategory) }}
                                    </div>
                                    <div
                                        v-if="filters.delayType"
                                        class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        Type: {{ filters.delayType === 'origin' ? 'Origin' : 'Destination' }}
                                    </div>
                                    <div
                                        v-if="filters.disputed"
                                        class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        Disputed: {{ filters.disputed === 'true' ? 'Yes' : 'No' }}
                                    </div>
                                    <div
                                        v-if="filters.controllable"
                                        class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        Driver Controllable:
                                        {{ filters.controllable === 'true' ? 'Yes' : filters.controllable === 'false' ? 'No' : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <Button variant="ghost" size="sm" @click="showFilters = !showFilters">
                                {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                                <Icon :name="showFilters ? 'chevron-up' : 'chevron-down'" class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <!-- responsive here -->
                    <CardContent v-if="showFilters" class="pt-2">
                        <!-- responsive here -->
                        <div class="flex flex-col justify-between gap-4">
                            <!-- responsive here -->
                            <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                                <div>
                                    <Label for="search">Search</Label>
                                    <!-- responsive here -->
                                    <Input
                                        id="search"
                                        v-model="filters.search"
                                        type="text"
                                        placeholder="Search by driver or type..."
                                        @input="applyFilters"
                                    />
                                </div>

                                <div>
                                    <Label for="delayCode">Delay Code</Label>
                                    <select
                                        id="delayCode"
                                        v-model="filters.delayCode"
                                        @change="applyFilters"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        <option value="">All Codes</option>
                                        <option v-for="code in delay_codes" :key="code.id" :value="code.id">
                                            {{ code.code }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="delayCategory">Delay Category</Label>
                                    <select
                                        id="delayCategory"
                                        v-model="filters.delayCategory"
                                        @change="applyFilters"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        <option value="">All Categories</option>
                                        <option value="1_120">1-120 mins</option>
                                        <option value="121_600">121-600 mins</option>
                                        <option value="601_plus">601+ mins</option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="delayType">Delay Type</Label>
                                    <select
                                        id="delayType"
                                        v-model="filters.delayType"
                                        @change="applyFilters"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
                                        <option value="">All Types</option>
                                        <option value="origin">Origin</option>
                                        <option value="destination">Destination</option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="disputed">Disputed Status</Label>
                                    <select
                                        id="disputed"
                                        v-model="filters.disputed"
                                        @change="applyFilters"
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
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
                                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    >
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

                <!-- On-Time Dashboard -->
                <OnTimeDashboard
                    v-if="!isSuperAdmin"
                    :metricsData="ontimeMetrics"
                    :driversData="bottomDrivers"
                    :chartData="ontimeChartData"
                    :averageOntime="average_ontime"
                    :delayType="filters.delayType"
                />

                <!-- Delays Table -->
                <!-- responsive here -->
                <Card>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <Table class="relative h-[500px] overflow-auto">
                                <TableHeader>
                                    <TableRow class="sticky top-0 z-10 border-b bg-background hover:bg-background">
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
                                        <TableHead v-for="col in tableColumns" :key="col" class="cursor-pointer" @click="sortBy(col)">
                                            <div class="flex items-center">
                                                <template v-if="col === 'delay_category'"> Delay </template>
                                                <template v-else>
                                                    {{
                                                        col
                                                            .replace(/_/g, ' ')
                                                            .split(' ')
                                                            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                                                            .join(' ')
                                                    }}
                                                </template>
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
                                                    <svg
                                                        v-else
                                                        class="h-4 w-4"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                    >
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
                                        <TableCell
                                            :colspan="isSuperAdmin ? tableColumns.length + 2 : tableColumns.length + 1"
                                            class="py-8 text-center"
                                        >
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
                                                {{ delay[col] === null ? 'N/A' : delay[col] ? 'Yes' : 'No' }}
                                            </template>
                                            <template v-else-if="col === 'delay_code'">
                                                {{ delay.delay_code?.code || '—' }}
                                                <span v-if="delay.delay_code?.deleted_at" class="ml-1 text-xs text-red-500">(Deleted Code)</span>
                                            </template>
                                            <template v-else-if="col === 'delay_category'">
                                                {{ formatDelayCategory(delay.delay_category) }}
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
                        <!-- paginate -->
                        <div class="border-t bg-muted/20 px-4 py-3" v-if="delays.links">
                            <!-- responsive here -->
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-muted-foreground">
                                    Showing {{ filteredDelays.length }} of {{ delays.data.length }} entries
                                </div>
                                <!-- responsive here -->
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

                <!-- Delay Form Modal (Pass only active delay codes) -->
                <Dialog v-model:open="formModal">
                    <DialogContent class="sm:max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>{{ selectedDelay ? 'Edit' : 'Add' }} Delay</DialogTitle>
                            <DialogDescription> Fill in the details to {{ selectedDelay ? 'update' : 'create' }} a delay record. </DialogDescription>
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
                            <DialogDescription> Create and manage delay codes for your organization. </DialogDescription>
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
                                <div v-if="!delay_codes || delay_codes.length === 0" class="rounded-md border py-8 text-center text-muted-foreground">
                                    No delay codes found
                                </div>
                                <div v-else class="space-y-2">
                                    <div
                                        v-for="code in delay_codes"
                                        :key="code.id"
                                        class="group flex items-center justify-between rounded-md border p-3 hover:bg-muted/50"
                                    >
                                        <div class="flex-1 cursor-pointer" @click="editCode(code)">
                                            <div class="font-medium">
                                                {{ code.code }}
                                                <span v-if="code.deleted_at" class="ml-2 text-xs text-red-500">(Deleted)</span>
                                            </div>
                                            <div v-if="code.description" class="mt-1 text-sm text-muted-foreground">
                                                {{ code.description }}
                                            </div>
                                        </div>
                                        <div class="opacity-0 transition-opacity group-hover:opacity-100">
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
                            <div v-if="showCodeForm" class="space-y-4 rounded-md border p-4">
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
                            <DialogDescription> Are you sure you want to delete this delay code? This action cannot be undone. </DialogDescription>
                        </DialogHeader>
                        <DialogFooter class="mt-4">
                            <Button type="button" @click="codeDeleteConfirm = false" variant="outline"> Cancel </Button>
                            <Button type="button" @click="deleteCode(codeToDelete)" variant="destructive"> Delete </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <!-- Delete Delay Confirmation Dialog -->
                <Dialog v-model:open="showDeleteModal">
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Confirm Deletion</DialogTitle>
                            <DialogDescription> Are you sure you want to delete this delay record? This action cannot be undone. </DialogDescription>
                        </DialogHeader>
                        <DialogFooter class="mt-4">
                            <Button type="button" @click="showDeleteModal = false" variant="outline"> Cancel </Button>
                            <Button type="button" @click="deleteDelay(delayToDelete)" variant="destructive"> Delete </Button>
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
                            <Button type="button" @click="showDeleteSelectedModal = false" variant="outline"> Cancel </Button>
                            <Button type="button" @click="deleteSelectedDelays()" variant="destructive"> Delete Selected </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </template>
        </div>
    </AppLayout>
</template>

<script setup>
import DelayForm from '@/components/DelayForm.vue';
import Icon from '@/components/Icon.vue';
import OnTimeDashboard from '@/components/ontime/OnTimeDashboard.vue';
import {
    Alert,
    AlertDescription,
    AlertTitle,
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    Input,
    Label,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui';
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

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
    dateFilter: { type: String, default: 'yesterday' },
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
    delay_breakdown: {
        type: Object,
        default: null,
    },
    line_chart_data: {
        type: Object,
        default: null,
    },
    average_ontime: {
        type: Number,
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
const errorMessage = ref('');
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
    delayCategory: '',
    delayType: '',
    disputed: '',
    controllable: '',
});

// Table columns
const tableColumns = ['date', 'delay_type', 'driver_name', 'delay_category', 'delay_code', 'disputed', 'driver_controllable'];

// Computed property: Filtered and sorted delays
const filteredDelays = computed(() => {
    let result = [...props.delays.data];

    // Search filter
    if (filters.value.search) {
        const term = filters.value.search.toLowerCase();
        result = result.filter(
            (item) =>
                item.driver_name?.toLowerCase().includes(term) ||
                item.delay_type?.toLowerCase().includes(term) ||
                item.delay_code?.code?.toLowerCase().includes(term),
        );
    }

    // Date filters
    if (filters.value.dateFrom) {
        result = result.filter((item) => item.date && item.date >= filters.value.dateFrom);
    }
    if (filters.value.dateTo) {
        result = result.filter((item) => item.date && item.date <= filters.value.dateTo);
    }

    // Delay code filter
    if (filters.value.delayCode) {
        result = result.filter((item) => item.delay_code?.id === parseInt(filters.value.delayCode));
    }
    //delay type filter
    if (filters.value.delayType) {
        result = result.filter((item) => item.delay_type === filters.value.delayType);
    }
    // Delay category filter
    if (filters.value.delayCategory) {
        result = result.filter((item) => item.delay_category === filters.value.delayCategory);
    }

    // Disputed filter
    if (filters.value.disputed !== '') {
        const isDisputed = filters.value.disputed === 'true';
        result = result.filter((item) => item.disputed === isDisputed);
    }

    // Controllable filter
    if (filters.value.controllable !== '') {
        if (filters.value.controllable === 'null') {
            result = result.filter((item) => item.driver_controllable === null);
        } else {
            const isControllable = filters.value.controllable === 'true';
            result = result.filter((item) => item.driver_controllable === isControllable);
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
    return props.delay_codes.filter((code) => !code.deleted_at);
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
        delayCategory: '',
        delayType: '',
        disputed: '',
        controllable: '',
    };
    applyFilters();
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

// Add these variables to your script setup section
const showFilters = ref(false);

// Add this computed property to check if any filters are active
const hasActiveFilters = computed(() => {
    return (
        filters.value.search ||
        filters.value.dateFrom ||
        filters.value.dateTo ||
        filters.value.delayCode ||
        filters.value.delayCategory ||
        filters.value.disputed ||
        filters.value.delayType ||
        filters.value.controllable
    );
});

// Add this helper function to get the delay code label
const getDelayCodeLabel = (codeId) => {
    if (!codeId) return '';
    const code = props.delay_codes.find((c) => c.id === parseInt(codeId));
    return code ? code.code : '';
};

// Add the missing deleteCode function
const deleteCode = async (id) => {
    try {
        await router.delete(route('delay_codes.destroy.admin', id), {
            onSuccess: () => {
                codeDeleteConfirm.value = false;
                successMessage.value = 'Delay code deleted successfully';
            },
        });
    } catch (error) {
        console.error('Error deleting delay code:', error);
    }
};

// Function to format delay category values
function formatDelayCategory(category) {
    if (!category) return '—';

    switch (category) {
        case '1_120':
            return '1-120 mins';
        case '121_600':
            return '121-600 mins';
        case '601_plus':
            return '601+ mins';
        default:
            return category;
    }
}

const saveCode = () => {
    const form = useForm({ code: codeForm.value.code, description: codeForm.value.description });
    const routeName = editingCode.value
        ? props.isSuperAdmin
            ? 'delay_codes.update.admin'
            : 'delay_codes.update'
        : props.isSuperAdmin
          ? 'delay_codes.store.admin'
          : 'delay_codes.store';
    const routeParams = editingCode.value
        ? props.isSuperAdmin
            ? { id: editingCode.value }
            : { tenantSlug: props.tenantSlug, id: editingCode.value }
        : props.isSuperAdmin
          ? {}
          : { tenantSlug: props.tenantSlug };
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

// Check for flash messages
if (props.flash && props.flash.error) {
    errorMessage.value = props.flash.error;
}

// Auto-hide error message
watch(errorMessage, (newValue) => {
    if (newValue) {
        setTimeout(() => {
            errorMessage.value = '';
        }, 5000);
    }
});

// On-Time dashboard data from API
const ontimeMetrics = computed(() => {
    if (!props.delay_breakdown?.by_category) return null;

    const categoryData = props.delay_breakdown.by_category;

    const type = filters.value.delayType;
    if (type) {
        return {
            between1_120Count: categoryData[`category_1_120_${type}_count`] || '0',
            between121_600Count: categoryData[`category_121_600_${type}_count`] || '0',
            moreThan601Count: categoryData[`category_601_plus_${type}_count`] || '0',
            totalDelays: categoryData[`total_${type}_delays`] || '0',
            by_category: true,
        };
    } else {
        return {
            between1_120Count: categoryData.category_1_120_count || '0',
            between121_600Count: categoryData.category_121_600_count || '0',
            moreThan601Count: categoryData.category_601_plus_count || '0',
            totalDelays: categoryData.total_delays || '0',
            by_category: true,
        };
    }
});

const bottomDrivers = computed(() => {
    if(filters.value.delayType=="origin")
    return props.delay_breakdown?.bottom_five_drivers.origin || [];

    if(filters.value.delayType=="destination")
    return props.delay_breakdown?.bottom_five_drivers.destination || [];

    else
    return props.delay_breakdown?.bottom_five_drivers.total || [];
});

const ontimeChartData = computed(() => {
    if (!props.line_chart_data || props.line_chart_data.length === 0) {
        return {
            labels: [],
            datasets: [
                {
                    label: 'On-Time Performance',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: false,
                },
            ],
        };
    }

    return {
        labels: props.line_chart_data.map((item) => item.date),
        datasets: [
            {
                label: 'On-Time Performance',
                data: props.line_chart_data.map((item) => item.onTimePerformance),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: false,
            },
        ],
    };
});

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
    return filteredDelays.value.length > 0 && selectedDelays.value.length === filteredDelays.value.length;
});

// Computed property to check if there's data
const hasData = computed(() => {
    return props.delays.data && props.delays.data.length > 0;
});

// Bulk selection functions
function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedDelays.value = filteredDelays.value.map((delay) => delay.id);
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
        ids: selectedDelays.value,
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
        },
    });
}
// Add these functions for import/export
function handleImport(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('csv_file', file);

    const routeName = props.isSuperAdmin ? route('ontime.import.admin') : route('ontime.import', { tenantSlug: props.tenantSlug });

    router.post(routeName, formData, {
        onSuccess: () => {
            successMessage.value = 'Delays imported successfully';
            event.target.value = ''; // Reset the file input
        },
        onError: (errors) => {
            console.error(errors);
            event.target.value = ''; // Reset the file input
        },
    });
}

function exportCSV() {
    // Check if there are any delays before exporting
    if (props.delays.data.length === 0) {
        errorMessage.value = 'No delay data found to export.';
        return;
    }

    // Submit the hidden form to trigger the download
    if (exportForm.value) {
        exportForm.value.submit();
    }
}
// Computed property for export URL
const exportUrl = computed(() => {
    return props.tenantSlug ? route('ontime.export', { tenantSlug: props.tenantSlug }) : route('ontime.export.admin');
});

// Add these to the existing script setup section
const showUploadOptions = ref(false);

// Computed property for template URL
const templateUrl = computed(() => {
    return '/storage/upload-data-temps/Delays Template.csv';
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

// Remove the separate onUnmounted hook since it's now inside onMounted
</script>
