<template>
  <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
    <Head title="Repair Orders"/>
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
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
  <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Repair Orders</h1>
  <div class="flex flex-wrap gap-3">
    <Button @click="openCreateModal" variant="default">
      <Icon name="plus" class="mr-2 h-4 w-4" />
      Create New Repair Order
    </Button>
    <!-- Add Delete Selected button -->
    <Button 
      v-if="selectedRepairOrders.length > 0" 
      @click="confirmDeleteSelected()" 
      variant="destructive"
    >
      <Icon name="trash" class="mr-2 h-4 w-4" />
      Delete Selected ({{ selectedRepairOrders.length }})
    </Button>
    <!-- Manage Areas of Concern button - only for SuperAdmin -->
    <Button v-if="SuperAdmin" @click="openAreasOfConcernModal" variant="outline">
      <Icon name="settings" class="mr-2 h-4 w-4" />
      Manage Areas of Concern
    </Button>
    
    <!-- Manage Vendors button - only for SuperAdmin -->
    <Button v-if="SuperAdmin" @click="openVendorsModal" variant="outline">
      <Icon name="settings" class="mr-2 h-4 w-4" />
      Manage Vendors
    </Button>
    <!-- Manage WO Statuses button - only for SuperAdmin -->
  <Button v-if="SuperAdmin" @click="openWoStatusesModal" variant="outline">
    <Icon name="settings" class="mr-2 h-4 w-4" />
    Manage WO Statuses
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
          <a :href="templateUrl" download="Repair Orders Template.csv" class="block px-4 py-2 text-sm hover:bg-muted">
            Download Template
          </a>
        </div>
      </div>
    </div>
    <Button @click.prevent="exportCSV" variant="outline">
      <Icon name="download" class="mr-2 h-4 w-4" />
      Download CSV
    </Button>
  </div>
</div>
<!-- Alert for Canceled QS Invoices -->
<div v-if="hasCanceledQSInvoices" class="mb-6">
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 p-4 rounded-md shadow-sm">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-800 flex items-center justify-center">
                <Icon name="triangleAlert" class="h-6 w-6 text-red-600 dark:text-red-300" />
              </div>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-medium text-red-800 dark:text-red-300">Attention Required</h3>
              <div class="mt-1 text-sm text-red-700 dark:text-red-200">
                {{ props.canceledQSInvoices?.length || 0 }} invoices found with canceled WO status but still on QS. These need immediate attention.
              </div>
              <div class="mt-2">
                <Button @click="showCanceledQSInvoicesDialog = true" variant="destructive" size="sm">
                  View Details
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-4">
          <div class="flex flex-col gap-2">
            <div class="flex flex-wrap gap-2">
              <Button 
                @click="selectDateFilter('yesterday')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'yesterday'}"
              >
                Yesterday
              </Button>
              <Button 
                @click="selectDateFilter('current-week')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'current-week'}"
              >
              WTD
              </Button>
              <Button 
                @click="selectDateFilter('6w')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === '6w'}"
              >
                6 Weeks
              </Button>
              <Button 
                @click="selectDateFilter('quarterly')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'quarterly'}"
              >
                Quarterly
              </Button>
              <Button 
                @click="selectDateFilter('full')" 
                variant="outline"
                size="sm"
                :class="{'bg-primary/10 text-primary border-primary': activeTab === 'full'}"
              >
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
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-col gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
              <div>
                <Label for="search">Search</Label>
                <Input 
                  id="search"
                  v-model="filters.search" 
                  type="text" 
                  placeholder="Search by RO#, Invoice, etc."
                  @input="debounceSearch"
                />
              </div>
              <div>
                <Label for="vendor_filter">Vendor</Label>
                <select 
                  id="vendor_filter"
                  v-model="filters.vendor_id" 
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm"
                  @change="applyFilters"
                >
                  <option value="">All Vendors</option>
                  <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                    {{ vendor.vendor_name }}
                    <span v-if="vendor.deleted_at">(Deleted)</span>
                  </option>
                </select>
              </div>
              <div>
                <Label for="status_filter">Status</Label>
                <select 
                  id="status_filter"
                  v-model="filters.status" 
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm"
                  @change="applyFilters"
                >
                <option value="">All Statuses</option>
<option disabled value="">Select status</option>
<option 
  v-for="status in woStatuses" 
  :key="status.id" 
  :value="status.name"
>
  {{ status.name }} 
  <span v-if="status.deleted_at">(Deleted)</span>
</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end">
              <Button @click="resetFilters" variant="outline" size="sm">
                <Icon name="x" class="mr-2 h-4 w-4" />
                Reset Filters
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
        <!-- Outstanding Invoices Filter -->
  <div class="bg-card rounded-lg border shadow-sm p-4 mb-6" v-if="!SuperAdmin">
    <h3 class="text-lg font-semibold mb-4">Outstanding Invoices Filter</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <Label for="min-invoice-amount">Minimum Invoice Amount ($)</Label>
        <Input 
          id="min-invoice-amount" 
          v-model="minInvoiceAmount" 
          type="number" 
          min="0" 
          step="100"
          placeholder="Enter minimum amount" 
          class="mt-1"
        />
      </div>
      <div>
        <Label for="outstanding-since">Outstanding Since</Label>
        <Input 
          id="outstanding-since" 
          v-model="outstandingDate" 
          type="date" 
          class="mt-1"
        />
      </div>
    </div>
    <div class="mt-4 flex justify-end">
      <Button @click="applyOutstandingInvoices">Apply Filter</Button>
    </div>
  </div>
<!-- Outstanding Invoices Section -->
<div v-if="outstandingInvoices && outstandingInvoices.length > 0" class="bg-card rounded-lg border shadow-sm mb-6">
    <div class="p-4 border-b flex justify-between items-center">
      <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
      <Badge variant="outline" class="text-sm">{{ outstandingInvoices.length }} invoices</Badge>
    </div>
    <div class="p-4">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>RO Number</TableHead>
            <TableHead>Vendor</TableHead>
            <TableHead>Week</TableHead>
            <TableHead class="text-right">Amount</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="invoice in outstandingInvoices" :key="invoice.ro_number">
            <TableCell>{{ invoice.ro_number }}</TableCell>
            <TableCell>{{ invoice.vendor_name }}</TableCell>
            <TableCell>W{{ invoice.week_number }}/{{ invoice.year }}</TableCell>
            <TableCell class="text-right">{{ formatCurrency(invoice.invoice_amount) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
  <div v-else-if="showOutstandingInvoicesSection" class="bg-card rounded-lg border shadow-sm mb-6">
    <div class="p-4 border-b">
      <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
    </div>
    <div class="p-4 text-center text-muted-foreground">
      No outstanding invoices match the current criteria
    </div>
  </div>
      <!-- Repair Orders Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto bg-background dark:bg-background border-t border-border">
            <Table class="relative h-[600px] overflow-auto">
              <TableHeader>
                <TableRow class="sticky top-0 bg-background border-b z-10 hover:bg-background">
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
                  <TableHead class="whitespace-nowrap cursor-pointer" @click="sortBy('ro_number')">
                    <div class="flex items-center">
                      RO#
                      <SortIndicator :column="'ro_number'" :sortColumn="sortColumn" :sortDirection="sortDirection" />
                    </div>
                  </TableHead>
                  <!-- Add Tenant column for SuperAdmin -->
                  <TableHead v-if="SuperAdmin" class="whitespace-nowrap">Company Name</TableHead>
                  <TableHead class="whitespace-nowrap cursor-pointer" @click="sortBy('ro_open_date')">
                    <div class="flex items-center">
                      Open Date
                      <SortIndicator :column="'ro_open_date'" :sortColumn="sortColumn" :sortDirection="sortDirection" />
                    </div>
                  </TableHead>
                  <TableHead class="whitespace-nowrap cursor-pointer" @click="sortBy('ro_close_date')">
                    <div class="flex items-center">
                      Close Date
                      <SortIndicator :column="'ro_close_date'" :sortColumn="sortColumn" :sortDirection="sortDirection" />
                    </div>
                  </TableHead>
                  <TableHead class="whitespace-nowrap">Truck</TableHead>
                  <TableHead class="whitespace-nowrap">Vendor</TableHead>
                  <TableHead class="whitespace-nowrap">Areas of Concern</TableHead>
                  <TableHead class="whitespace-nowrap">WO#</TableHead>
                  <TableHead class="whitespace-nowrap">WO Status</TableHead>
                  <TableHead class="whitespace-nowrap">Invoice</TableHead>
                  <TableHead class="whitespace-nowrap">Invoice Amount</TableHead>
                  <TableHead class="whitespace-nowrap">Invoice Received</TableHead>
                  <TableHead class="whitespace-nowrap">On QS</TableHead>
                  <TableHead class="whitespace-nowrap">QS Invoice Date</TableHead>
                  <TableHead class="whitespace-nowrap">Disputed</TableHead>
                  <TableHead class="whitespace-nowrap">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="repairOrders.data.length === 0">
                  <TableCell :colspan="SuperAdmin ? 16 : 15" class="text-center py-8">
                    No repair orders found.
                  </TableCell>
                </TableRow>
                <TableRow v-for="order in repairOrders.data" :key="order.id" class="hover:bg-muted/50">
                  <!-- Add checkbox for selecting individual row -->
            <TableCell class="text-center">
              <input 
                type="checkbox" 
                :value="order.id" 
                v-model="selectedRepairOrders"
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
            </TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.ro_number }}</TableCell>
                  <!-- Add Tenant cell for SuperAdmin -->
                  <TableCell v-if="SuperAdmin" class="whitespace-nowrap">{{ order.tenant?.name ?? '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ formatDate(order.ro_open_date) }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.ro_close_date ? formatDate(order.ro_close_date) : 'N/A' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.truck?.truckid ?? '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">
                    {{ order.vendor?.vendor_name ?? '—' }}
                    <span v-if="order.vendor?.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                  </TableCell>
                  <TableCell class="whitespace-nowrap">
  <div v-if="order.areas_of_concern && order.areas_of_concern.length > 0">
    <span v-for="(area, index) in order.areas_of_concern" :key="area.id" class="inline-block">
      {{ area.concern }}
      <span v-if="area.deleted_at" class="text-xs text-red-500">(Deleted)</span>
      <span v-if="index < order.areas_of_concern.length - 1">, </span>
    </span>
  </div>
  <span v-else>—</span>
</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.wo_number }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.wo_status?.name || '—' }}</TableCell>                  <TableCell class="whitespace-nowrap">{{ order.invoice }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ formatCurrency(order.invoice_amount) }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.invoice_received ? 'Yes' : 'No' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.on_qs ? order.on_qs.charAt(0).toUpperCase() + order.on_qs.slice(1) : 'No' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.qs_invoice_date ? formatDate(order.qs_invoice_date) : 'N/A' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ order.disputed ? 'Yes' : 'No' }}</TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Button @click="openEditModal(order)" variant="warning" size="sm">
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>
                      <Button @click="deleteOrder(order.id)" variant="destructive" size="sm">
                        <Icon name="trash" class="mr-1 h-4 w-4" />
                        Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <!-- Pagination -->
          <div class="bg-muted/20 px-4 py-3 border-t" v-if="repairOrders.links">
            <div class="flex justify-between items-center">
              <div class="text-sm text-muted-foreground">
                Showing {{ repairOrders.data.length }} entries
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
                    v-for="link in repairOrders.links" 
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

      <!-- Modal for Create/Edit Repair Order -->
      <Dialog v-model:open="showModal">
        <DialogContent class="sm:max-w-4xl">
          <DialogHeader>
            <DialogTitle>{{ formTitle }}</DialogTitle>
            <DialogDescription>
              Fill in the details to {{ formAction.toLowerCase() }} a repair order.
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="submitForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">
            <!-- For SuperAdmin: Tenant Dropdown -->
            <div v-if="SuperAdmin" class="col-span-2">
              <Label for="tenant">Company Name</Label>
              <div class="relative">
                <select id="tenant" v-model="form.tenant_id" required class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none">
                  <option disabled value="">Select a tenant</option>
                  <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                    {{ tenant.name }}
                  </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- RO# -->
            <div>
              <Label for="ro_number">RO#</Label>
              <Input id="ro_number" v-model="form.ro_number" type="text" required />
            </div>
            
            <!-- RO Open Date -->
            <div>
              <Label for="ro_open_date">RO Open Date</Label>
              <Input id="ro_open_date" v-model="form.ro_open_date" type="date" required />
            </div>
            
            <!-- RO Close Date -->
            <div>
              <Label for="ro_close_date">RO Close Date</Label>
              <Input id="ro_close_date" v-model="form.ro_close_date" type="date" />
            </div>
            
            <!-- Truck -->
            <div>
              <Label for="truck_id">Truck</Label>
              <div class="relative">
                <select id="truck_id" v-model="form.truck_id" required class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none">
                  <option disabled value="">Select a truck</option>
                  <option v-for="truck in trucks" :key="truck.id" :value="truck.id">
                    {{ truck.truckid }}
                  </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- Areas Of Concern (improved multi-select) -->
            <div class="col-span-2">
              <Label for="area_of_concerns">Areas Of Concern</Label>
              <div class="border border-input rounded-md p-2 bg-background">
                <div class="flex flex-wrap gap-2 mb-2">
                  <div v-for="selectedId in form.area_of_concerns" :key="selectedId" 
                       class="bg-primary/10 text-primary px-2 py-1 rounded-md flex items-center text-sm">
                    {{ areasOfConcern.find(a => a.id === selectedId)?.concern }}
                    <span v-if="areasOfConcern.find(a => a.id === selectedId)?.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                    <button type="button" @click="removeAreaOfConcern(selectedId)" class="ml-1 text-primary hover:text-primary/80">
                      <Icon name="x" class="h-3 w-3" />
                    </button>
                  </div>
                </div>
                <div class="relative">
                  <select 
                    id="area_of_concerns_select" 
                    @change="addAreaOfConcern($event)" 
                    class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
                  >
                    <option value="">Select an area of concern to add</option>
                    <option 
                      v-for="area in availableAreasOfConcern" 
                      :key="area.id" 
                      :value="area.id"
                      :disabled="area.deleted_at"
                    >
                      {{ area.concern }} {{ area.deleted_at ? '(Deleted)' : '' }}
                    </option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Repairs Made -->
            <div class="col-span-2">
              <Label for="repairs_made">Repairs Made</Label>
              <textarea id="repairs_made" v-model="form.repairs_made"  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
            </div>
            
            <!-- Vendor -->
            <div>
              <Label for="vendor_id">Vendor</Label>
              <div class="relative">
                <select id="vendor_id" v-model="form.vendor_id" required class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none">
                  <option disabled value="">Select a vendor</option>
                  <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id" :disabled="vendor.deleted_at">
                    {{ vendor.vendor_name }} {{ vendor.deleted_at ? '(Deleted)' : '' }}
                  </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- WO# -->
            <div>
              <Label for="wo_number">WO#</Label>
              <Input id="wo_number" v-model="form.wo_number" type="text"  />
            </div>
            
            <!-- WO Status -->
            <div>
  <Label for="wo_status_id">WO Status</Label>
  <div class="relative">
    <select id="wo_status_id" v-model="form.wo_status_id" required class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none">
      <option disabled value="">Select status</option>
      <option v-for="status in woStatuses" :key="status.id" :value="status.id" :disabled="status.deleted_at">
        {{ status.name }} {{ status.deleted_at ? '(Deleted)' : '' }}
      </option>
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
      <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </div>
  </div>
</div>
            
            <!-- Invoice -->
            <div>
              <Label for="invoice">Invoice</Label>
              <Input id="invoice" v-model="form.invoice" type="text"  />
            </div>
            
            <!-- Invoice Amount -->
            <div>
              <Label for="invoice_amount">Invoice Amount</Label>
              <Input id="invoice_amount" v-model="form.invoice_amount" type="number" step="0.01" />
            </div>
            
            <!-- Invoice Received -->
            <div>
              <Label for="invoice_received">Invoice Received</Label>
              <div class="flex items-center space-x-2">
                <input 
                  id="invoice_received" 
                  v-model="form.invoice_received" 
                  type="checkbox" 
                  class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
                <label for="invoice_received" class="text-sm">Yes</label>
              </div>
            </div>
            
            <!-- On QS -->
            <div>
              <Label for="on_qs">On QS</Label>
              <div class="relative">
                <select 
                  id="on_qs" 
                  v-model="form.on_qs" 
                  required 
                  class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none"
                >
                  <option value="yes">Yes</option>
                  <option value="no">No</option>
                  <option value="not expected">Not Expected</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- QS Invoice Date -->
            <div>
              <Label for="qs_invoice_date">QS Invoice Date</Label>
              <Input id="qs_invoice_date" v-model="form.qs_invoice_date" type="date" />
            </div>
            
            <!-- Disputed - Only show when editing -->
            <div v-if="formAction === 'Update'">
              <Label for="disputed">Disputed?</Label>
              <div class="relative">
                <select id="disputed" v-model="form.disputed" required class="flex h-10 w-full items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 appearance-none">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- Dispute Outcome - Only show when editing -->
            <div class="col-span-2" v-if="formAction === 'Update'">
              <Label for="dispute_outcome">Dispute Outcome</Label>
              <textarea id="dispute_outcome" v-model="form.dispute_outcome" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
            </div>
            
            <DialogFooter class="col-span-2 mt-4">
              <Button type="button" @click="closeModal" variant="outline">Cancel</Button>
              <Button type="submit" variant="default">{{ formAction }}</Button>
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
              Are you sure you want to delete this repair order? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button type="button" @click="showDeleteModal = false" variant="outline">Cancel</Button>
            <Button type="button" @click="confirmDelete" variant="destructive">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Modal for Areas of Concern Management -->
      <Dialog v-model:open="showAreasOfConcernModal">
        <DialogContent class="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Manage Areas of Concern</DialogTitle>
            <DialogDescription>
              Add or remove areas of concern for repair orders.
            </DialogDescription>
          </DialogHeader>
          
          <div class="space-y-6">
            <!-- Add new area of concern form -->
            <form @submit.prevent="submitAreaOfConcernForm" class="space-y-4">
              <div class="space-y-2">
                <Label for="concern">Area of Concern</Label>
                <Input id="concern" v-model="areaOfConcernForm.concern" required />
              </div>
              
              <Button type="submit" class="w-full">Add Area of Concern</Button>
            </form>
            
            <!-- List of existing areas of concern with fixed height and scrolling -->
            <div class="border rounded-md">
              <div class="max-h-[300px] overflow-y-auto">
                <Table>
                  <TableHeader class="sticky top-0 bg-background z-10">
                    <TableRow>
                      <TableHead>Area of Concern</TableHead>
                      <TableHead class="w-20">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-if="areasOfConcern.length === 0">
                      <TableCell colspan="2" class="text-center py-4">No areas of concern found.</TableCell>
                    </TableRow>
                    <TableRow v-for="area in areasOfConcern" :key="area.id">
                      <TableCell>
                        {{ area.concern }}
                        <span v-if="area.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                      </TableCell>
                      <TableCell>
                        <div class="flex space-x-2">
                          <Button v-if="area.deleted_at" @click="restoreAreaOfConcern(area.id)" variant="outline" size="sm">
                            <Icon name="undo" class="h-4 w-4" />
                          </Button>
                          <Button v-if="!area.deleted_at" @click="deleteAreaOfConcern(area.id)" variant="destructive" size="sm">
                            <Icon name="trash" class="h-4 w-4" />
                          </Button>
                          <Button v-if="area.deleted_at" @click="forceDeleteAreaOfConcern(area.id)" variant="destructive" size="sm">
                            <Icon name="x" class="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </div>
          
          <DialogFooter>
            <Button @click="showAreasOfConcernModal = false" variant="outline">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Vendors Management Modal -->
      <Dialog v-model:open="showVendorsModal">
        <DialogContent class="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Manage Vendors</DialogTitle>
            <DialogDescription>
              Add or remove vendors for repair orders.
            </DialogDescription>
          </DialogHeader>
          
          <div class="space-y-6">
            <!-- Add new vendor form -->
            <form @submit.prevent="submitVendorForm" class="space-y-4">
              <div class="space-y-2">
                <Label for="vendor_name">Vendor Name</Label>
                <Input id="vendor_name" v-model="vendorForm.vendor_name" required />
              </div>
              
              <Button type="submit" class="w-full">Add Vendor</Button>
            </form>
            
            <!-- List of existing vendors -->
            <div class="border rounded-md overflow-hidden">
              <div class="max-h-[300px] overflow-y-auto">
                <Table>
                  <TableHeader class="sticky top-0 bg-background z-10">
                    <TableRow class="sticky top-0 bg-background border-b z-10">
                      <TableHead>Vendor Name</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-if="vendors.length === 0">
                      <TableCell colspan="2" class="text-center py-4">No vendors found.</TableCell>
                    </TableRow>
                    <TableRow v-for="vendor in vendors" :key="vendor.id">
                      <TableCell>
                        {{ vendor.vendor_name }}
                        <span v-if="vendor.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                      </TableCell>
                      <TableCell>
                        <div class="flex space-x-2">
                          <Button v-if="vendor.deleted_at" @click="restoreVendor(vendor.id)" variant="outline" size="sm">
                            <Icon name="undo" class="h-4 w-4" />
                          </Button>
                          <Button v-if="!vendor.deleted_at" @click="deleteVendor(vendor.id)" variant="destructive" size="sm">
                            <Icon name="trash" class="h-4 w-4" />
                          </Button>
                          <Button v-if="vendor.deleted_at" @click="forceDeleteVendor(vendor.id)" variant="destructive" size="sm">
                            <Icon name="x" class="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </div>
          
          <DialogFooter>
            <Button @click="showVendorsModal = false" variant="outline">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <Dialog v-model:open="showDeleteSelectedModal">
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Confirm Bulk Deletion</DialogTitle>
      <DialogDescription>
        Are you sure you want to delete {{ selectedRepairOrders.length }} repair orders? This action cannot be undone.
      </DialogDescription>
    </DialogHeader>
    <DialogFooter class="mt-4">
      <Button type="button" @click="showDeleteSelectedModal = false" variant="outline">
        Cancel
      </Button>
      <Button type="button" @click="deleteSelectedRepairOrders()" variant="destructive">
        Delete Selected
      </Button>
    </DialogFooter>
  </DialogContent>
</Dialog>
<!-- Add the WO Statuses Management Modal -->
<Dialog v-model:open="showWoStatusesModal">
  <DialogContent class="sm:max-w-[600px]">
    <DialogHeader>
      <DialogTitle>Manage Work Order Statuses</DialogTitle>
      <DialogDescription>
        Add or remove work order statuses for repair orders.
      </DialogDescription>
    </DialogHeader>
    
    <div class="space-y-6">
      <!-- Add new WO status form -->
      <form @submit.prevent="submitWoStatusForm" class="space-y-4">
        <div class="space-y-2">
          <Label for="status_name">Status Name</Label>
          <Input id="status_name" v-model="woStatusForm.name" required />
        </div>
        
        <Button type="submit" class="w-full">Add Work Order Status</Button>
      </form>
      
      <!-- List of existing WO statuses -->
      <div class="border rounded-md overflow-hidden">
        <div class="max-h-[300px] overflow-y-auto">
          <Table>
            <TableHeader class="sticky top-0 bg-background z-10">
              <TableRow class="sticky top-0 bg-background border-b z-10">
                <TableHead>Status Name</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="woStatuses.length === 0">
                <TableCell colspan="2" class="text-center py-4">No work order statuses found.</TableCell>
              </TableRow>
              <TableRow v-for="status in woStatuses" :key="status.id">
                <TableCell>
                  {{ status.name }}
                  <span v-if="status.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-2">
                    <Button v-if="status.deleted_at" @click="restoreWoStatus(status.id)" variant="outline" size="sm">
                      <Icon name="undo" class="h-4 w-4" />
                    </Button>
                    <Button v-if="!status.deleted_at" @click="deleteWoStatus(status.id)" variant="destructive" size="sm">
                      <Icon name="trash" class="h-4 w-4" />
                    </Button>
                    <Button v-if="status.deleted_at" @click="forceDeleteWoStatus(status.id)" variant="destructive" size="sm">
                      <Icon name="x" class="h-4 w-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
    
    <DialogFooter>
      <Button @click="showWoStatusesModal = false" variant="outline">Close</Button>
    </DialogFooter>
  </DialogContent>
</Dialog>
    </div>
    <!-- Dialog for Canceled QS Invoices -->
    <Dialog v-model:open="showCanceledQSInvoicesDialog">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <Icon name="alert-triangle" class="h-5 w-5 text-red-600" />
            Canceled Invoices on QS
          </DialogTitle>
          <DialogDescription>
            These invoices have a canceled WO status but are still marked as on QS. Please review and take appropriate action.
          </DialogDescription>
        </DialogHeader>
        
        <div class="max-h-[60vh] overflow-y-auto">
          <Table v-if="props.canceledQSInvoices?.length">
            <TableHeader>
              <TableRow>
                <TableHead>RO Number</TableHead>
                <TableHead>Vendor</TableHead>
                <TableHead class="text-right">Amount</TableHead>
                <TableHead>Week</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="invoice in props.canceledQSInvoices" :key="invoice.ro_number" class="hover:bg-red-50 dark:hover:bg-red-900/30">
                <TableCell class="font-medium">
                  <div class="flex items-center gap-2">
                    <Icon name="alert-triangle" class="h-4 w-4 text-red-600 dark:text-red-400" />
                    {{ invoice.ro_number }}
                  </div>
                </TableCell>
                <TableCell>{{ invoice.vendor_name }}</TableCell>
                <TableCell class="text-right">{{ formatCurrency(invoice.invoice_amount) }}</TableCell>
                <TableCell>W{{ invoice.week_number }}/{{ invoice.year }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <div v-else class="text-center py-8 text-muted-foreground">
            No canceled QS invoices found.
          </div>
        </div>
        
        <DialogFooter>
          <Button @click="showCanceledQSInvoicesDialog = false" variant="outline">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useForm, router, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Icon from '@/components/Icon.vue'
import SortIndicator from '@/components/SortIndicator.vue'
import { 
  Button, Card, CardContent, CardHeader, CardTitle, Table, TableHeader, TableBody, TableRow, TableCell, TableHead,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, 
  Label, Input, Alert, AlertTitle, AlertDescription 
} from '@/components/ui'
import debounce from 'lodash/debounce'

const props = defineProps({
  repairOrders: { type: Object, default: () => ({ data: [], links: [] }) },
  tenantSlug: { type: String, default: null },
  SuperAdmin: { type: Boolean, default: false },
  tenants: { type: Array, default: () => [] },
  trucks: { type: Array, default: () => [] },
  vendors: { type: Array, default: () => [] },
  areasOfConcern: { type: Array, default: () => [] },
  dateRange: { type: Object, default: null },
  dateFilter: { type: String, default: 'yesterday' },
  woStatuses: { type: Array, default: () => [] },
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
  canceledQSInvoices: {
    type: Array,
    default: () => []
  },
  initialMinInvoiceAmount: {
  type: [Number, String, null],
  default: null
},
initialOutstandingDate: {
  type: [String, null],
  default: null
},
outstandingInvoices: { type: Array, default: () => [] },
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
// Define breadcrumbs for the layout
const breadcrumbs = [
  {
    title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
    href: props.tenantSlug
      ? route('dashboard', { tenantSlug: props.tenantSlug })
      : route('admin.dashboard')
  },
  {
    title: 'Repair Orders',
    href: props.tenantSlug
     ? route('repair_orders.index', { tenantSlug: props.tenantSlug })
      : route('repair_orders.index.admin')
  }
]
const woStatuses = ref(props.woStatuses || []);
const showWoStatusesModal = ref(false);
const woStatusForm = ref({
  name: '',
});
// State variables
const errorMessage = ref('');
const successMessage = ref('')
const showModal = ref(false)
const showDeleteModal = ref(false)
const showAreasOfConcernModal = ref(false)
const showVendorsModal = ref(false)
const formTitle = ref('Create Repair Order')
const formAction = ref('Create')
const repairOrderToDelete = ref(null)
const sortColumn = ref('ro_number')
const sortDirection = ref('asc')
const activeTab = ref(props.dateFilter || 'full')
const perPage = ref(10)
// Add these new refs
const selectedRepairOrders = ref([]);
const showDeleteSelectedModal = ref(false);

// Add this computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
  return props.repairOrders.data.length > 0 && 
         props.repairOrders.data.every(order => selectedRepairOrders.value.includes(order.id));
});

// Add these new functions for bulk selection and deletion
// Add these new functions for bulk selection and deletion
function toggleSelectAll(event) {
  if (event.target.checked) {
    // Select all visible repair orders
    selectedRepairOrders.value = props.repairOrders.data.map(order => order.id);
  } else {
    // Deselect all
    selectedRepairOrders.value = [];
  }
}

function confirmDeleteSelected() {
  if (selectedRepairOrders.value.length > 0) {
    showDeleteSelectedModal.value = true;
  }
}

function deleteSelectedRepairOrders() {
  const bulkDeleteForm = useForm({
    ids: selectedRepairOrders.value
  });
  
  const routeName = props.SuperAdmin ? 'repair_orders.destroyBulk.admin' : 'repair_orders.destroyBulk';
  const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };
  
  bulkDeleteForm.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = `${selectedRepairOrders.value.length} repair orders deleted successfully.`;
      selectedRepairOrders.value = [];
      showDeleteSelectedModal.value = false;
    },
    onError: (errors) => {
      console.error(errors);
      alert('Error deleting repair orders');
    }
  });
}

// Filters
const filters = ref({
  search: '',
  vendor_id: '',
  status: ''
})

// Form for repair orders
const form = useForm({
  id: null,
  tenant_id: props.SuperAdmin ? '' : null,
  ro_number: '',
  ro_open_date: new Date().toISOString().split('T')[0],
  ro_close_date: '',
  truck_id: '',
  vendor_id: '',
  wo_number: '',
  wo_status_id: '',
  invoice: '',
  invoice_amount: '',
  invoice_received: false,
  on_qs: 'no',
  qs_invoice_date: '',
  disputed: false,
  dispute_outcome: '',
  repairs_made: '',
  area_of_concerns: []
})

// Form for areas of concern
const areaOfConcernForm = useForm({
  concern: '',
})

// Form for vendors
const vendorForm = useForm({
  vendor_name: '',
})

// Computed properties
const availableAreasOfConcern = computed(() => {
  return props.areasOfConcern.filter(area => !form.area_of_concerns.includes(area.id))
})

// Methods for repair orders
const openCreateModal = () => {
  // Completely reset the form to default values
  form.reset()
  // Set tenant_id appropriately for SuperAdmin
  form.tenant_id = props.SuperAdmin ? '' : null
  // Reset boolean fields explicitly to false
  form.invoice_received = false
  form.on_qs = false
  form.disputed = false  // Set disputed to false by default
  form.dispute_outcome = ''  // Set dispute_outcome to empty string by default
  // Clear arrays
  form.area_of_concerns = []
  // Set form mode
  formTitle.value = 'Create Repair Order'
  formAction.value = 'Create'
  showModal.value = true
}

const openEditModal = (order) => {
  // Reset form first to clear any previous data
  form.reset()
  
  // Set all form fields from the order object
  form.id = order.id
  form.tenant_id = order.tenant_id
  form.ro_number = order.ro_number
  form.ro_open_date = order.ro_open_date
  form.ro_close_date = order.ro_close_date || ''
  form.truck_id = order.truck_id
  form.vendor_id = order.vendor_id
  form.wo_number = order.wo_number
  form.wo_status_id = order.wo_status_id;
  form.invoice = order.invoice || ''
  form.invoice_amount = order.invoice_amount || ''
  // Handle boolean values that might come as 0/1 or true/false
  form.invoice_received = Boolean(order.invoice_received)
  form.on_qs = order.on_qs || 'no'
  form.qs_invoice_date = order.qs_invoice_date || ''
  form.disputed = Boolean(order.disputed)
  form.dispute_outcome = order.dispute_outcome || ''
  form.repairs_made = order.repairs_made || ''
  
  // Handle areas of concern with better error checking - use areas_of_concern (snake_case)
  form.area_of_concerns = []
  if (order.areas_of_concern && Array.isArray(order.areas_of_concern)) {
    // Extract just the IDs from the areas_of_concern relationship
    form.area_of_concerns = order.areas_of_concern.map(area => area.id)
  }
  
  // Set form mode
  formTitle.value = 'Edit Repair Order'
  formAction.value = 'Update'
  showModal.value = true
}

const closeModal = () => {
  // When closing the modal, reset the form to avoid data leakage
  form.reset()
  showModal.value = false
}

const submitForm = () => {
  const storeRoute = props.SuperAdmin 
    ? route('repair_orders.store.admin')
    : route('repair_orders.store', props.tenantSlug)
    
  if (formAction.value === 'Update' && form.id) {
    const updateRoute = props.SuperAdmin 
      ? route('repair_orders.update.admin', form.id)
      : route('repair_orders.update', [props.tenantSlug, form.id])
      
    form.put(updateRoute, {
      onSuccess: () => {
        successMessage.value = 'Repair order updated successfully!'
        showModal.value = false
        // Reset form after successful submission
        form.reset()
      }
    })
  } else {
    form.post(storeRoute, {
      onSuccess: () => {
        successMessage.value = 'Repair order created successfully!'
        showModal.value = false
        // Reset form after successful submission
        form.reset()
      }
    })
  }
}

const deleteOrder = (id) => {
  repairOrderToDelete.value = id
  showDeleteModal.value = true
}

const confirmDelete = () => {
  const deleteRoute = props.SuperAdmin 
    ? route('repair_orders.destroy.admin', repairOrderToDelete.value)
    : route('repair_orders.destroy', [props.tenantSlug, repairOrderToDelete.value])
    
  router.delete(deleteRoute, {
    onSuccess: () => {
      showDeleteModal.value = false
      repairOrderToDelete.value = null
    }
  })
}

// Methods for areas of concern
const openAreasOfConcernModal = () => {
  areaOfConcernForm.reset()
  showAreasOfConcernModal.value = true
}

const submitAreaOfConcernForm = () => {
  areaOfConcernForm.post(route('area_of_concerns.store.admin'), {
    onSuccess: () => {
      areaOfConcernForm.reset()
    }
  })
}

const deleteAreaOfConcern = (id) => {
  if (confirm('Are you sure you want to delete this area of concern?')) {
    router.delete(route('area_of_concerns.destroy.admin', id))
  }
}

const restoreAreaOfConcern = (id) => {
  if (confirm('Are you sure you want to restore this area of concern?')) {
    router.post(route('area_of_concerns.restore.admin', id))
  }
}

const forceDeleteAreaOfConcern = (id) => {
  if (confirm('Are you sure you want to permanently delete this area of concern? This action cannot be undone.')) {
    router.delete(route('area_of_concerns.forceDelete.admin', id))
  }
}

// Methods for vendors
const openVendorsModal = () => {
  vendorForm.reset()
  showVendorsModal.value = true
}

const submitVendorForm = () => {
  vendorForm.post(route('vendors.store.admin'), {
    onSuccess: () => {
      vendorForm.reset()
    }
  })
}

const deleteVendor = (id) => {
  if (confirm('Are you sure you want to delete this vendor?')) {
    router.delete(route('vendors.destroy.admin', id))
  }
}

const restoreVendor = (id) => {
  if (confirm('Are you sure you want to restore this vendor?')) {
    router.post(route('vendors.restore.admin', id))
  }
}

const forceDeleteVendor = (id) => {
  if (confirm('Are you sure you want to permanently delete this vendor? This action cannot be undone.')) {
    router.delete(route('vendors.forceDelete.admin', id))
  }
}

// Methods for areas of concern selection
const addAreaOfConcern = (event) => {
  const selectedId = parseInt(event.target.value)
  if (selectedId && !form.area_of_concerns.includes(selectedId)) {
    form.area_of_concerns.push(selectedId)
  }
  event.target.value = ''
}

const removeAreaOfConcern = (id) => {
  form.area_of_concerns = form.area_of_concerns.filter(areaId => areaId !== id)
}

// Pagination and sorting
const visitPage = (url) => {
  if (url) {
    router.visit(url, { preserveState: true })
  }
}

const sortBy = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
  
  const indexRoute = props.SuperAdmin 
    ? route('repair_orders.index.admin')
    : route('repair_orders.index', props.tenantSlug)
  
  router.get(indexRoute, {
    sort: sortColumn.value,
    direction: sortDirection.value,
    ...filters.value
  }, { preserveState: true })
}

// Filtering
const debounceSearch = debounce(() => {
  applyFilters()
}, 500)

const applyFilters = () => {
  const indexRoute = props.SuperAdmin 
    ? route('repair_orders.index.admin')
    : route('repair_orders.index', props.tenantSlug)
    
  router.get(indexRoute, {
    sort: sortColumn.value,
    direction: sortDirection.value,
    ...filters.value
  }, { preserveState: true })
}

const resetFilters = () => {
  filters.value = {
    search: '',
    vendor_id: '',
    status: ''
  }
  applyFilters()
}

// Date filtering
const selectDateFilter = (filter) => {
  activeTab.value = filter
  
  const routeName = props.tenantSlug 
    ? route('repair_orders.index', { tenantSlug: props.tenantSlug }) 
    : route('repair_orders.index.admin')
    
  router.get(routeName, { 
    dateFilter: filter,
    perPage: perPage.value 
  }, { preserveState: true })
}

// Pagination
const changePerPage = () => {
  const routeName = props.tenantSlug 
    ? route('repair_orders.index', { tenantSlug: props.tenantSlug }) 
    : route('repair_orders.index.admin')
    
  router.get(routeName, { 
    dateFilter: activeTab.value,
    perPage: perPage.value 
  }, { preserveState: true })
}

// Import/Export
const handleImport = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const formData = new FormData()
  formData.append('file', file)
  
  const importRoute = props.SuperAdmin 
    ? route('repair_orders.import.admin')
    : route('repair_orders.import', props.tenantSlug)
    
  router.post(importRoute, formData, {
    onSuccess: () => {
      successMessage.value = 'Import completed successfully!'
      event.target.value = null
    },
    onError: (errors) => {
      // Handle import errors if needed
      console.error('Import errors:', errors)
    }
  })
}

const exportCSV = () => {
  if (props.repairOrders.data.length===0) {
    errorMessage.value = "No data available to export";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
    return;
  }
  window.location.href = props.SuperAdmin 
    ? route('repair_orders.export.admin')
    : route('repair_orders.export', props.tenantSlug)
}

// Helper functions
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const parts = dateStr.split('-')
  if (parts.length !== 3) return dateStr
  const [year, month, day] = parts
  return `${Number(month)}/${Number(day)}/${year}`
}

const formatCurrency = (amount) => {
  if (!amount) return '$0.00'
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount)
}

const showUploadOptions = ref(false);

// Computed property for template URL
const templateUrl = computed(() => {
  return '/storage/upload-data-temps/Repair Orders Template.csv';
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

function openWoStatusesModal() {
  showWoStatusesModal.value = true;
  woStatusForm.value = {
    name: '',
  };
}

function submitWoStatusForm() {
  
  router.post(route('wo_statuses.store.admin'), woStatusForm.value, {
    onSuccess: () => {
      woStatusForm.value.name = '';
      successMessage.value = 'Work order status created successfully.';
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
    }
  });
}

function deleteWoStatus(id) {
  if (!confirm('Are you sure you want to delete this work order status?')) return;
  
  router.delete(route('wo_statuses.destroy.admin', id), {
    onSuccess: () => {
      successMessage.value = 'Work order status deleted successfully.';
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
    }
  });
}

function restoreWoStatus(id) {
  router.post(route('wo_statuses.restore.admin', id), {}, {
    onSuccess: () => {
      successMessage.value = 'Work order status restored successfully.';
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
    }
  });
}

function forceDeleteWoStatus(id) {
  if (!confirm('Are you sure you want to permanently delete this work order status? This action cannot be undone.')) return;
  
  router.delete(route('wo_statuses.forceDelete.admin', id), {
    onSuccess: () => {
      successMessage.value = 'Work order status permanently deleted.';
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
    }
  });
}
const hasCanceledQSInvoices = computed(() => {
  return props.canceledQSInvoices.length > 0;
});
const showCanceledQSInvoicesDialog = ref(false);
const minInvoiceAmount = ref(props.initialMinInvoiceAmount || null);
const outstandingDate = ref(props.initialOutstandingDate || null);
const showOutstandingInvoicesSection = ref(true);

// Add this function to handle the filter application
const applyOutstandingInvoices = () => {
  // Visit the current route with the filter parameters
  router.visit(route('repair_orders.index', {
    tenantSlug: props.tenantSlug,
    dateFilter: activeTab.value,
    minInvoiceAmount: minInvoiceAmount.value || null,
    outstandingDate: outstandingDate.value || null
  }), {
    preserveState: true,
    preserveScroll: true,
    only: ['maintenanceData']
  });
};





</script>

<style scoped>
/* Any component-specific styles can go here */
</style>