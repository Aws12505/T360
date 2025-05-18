<template>
  <div class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto p-2 md:p-4 lg:p-6 space-y-6">
    <!-- Alerts -->
    <Alert v-if="successMessage" variant="success" class="animate-in fade-in duration-300">
      <AlertTitle class="flex items-center gap-2">
        <Icon name="check_circle" class="h-5 w-5 text-green-500" />
        Success
      </AlertTitle>
      <AlertDescription>{{ successMessage }}</AlertDescription>
    </Alert>
    <Alert v-if="errorMessage" variant="destructive" class="animate-in fade-in duration-300">
      <AlertTitle class="flex items-center gap-2">
        <Icon name="alert_circle" class="h-5 w-5" />
        Error
      </AlertTitle>
      <AlertDescription>{{ errorMessage }}</AlertDescription>
    </Alert>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6 space-y-2 sm:space-y-0">
      <div class="flex items-center gap-3">
        <Icon name="clipboard-list" class="h-6 w-6 text-primary hidden sm:block" />
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-200">Repair Orders</h1>
      </div>
      <div class="flex flex-wrap gap-3">
        <Button @click="openCreateModal" variant="default" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" /> Create New
        </Button>
        <Button v-if="selectedIds.length" @click="confirmBulkDelete" variant="destructive" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" /> Delete Selected ({{ selectedIds.length }})
        </Button>
        <Button v-if="isAdmin" @click="openAreasModal" variant="outline" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="settings" class="mr-1 h-4 w-4 md:mr-2" /> Areas
        </Button>
        <Button v-if="isAdmin" @click="openVendorsModal" variant="outline" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="settings" class="mr-1 h-4 w-4 md:mr-2" /> Vendors
        </Button>
        <Button v-if="isAdmin" @click="openStatusModal" variant="outline" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="settings" class="mr-1 h-4 w-4 md:mr-2" /> Statuses
        </Button>
        <div class="relative">
          <Button @click="showUpload = !showUpload" variant="secondary" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
            <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2" /> Upload CSV
            <Icon name="chevron-down" class="ml-2 h-4 w-4" />
          </Button>
          <div v-if="showUpload" class="absolute right-0 mt-1 w-48 rounded-md border bg-background shadow-md z-10 animate-in fade-in duration-200">
            <label class="block cursor-pointer px-4 py-2 text-sm hover:bg-muted transition-colors">
              <span>Upload CSV</span><input type="file" class="hidden" @change="importCsv" accept=".csv"/>
            </label>
            <a :href="templateUrl" download class="block px-4 py-2 text-sm hover:bg-muted transition-colors">Download Template</a>
          </div>
        </div>
        <Button @click.prevent="exportCsv" variant="outline" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" /> Download CSV
        </Button>
      </div>
    </div>
    <!-- Canceled QS Invoices Alert -->
    <div v-if="hasCanceledQSInvoices && !SuperAdmin" class="mb-6">
                <div class="rounded-md border-l-4 border-red-500 bg-red-50 p-4 shadow-sm dark:border-red-400 dark:bg-red-900/30">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-800">
                                <Icon name="triangleAlert" class="h-6 w-6 text-red-600 dark:text-red-300" />
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800 dark:text-red-300">Attention Required</h3>
                            <div class="mt-1 text-sm text-red-700 dark:text-red-200">
                                {{ props.canceledQSInvoices?.length || 0 }} invoices found with canceled WO status but still on QS. These need
                                immediate attention.
                            </div>
                            <div class="mt-2">
                                <Button @click="showCanceledQSInvoicesDialog = true" variant="destructive" size="sm"> View Details </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <!-- Date Filter Tabs -->
    <Card class="shadow-sm border bg-card">
      <CardContent class="p-3 md:p-4">
        <div class="flex flex-wrap gap-2">
          <Button
            v-for="opt in dateOptions"
            :key="opt.value"
            size="sm"
            variant="outline"
            :class="{'border-primary bg-primary/10 text-primary': filter.dateFilter===opt.value}"
            @click="selectDate(opt.value)">
            {{ opt.label }}
          </Button>
        </div>
        <div v-if="dateRange" class="text-sm text-muted-foreground mt-2">
          <span v-if="dateFilter === 'yesterday' && dateRange.start"> Showing data from {{ formatDate(dateRange.start) }} </span>
          <span v-else-if="dateRange.start && dateRange.end">
            Showing data from {{ formatDate(dateRange.start) }} to {{ formatDate(dateRange.end) }}
          </span>
          <span v-else>
            {{ dateRange.label }}
          </span>
          <span v-if="weekNumberText" class="ml-1">({{ weekNumberText }})</span>
        </div>
      </CardContent>
    </Card>
    <div
                class="mx-auto mb-6 grid max-w-[95vw] grid-cols-1 gap-4 md:max-w-[64vw] md:grid-cols-2 lg:max-w-full"
                v-if="(dateFilter === 'quarterly' || dateFilter === '6w') && !SuperAdmin"
            >
                <!-- Panel: Areas of Concern -->
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="text-lg font-semibold">Top 5 Frequent Repairs</h3>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Parts:</span>
                                <span class="text-sm">Work Orders:</span>
                            </div>
                            <li v-for="(area, index) in topAreasOfConcern" :key="index" class="flex items-center justify-between pr-4">
                                <span class="text-sm">{{ area.concern }}</span>
                                <Badge variant="outline">{{ area.count }}</Badge>
                            </li>
                            <li v-if="topAreasOfConcern.length === 0" class="text-center text-muted-foreground">No data available</li>
                        </ul>
                    </div>
                </div>

                <!--  Panel: Work Orders by Truck -->
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="text-lg font-semibold">Top 5 Frequently Repaired Tractors</h3>
                    </div>
                    <div class="p-4">
                        <ul class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Asset ID:</span>
                                <span class="text-sm">Work Orders:</span>
                            </div>
                            <li v-for="(truck, index) in topWorkOrdersByTruck" :key="index" class="flex items-center justify-between pr-4">
                                <span class="text-sm">{{ truck.truckid }}</span>
                                <Badge variant="outline">{{ truck.work_order_count }}</Badge>
                            </li>
                            <li v-if="topWorkOrdersByTruck.length === 0" class="text-center text-muted-foreground">No data available</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Outstanding Invoices Filter -->
    <div
      v-if="!SuperAdmin"
      class="mx-auto mb-6 max-w-[95vw] overflow-x-auto rounded-lg border bg-card p-4 shadow-sm md:max-w-[64vw] lg:max-w-full"
    >
      <h3 class="mb-4 text-lg font-semibold">Outstanding Invoices Filter</h3>
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
          <Label for="min-invoice-amount">Minimum Invoice Amount ($)</Label>
          <Input
            id="min-invoice-amount"
            v-model.number="minInvoiceAmount"
            type="number"
            min="0"
            step="0.01"
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
    </div>

    <!-- Outstanding Invoices Section -->
    <div
      v-if="filteredOutstandingInvoices.length > 0"
      class="mx-auto mb-6 max-w-[95vw] overflow-x-auto rounded-lg border bg-card shadow-sm md:max-w-[64vw] lg:max-w-full"
    >
      <div class="flex items-center justify-between border-b p-4">
        <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
        <div class="flex items-start justify-between">
          <div class="flex flex-col items-center space-y-1">
            <Badge variant="outline" class="text-sm"> {{ filteredOutstandingInvoices.length }} invoices </Badge>
            <Badge variant="outline" class="text-sm"> total: ${{ totalFilteredOutstanding.toFixed(2) }} </Badge>
          </div>
          <Button variant="ghost" size="sm" @click="showOutstandingInvoicesSection = !showOutstandingInvoicesSection" class="ml-4 mt-1">
            {{ showOutstandingInvoicesSection ? 'Hide Invoices' : 'Show Invoices' }}
            <Icon :name="showOutstandingInvoicesSection ? 'chevron-up' : 'chevron-down'" class="ml-2 h-4 w-4" />
          </Button>
        </div>
      </div>
      <div v-if="showOutstandingInvoicesSection" class="mb-6 rounded-lg border bg-card shadow-sm">
        <div class="overflow-x-auto">
          <div class="max-h-60 overflow-y-auto sm:max-h-80 md:max-h-96">
            <Table class="min-w-full">
              <TableHeader>
                <TableRow>
                  <TableHead>RO Number</TableHead>
                  <TableHead>Vendor</TableHead>
                  <TableHead>Week</TableHead>
                  <TableHead class="text-right">Amount</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="invoice in filteredOutstandingInvoices" :key="invoice.ro_number">
                  <TableCell>{{ invoice.ro_number }}</TableCell>
                  <TableCell>{{ invoice.vendor_name }}</TableCell>
                  <TableCell>W{{ invoice.week_number }}/{{ invoice.year }}</TableCell>
                  <TableCell class="text-right">{{ formatCurrency(invoice.invoice_amount) }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="showOutstandingInvoicesSection" class="mb-6 rounded-lg border bg-card shadow-sm">
      <div class="border-b p-4">
        <h3 class="text-lg font-semibold">Outstanding Invoices</h3>
      </div>
      <div class="p-4 text-center text-muted-foreground">No outstanding invoices match the current criteria</div>
    </div>

    <!-- Filters Card -->
    <!-- Filters -->
    <Card class="shadow-sm border">
      <CardHeader class="p-2 md:p-4 lg:p-6 border-b">
        <div class="flex justify-between items-center">
          <CardTitle class="text-lg md:text-xl lg:text-2xl flex items-center gap-2">
            <Icon name="filter" class="h-5 w-5 text-muted-foreground" />
            Filters
          </CardTitle>
          <Button 
            variant="ghost" 
            size="sm" 
            @click="showFilters = !showFilters"
            class="flex items-center gap-1.5 text-muted-foreground hover:text-foreground transition-colors"
          >
            <span class="text-sm hidden sm:inline">{{ showFilters ? 'Hide Filters' : 'Show Filters' }}</span>
            <Icon :name="showFilters ? 'chevron-up' : 'chevron-down'" class="h-4 w-4" />
          </Button>
        </div>
      </CardHeader>
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        leave-active-class="transition-all duration-200 ease-in"
        enter-from-class="opacity-0 max-h-0"
        enter-to-class="opacity-100 max-h-[500px]"
        leave-from-class="opacity-100 max-h-[500px]"
        leave-to-class="opacity-0 max-h-0"
      >
        <CardContent v-if="showFilters" class="p-4 md:p-6 lg:p-8 overflow-hidden">
          <div class="flex flex-col gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 w-full">
              <div>
                <Label for="search" class="flex items-center gap-1.5 mb-2">
                  <Icon name="search" class="h-4 w-4 text-muted-foreground" />
                  Search
                </Label>
                <Input 
                  id="search" 
                  type="text" 
                  v-model="filter.search" 
                  placeholder="RO#, Invoice..." 
                  class="py-1 px-1 md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10" 
                />
              </div>
              <div>
                <Label for="vendor" class="flex items-center gap-1.5 mb-2">
                  <Icon name="building" class="h-4 w-4 text-muted-foreground" />
                  Vendor
                </Label>
                <div class="relative">
                  <select 
                    id="vendor" 
                    v-model="filter.vendor_id" 
                    class="flex h-10 w-full appearance-none items-center rounded-md border bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="">All Vendors</option>
                    <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.vendor_name }}</option>
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
              <div>
                <Label for="status" class="flex items-center gap-1.5 mb-2">
                  <Icon name="activity" class="h-4 w-4 text-muted-foreground" />
                  Status
                </Label>
                <div class="relative">
                  <select 
                    id="status" 
                    v-model="filter.status_id" 
                    class="flex h-10 w-full appearance-none items-center rounded-md border bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="">All Statuses</option>
                    <option v-for="s in woStatuses" :key="s.id" :value="s.id">{{ s.name }}</option>
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
            </div>
            <Button @click="resetFilters" variant="ghost" size="sm" class="self-start">
              <Icon name="rotate_ccw" class="mr-2 h-4 w-4" /> Reset Filters
            </Button>
          </div>
        </CardContent>
      </Transition>
    </Card>

    <!-- Table -->
        <!-- Data Table -->
        <template v-if="hasData">
      <Card class="mx-auto max-w-[95vw] md:max-w-[64vw] lg:max-w-full overflow-x-auto shadow-sm border">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table class="relative h-[500px] overflow-auto">
              <TableHeader>
                <TableRow class="sticky top-0 z-10 border-b bg-background hover:bg-background">
                  <TableHead class="w-12">
                    <div class="flex items-center justify-center">
                      <input 
                        type="checkbox" 
                        :checked="allSelected" 
                        @change="toggleAll" 
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" 
                      />
                    </div>
                  </TableHead>
                  <TableHead @click="sort('ro_number')" class="cursor-pointer font-semibold">
                    <div class="flex items-center space-x-1">
                      RO#
                      <SortIndicator column="ro_number" :sortState="sortState"/>
                    </div>
                  </TableHead>
                  <TableHead v-if="isAdmin" class="font-semibold">Company</TableHead>
                  <TableHead @click="sort('ro_open_date')" class="cursor-pointer font-semibold">
                    <div class="flex items-center space-x-1">
                      Open Date
                      <SortIndicator column="ro_open_date" :sortState="sortState"/>
                    </div>
                  </TableHead>
                  <TableHead @click="sort('ro_close_date')" class="cursor-pointer font-semibold">
                    <div class="flex items-center space-x-1">
                      Close Date
                      <SortIndicator column="ro_close_date" :sortState="sortState"/>
                    </div>
                  </TableHead>
                  <TableHead class="font-semibold">Truck</TableHead>
                  <TableHead class="font-semibold">Vendor</TableHead>
                  <TableHead class="font-semibold">Areas of Concern</TableHead>
                  <TableHead class="font-semibold">WO#</TableHead>
                  <TableHead class="font-semibold">WO Status</TableHead>
                  <TableHead class="font-semibold">Invoice</TableHead>
                  <TableHead class="font-semibold">Amount</TableHead>
                  <TableHead class="font-semibold">Invoice Received</TableHead>
                  <TableHead class="font-semibold">On QS</TableHead>
                  <TableHead class="font-semibold">QS Invoice Date</TableHead>
                  <TableHead class="font-semibold">Disputed</TableHead>
                  <TableHead class="font-semibold">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="!repairOrders.data.length">
                  <TableCell :colspan="isAdmin ? 16 : 15" class="py-8 text-center">
                    <div class="flex flex-col items-center justify-center rounded-lg border bg-muted/20 py-16">
                      <Icon name="database-x" class="h-16 w-16 mx-auto mb-4 opacity-70" />
                      <h2 class="text-lg font-medium">No repair orders found.</h2>
                      <p class="text-muted-foreground mt-2">There is no data to display at this time.</p>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-for="o in repairOrders.data" :key="o.id" class="hover:bg-muted/50 transition-colors">
                  <TableCell>
                    <input
                      type="checkbox"
                      :value="o.id"
                      v-model="selectedIds"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-1 focus:ring-primary"
                    />
                  </TableCell>
                  <TableCell class="whitespace-nowrap font-medium">{{ o.ro_number }}</TableCell>
                  <TableCell v-if="isAdmin" class="whitespace-nowrap">{{ o.tenant?.name || '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ formatDate(o.ro_open_date) }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.ro_close_date ? formatDate(o.ro_close_date) : 'N/A' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.truck?.truckid || '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.vendor?.vendor_name || '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">
                    <span v-if="o.areas_of_concern?.length">
                      <span v-for="(area, idx) in o.areas_of_concern" :key="area.id">
                        {{ area.concern }}<span v-if="idx < o.areas_of_concern.length - 1">, </span>
                      </span>
                    </span>
                    <span v-else>—</span>
                  </TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.wo_number || '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.wo_status?.name || '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.invoice || '—' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ formatCurrency(o.invoice_amount) }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.invoice_received ? 'Yes' : 'No' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.on_qs ? (o.on_qs.charAt(0).toUpperCase() + o.on_qs.slice(1)) : 'No' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.qs_invoice_date ? formatDate(o.qs_invoice_date) : 'N/A' }}</TableCell>
                  <TableCell class="whitespace-nowrap">{{ o.disputed ? 'Yes' : 'No' }}</TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Button size="sm" variant="warning" @click="openEdit(o)" class="h-8 px-2">
                        <Icon name="pencil" class="h-4 w-4" />
                      </Button>
                      <Button size="sm" variant="destructive" @click="deleteOne(o.id)" class="h-8 px-2">
                        <Icon name="trash" class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
        <!-- Pagination -->
<div class="border-t bg-muted/20 px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-2">
  <div class="flex items-center gap-4 text-sm text-muted-foreground">
    <span class="flex items-center gap-1">
      <Icon name="list" class="h-4 w-4" />
      Showing {{ repairOrders.data.length }} of {{ repairOrders.total }} entries
    </span>
    <span class="flex items-center gap-1">
      <Icon name="layout-grid" class="h-4 w-4" />
      Per page:
    </span>
    <div class="relative">
      <select 
        v-model.number="localPerPage" 
        @change="changePerPage"
        class="h-8 appearance-none rounded-md border bg-background px-2 py-1 text-sm focus:ring-2 focus:ring-ring"
      >
        <option v-for="n in [10,25,50,100]" :key="n" :value="n">{{ n }}</option>
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
  <div class="flex space-x-1">
    <Button size="sm" variant="ghost" @click="go(repairOrders.prev_page_url)" :disabled="!repairOrders.prev_page_url" class="flex items-center gap-1">
      <Icon name="chevron-left" class="h-4 w-4" /> Prev
    </Button>
    <Button 
      v-for="link in repairOrders.links.slice(1, -1)" 
      :key="link.label" 
      size="sm" 
      variant="ghost" 
      @click="go(link.url)"
      :disabled="!link.url"
      :class="{'border-primary bg-primary/10 text-primary font-medium':link.active}"
    >
      <span v-html="link.label"></span>
    </Button>
    <Button size="sm" variant="ghost" @click="go(repairOrders.next_page_url)" :disabled="!repairOrders.next_page_url" class="flex items-center gap-1">
      Next <Icon name="chevron-right" class="h-4 w-4" />
    </Button>
  </div>
</div>
      </Card>
    </template>

    <div v-else class="flex flex-col items-center justify-center rounded-lg border bg-muted/20 py-16">
      <Icon name="database-x" class="h-16 w-16 mx-auto mb-4 opacity-70" />
      <h2 class="text-lg font-medium">There is No Data to give Information about.</h2>
    </div>


    <!-- Create/Edit Modal -->
    <Dialog v-model:open="showModal">
  <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
    <DialogHeader class="px-4 sm:px-6 border-b pb-3">
      <div class="flex items-center gap-2">
        <Icon :name="form.id ? 'pencil' : 'plus-circle'" class="h-5 w-5 text-primary" />
        <DialogTitle class="text-lg sm:text-xl font-semibold">{{ formAction }} Repair Order</DialogTitle>
      </div>
      <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">Fill in the details to {{ formAction.toLowerCase() }} a repair order.</DialogDescription>
    </DialogHeader>
    
    <div class="max-h-[70vh] overflow-y-auto px-4 sm:px-6">
      <form @submit.prevent="submitForm" class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 sm:gap-4 sm:p-4">     
        <!-- Company (Admin only) -->
        <div v-if="isAdmin" class="col-span-2 mb-1">
          <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
            <Icon name="building" class="h-4 w-4 text-muted-foreground" />
            Company
          </Label>
          <div class="relative">
            <select v-model="form.tenant_id" required class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm ring-offset-background focus-visible:ring-2">
              <option disabled value="">Select</option>
              <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Basic Information -->
        <div class="col-span-2 border-b pb-1 mb-2 flex items-center gap-2">
          <Icon name="info" class="h-4 w-4 text-primary" />
          <h3 class="text-md font-semibold text-primary">Basic Information</h3>
        </div>
        
        <div class="grid grid-cols-2 gap-3 col-span-2">
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="hash" class="h-4 w-4 text-muted-foreground" />
              RO#
            </Label>
            <Input v-model="form.ro_number" required class="h-9 w-full"/>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="truck" class="h-4 w-4 text-muted-foreground" />
              Truck
            </Label>
            <div class="relative">
              <select v-model="form.truck_id" required class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm">
                <option disabled value="">Select</option>
                <option v-for="t in trucks" :key="t.id" :value="t.id">{{ t.truckid }}</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="calendar" class="h-4 w-4 text-muted-foreground" />
              Open Date
            </Label>
            <Input type="date" v-model="form.ro_open_date" required class="h-9 w-full"/>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="calendar-check" class="h-4 w-4 text-muted-foreground" />
              Close Date
            </Label>
            <Input type="date" v-model="form.ro_close_date" class="h-9 w-full"/>
          </div>
        </div>
        
        <!-- Repair Details -->
        <div class="col-span-2 border-b pb-1 mb-2 mt-1 flex items-center gap-2">
          <Icon name="wrench" class="h-4 w-4 text-primary" />
          <h3 class="text-md font-semibold text-primary">Repair Details</h3>
        </div>
        
        <div class="col-span-2">
          <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
            <Icon name="alert-triangle" class="h-4 w-4 text-muted-foreground" />
            Areas of Concern
          </Label>
          <div class="flex flex-wrap gap-1 mb-2 bg-muted/30 p-2 rounded-md min-h-[40px]">
            <span v-for="id in form.area_of_concerns" :key="id" class="badge bg-primary/10 text-primary px-2 py-1 rounded-md flex items-center">
              {{ areasMap[id] }}<button @click="removeArea(id)" class="ml-1 hover:text-red-500 focus:outline-none">×</button>
            </span>
            <span v-if="!form.area_of_concerns.length" class="text-muted-foreground text-sm italic">No areas selected</span>
          </div>
          <div class="relative">
            <select @change="addArea($event)" class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm">
              <option value="">Select an area to add</option>
              <option v-for="a in availableAreas" :key="a.id" :value="a.id">{{ a.concern }}</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="col-span-2">
          <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
            <Icon name="clipboard-check" class="h-4 w-4 text-muted-foreground" />
            Repairs Made
          </Label>
          <textarea v-model="form.repairs_made" class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm" rows="2"></textarea>
        </div>
        
        <!-- Vendor & Invoice Information -->
        <div class="col-span-2 border-b pb-1 mb-2 mt-1 flex items-center gap-2">
          <Icon name="receipt" class="h-4 w-4 text-primary" />
          <h3 class="text-md font-semibold text-primary">Vendor & Invoice Information</h3>
        </div>
        
        <div class="grid grid-cols-2 gap-3 col-span-2">
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="building-2" class="h-4 w-4 text-muted-foreground" />
              Vendor
            </Label>
            <div class="relative">
              <select v-model="form.vendor_id" required class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm">
                <option disabled value="">Select</option>
                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.vendor_name }}</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="file-text" class="h-4 w-4 text-muted-foreground" />
              WO#
            </Label>
            <Input v-model="form.wo_number" class="h-9 w-full"/>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="activity" class="h-4 w-4 text-muted-foreground" />
              WO Status
            </Label>
            <div class="relative">
              <select v-model="form.wo_status_id" required class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm">
                <option disabled value="">Select</option>
                <option v-for="s in woStatuses" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="check-square" class="h-4 w-4 text-muted-foreground" />
              On QS
            </Label>
            <div class="relative">
              <select v-model="form.on_qs" required class="flex h-9 w-full appearance-none rounded-md border bg-background px-3 py-1 text-sm">
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
        </div>
        
        <div class="grid grid-cols-2 gap-3 col-span-2">
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="file-invoice" class="h-4 w-4 text-muted-foreground" />
              Invoice
            </Label>
            <Input v-model="form.invoice" class="h-9 w-full"/>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="dollar-sign" class="h-4 w-4 text-muted-foreground" />
              Amount
            </Label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">$</span>
              <Input type="number" step="0.01" v-model="form.invoice_amount" class="pl-7 h-9 w-full"/>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <input type="checkbox" v-model="form.invoice_received" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-1 focus:ring-primary"/>
            <Label class="flex items-center gap-1.5 text-sm font-medium">
              <Icon name="inbox" class="h-4 w-4 text-muted-foreground" />
              Invoice Received
            </Label>
          </div>
          <div>
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="calendar-days" class="h-4 w-4 text-muted-foreground" />
              QS Invoice Date
            </Label>
            <Input type="date" v-model="form.qs_invoice_date" class="h-9 w-full"/>
          </div>
        </div>
        
        <!-- Dispute Information (Update only) -->
        <div v-if="formAction==='Update'" class="col-span-2">
          <div class="border-b pb-1 mb-2 mt-1 flex items-center gap-2">
            <Icon name="alert-octagon" class="h-4 w-4 text-primary" />
            <h3 class="text-md font-semibold text-primary">Dispute Information</h3>
          </div>
          <div class="flex items-center space-x-2">
            <input type="checkbox" v-model="form.disputed" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-1 focus:ring-primary"/>
            <Label class="flex items-center gap-1.5 text-sm font-medium">
              <Icon name="alert-circle" class="h-4 w-4 text-muted-foreground" />
              Disputed?
            </Label>
          </div>
          <div v-if="form.disputed" class="mt-2">
            <Label class="flex items-center gap-1.5 mb-1 text-sm font-medium">
              <Icon name="message-square" class="h-4 w-4 text-muted-foreground" />
              Dispute Outcome
            </Label>
            <textarea v-model="form.dispute_outcome" class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm" rows="2"></textarea>
          </div>
        </div>
      </form>
    </div>
    
    <DialogFooter class="px-4 sm:px-6 flex justify-end gap-2 mt-2 pt-3 border-t">
      <Button variant="outline" @click="closeModal" class="h-9 px-4 py-1 text-xs sm:text-sm">Cancel</Button>
      <Button type="submit" @click="submitForm" class="h-9 px-4 py-1 text-xs sm:text-sm">{{ formAction }}</Button>
    </DialogFooter>
  </DialogContent>
</Dialog>

    <!-- Delete One -->
    <Dialog v-model:open="showDeleteModal">
      <DialogContent class="max-w-[95vw] sm:max-w-md">
        <DialogHeader class="px-4 sm:px-6">
          <DialogTitle class="text-lg sm:text-xl">Confirm Deletion</DialogTitle>
          <DialogDescription class="text-xs sm:text-sm">This action cannot be undone.</DialogDescription>
        </DialogHeader>
        <DialogFooter class="px-4 sm:px-6 flex gap-2">
          <Button variant="outline" @click="showDeleteModal=false" class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm">Cancel</Button>
          <Button variant="destructive" @click="confirmDelete" class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm">Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Bulk Delete -->
    <Dialog v-model:open="showBulkDeleteModal">
      <DialogContent class="max-w-[95vw] sm:max-w-md">
        <DialogHeader class="px-4 sm:px-6">
          <DialogTitle class="text-lg sm:text-xl">Delete {{ selectedIds.length }} Orders?</DialogTitle>
          <DialogDescription class="text-xs sm:text-sm">This action cannot be undone.</DialogDescription>
        </DialogHeader>
        <DialogFooter class="px-4 sm:px-6 flex gap-2">
          <Button variant="outline" @click="showBulkDeleteModal=false" class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm">Cancel</Button>
          <Button variant="destructive" @click="deleteBulk" class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm">Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

<!-- Areas Modal -->
<Dialog v-model:open="showAreasModal">
  <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-[600px]">
    <DialogHeader>
      <DialogTitle>Manage Areas of Concern</DialogTitle>
      <DialogDescription class="text-sm sm:text-base">Add or remove areas of concern for repair orders.</DialogDescription>
    </DialogHeader>

    <div class="space-y-4 sm:space-y-6">
      <!-- Add new area of concern form -->
      <form @submit.prevent="submitArea" class="space-y-3 sm:space-y-4">
        <div class="space-y-1 sm:space-y-2">
          <Label for="concern">Area of Concern</Label>
          <Input id="concern" v-model="areaForm.concern" required class="text-sm sm:text-base" />
        </div>

        <Button type="submit" class="w-full">Add Area of Concern</Button>
      </form>

      <!-- List of existing areas of concern with fixed height and scrolling -->
      <div class="rounded-md border">
        <div class="max-h-[40vh] overflow-y-auto sm:max-h-[300px]">
          <Table>
            <TableHeader class="sticky top-0 z-10 bg-background">
              <TableRow>
                <TableHead class="text-xs sm:text-sm">Area of Concern</TableHead>
                <TableHead class="w-20 text-xs sm:text-sm">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="!props.areasOfConcern.length">
                <TableCell colspan="2" class="py-4 text-center text-sm">No areas of concern found.</TableCell>
              </TableRow>
              <TableRow v-for="a in areasOfConcern" :key="a.id">
                <TableCell class="text-xs sm:text-sm">
                  {{ a.concern }}
                  <span v-if="a.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-1 sm:space-x-2">
                    <Button v-if="a.deleted_at" @click="restoreArea(a.id)" variant="outline" size="sm">
                      <Icon name="undo" class="h-4 w-4" />
                    </Button>
                    <Button
                      v-if="!a.deleted_at"
                      @click="deleteArea(a.id)"
                      variant="destructive"
                      size="sm"
                    >
                      <Icon name="trash" class="h-4 w-4" />
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
      <Button @click="showAreasModal = false" variant="outline" class="w-full sm:w-auto">Close</Button>
    </DialogFooter>
  </DialogContent>
</Dialog>

<!-- Vendors Modal -->
<Dialog v-model:open="showVendorsModal">
  <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-[600px]">
    <DialogHeader>
      <DialogTitle>Manage Vendors</DialogTitle>
      <DialogDescription class="text-sm sm:text-base">Add or remove vendors for repair orders.</DialogDescription>
    </DialogHeader>

    <div class="space-y-4 sm:space-y-6">
      <!-- Add new vendor form -->
      <form @submit.prevent="submitVendor" class="space-y-3 sm:space-y-4">
        <div class="space-y-1 sm:space-y-2">
          <Label for="vendor_name">Vendor Name</Label>
          <Input id="vendor_name" v-model="vendorForm.vendor_name" required class="text-sm sm:text-base" />
        </div>

        <Button type="submit" class="w-full">Add Vendor</Button>
      </form>

      <!-- List of existing vendors -->
      <div class="overflow-hidden rounded-md border">
        <div class="max-h-[40vh] overflow-y-auto sm:max-h-[300px]">
          <Table>
            <TableHeader class="sticky top-0 z-10 bg-background">
              <TableRow class="sticky top-0 z-10 border-b bg-background">
                <TableHead class="text-xs sm:text-sm">Vendor Name</TableHead>
                <TableHead class="text-xs sm:text-sm">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="vendors.length === 0">
                <TableCell colspan="2" class="py-4 text-center text-sm">No vendors found.</TableCell>
              </TableRow>
              <TableRow v-for="vendor in vendors" :key="vendor.id">
                <TableCell class="text-xs sm:text-sm">
                  {{ vendor.vendor_name }}
                  <span v-if="vendor.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-1 sm:space-x-2">
                    <Button v-if="vendor.deleted_at" @click="restoreVendor(vendor.id)" variant="outline" size="sm">
                      <Icon name="undo" class="h-4 w-4" />
                    </Button>
                    <Button
                      v-if="!vendor.deleted_at"
                      @click="deleteVendor(vendor.id)"
                      variant="destructive"
                      size="sm"
                    >
                      <Icon name="trash" class="h-4 w-4" />
                    </Button>
                    <Button
                      v-if="vendor.deleted_at"
                      @click="forceDeleteVendor(vendor.id)"
                      variant="destructive"
                      size="sm"
                    >
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
      <Button @click="showVendorsModal = false" variant="outline" class="w-full sm:w-auto">Close</Button>
    </DialogFooter>
  </DialogContent>
</Dialog>

<!-- Statuses Modal -->
<Dialog v-model:open="showStatusModal">
  <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-[600px]">
    <DialogHeader>
      <DialogTitle>Manage Work Order Statuses</DialogTitle>
      <DialogDescription class="text-sm sm:text-base"> Add or remove work order statuses for repair orders. </DialogDescription>
    </DialogHeader>

    <div class="space-y-4 sm:space-y-6">
      <!-- Add new WO status form -->
      <form @submit.prevent="submitStatus" class="space-y-3 sm:space-y-4">
        <div class="space-y-1 sm:space-y-2">
          <Label for="status_name">Status Name</Label>
          <Input id="status_name" v-model="statusForm.name" required class="text-sm sm:text-base" />
        </div>

        <Button type="submit" class="w-full">Add Work Order Status</Button>
      </form>

      <!-- List of existing WO statuses -->
      <div class="overflow-hidden rounded-md border">
        <div class="max-h-[40vh] overflow-y-auto sm:max-h-[300px]">
          <Table>
            <TableHeader class="sticky top-0 z-10 bg-background">
              <TableRow class="sticky top-0 z-10 border-b bg-background">
                <TableHead class="text-xs sm:text-sm">Status Name</TableHead>
                <TableHead class="text-xs sm:text-sm">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="!props.woStatuses.length">
                <TableCell colspan="2" class="py-4 text-center text-sm">No work order statuses found.</TableCell>
              </TableRow>
              <TableRow v-for="s in props.woStatuses" :key="s.id">
                <TableCell class="text-xs sm:text-sm">
                  {{ s.name }}
                  <span v-if="s.deleted_at" class="ml-1 text-xs text-red-500">(Deleted)</span>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-1 sm:space-x-2">
                    <Button v-if="s.deleted_at" @click="restoreStatus(s.id)" variant="outline" size="sm">
                      <Icon name="undo" class="h-4 w-4" />
                    </Button>
                    <Button
                      v-if="!s.deleted_at"
                      @click="deleteStatus(s.id)"
                      variant="destructive"
                      size="sm"
                    >
                      <Icon name="trash" class="h-4 w-4" />
                    </Button>
                    <Button
                      v-if="s.deleted_at"
                      @click="forceDeleteStatus(s.id)"
                      variant="destructive"
                      size="sm"
                    >
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
      <Button @click="showStatusModal = false" variant="outline" class="w-full sm:w-auto">Close</Button>
    </DialogFooter>
  </DialogContent>
</Dialog>
<!-- Dialog for Canceled QS Invoices -->
<Dialog v-model:open="showCanceledQSInvoicesDialog">
            <DialogContent class="max-h-[90vh] max-w-[95vw] overflow-y-auto md:max-w-[80vw] lg:max-w-4xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-lg md:text-xl">
                        <Icon name="alert-triangle" class="h-5 w-5 text-red-600" />
                        Canceled Invoices on QS
                    </DialogTitle>
                    <DialogDescription>
                        These invoices have a canceled WO status but are still marked as on QS. Please review and take appropriate action.
                    </DialogDescription>
                </DialogHeader>

                <div class="max-h-[60vh] overflow-x-auto overflow-y-auto">
                    <Table v-if="props.canceledQSInvoices?.length">
                        <TableHeader>
                            <TableRow>
                                <TableHead class="whitespace-nowrap">RO Number</TableHead>
                                <TableHead class="whitespace-nowrap">Vendor</TableHead>
                                <TableHead class="whitespace-nowrap text-right">Amount</TableHead>
                                <TableHead class="whitespace-nowrap">Week</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="invoice in props.canceledQSInvoices"
                                :key="invoice.ro_number"
                                class="hover:bg-red-50 dark:hover:bg-red-900/30"
                            >
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
                    <div v-else class="py-8 text-center text-muted-foreground">No canceled QS invoices found.</div>
                </div>

                <DialogFooter class="mt-4 flex flex-col gap-2 sm:flex-row">
                    <Button @click="showCanceledQSInvoicesDialog = false" variant="outline" class="w-full sm:w-auto">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
  </div>
</template>
  
  <script setup lang="ts">
  import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
  import { router, useForm } from '@inertiajs/vue3'
  import SortIndicator from '@/components/SortIndicator.vue'
  import Icon from '@/components/Icon.vue'
  import {
    Alert, AlertTitle, AlertDescription,
    Button, Card, CardContent, CardHeader, CardTitle,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Input, Label, Table, TableHeader, TableRow, TableHead, TableBody, TableCell
  } from '@/components/ui'
  
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
    canceledQSInvoices: {
        type: Array,
        default: () => [],
    },
    outstandingInvoices: { type: Array, default: () => [] },
    workOrdersByTruck: { type: Array, default: () => [] },
    workOrderByAreasOfConcern: { type: Array, default: () => [] },
    filters: { type: Object, default: ()=> ({
        search: '',
        vendor_id: '',
        status_id: '',
    }),},
    perPage: { type: Number, default: 10 },
    openedComponent: { type: String, default: 'trucks' },
  })
  const emit = defineEmits<{'update:perPage':(val:number)=>void}>()
  
  // State
  const filter = ref({...props.filters, dateFilter:props.dateFilter})
  const localPerPage = ref(props.perPage)
  const selectedIds = ref<number[]>([])
  const showFilters = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')
  const showUpload = ref(false)
  
  const showModal = ref(false)
  const showDeleteModal = ref(false)
  const showBulkDeleteModal = ref(false)
  const showAreasModal = ref(false)
  const showVendorsModal = ref(false)
  const showStatusModal = ref(false)
  
  const formAction = ref<'Create'|'Update'>('Create')
  const form = useForm({
    id:null, tenant_id:props.SuperAdmin?'':null, ro_number:'', ro_open_date:'', ro_close_date:'', truck_id:null,
    vendor_id:null, wo_number:'', wo_status_id:null, invoice:'', invoice_amount:'', invoice_received:false,
    on_qs:'no', qs_invoice_date:'', disputed:false, dispute_outcome:'', repairs_made:'', area_of_concerns:[]
  })
  const areaForm = useForm({concern:''})
  const vendorForm = useForm({vendor_name:''})
  const statusForm = useForm({name:''})
  
  // Computed
  const isAdmin = computed(()=>props.SuperAdmin)
  const hasData = computed(()=>props.repairOrders.data.length>0)
  const allSelected = computed(()=>hasData.value && props.repairOrders.data.every(o=>selectedIds.value.includes(o.id)))
  const dateOptions = [
    {label:'Yesterday',value:'yesterday'},{label:'WTD',value:'current-week'},{label:'T6W',value:'6w'},{label:'Quarterly',value:'quarterly'}
  ]
  
  // Helpers
  const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const [year, month, day] = parts;
    return `${Number(month)}/${Number(day)}/${year}`;
};
  function formatCurrency(a:any){ return a? new Intl.NumberFormat('en-US',{style:'currency',currency:'USD'}).format(a): '$0.00' }
  const templateUrl = '/storage/upload-data-temps/Repair Orders Template.csv'
  
  // Actions
  function selectDate(val:string){ filter.value.dateFilter=val; applyFilters() }
  function applyFilters() {
  const routeName = props.SuperAdmin
    ? 'repair_orders.index.admin'
    : 'repair_orders.index'

  const params = {
    ...(props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug }),
    ...filter.value,
    perPage:    localPerPage.value,
    dateFilter: filter.value.dateFilter,
    openedComponent: 'repairOrders',
  }
  router.visit(
    route(routeName, params),
    {
      only: [  'repairOrders',
      'filters',
      'perPage',
      'dateFilter',
      'weekNumber',
      'startWeekNumber',
      'endWeekNumber',
      'year',
      'workOrdersByTruck',
      'workOrderByAreasOfConcern',
      'openedComponent',
     ],
    }
  )
}
  function resetFilters(){ filter.value={...filter.value, search:'',vendor_id:'',status_id:''}; applyFilters() }
  const sortState = ref({column:'ro_number',direction:'asc'})
  function sort(col:string){ if(sortState.value.column===col) sortState.value.direction = sortState.value.direction==='asc'?'desc':'asc'; else { sortState.value = {column:col,direction:'asc'} }; applyFilters() }
  function changePerPage(){  applyFilters() }
  function go(url?:string){if (url) {
        // Add perPage parameter to the URL
        const urlObj = new URL(url);
        const baseUrl = urlObj.origin + urlObj.pathname;

        router.get(
            baseUrl,
            {
                ...filter.value,
                perPage:    localPerPage.value,
                dateFilter: filter.value.dateFilter,
                openedComponent: 'repairOrders',
                page: urlObj.searchParams.get('page') || 1,
            },
            {preserveScroll: true}
        );
    } }
  function toggleAll(e:Event){ const chk=(e.target as HTMLInputElement).checked; selectedIds.value = chk? props.repairOrders.data.map(o=>o.id):[] }
  
  // CSV
  function importCsv(evt:any){ const file=evt.target.files[0]; if(!file)return; const f=new FormData(); f.append('file',file); const r= props.tenantSlug? route('repair_orders.import',{tenantSlug:props.tenantSlug}): route('repair_orders.import.admin'); router.post(r,f,{onSuccess:()=>{ successMessage.value='Imported'; evt.target.value=null }}) }
  function exportCsv(){ if(!hasData.value){ errorMessage.value='No data'; return } window.location.href = props.tenantSlug? route('repair_orders.export',{tenantSlug:props.tenantSlug}): route('repair_orders.export.admin') }
  
  // Create/Edit handlers
  function openCreateModal(){ form.reset(); formAction.value='Create'; showModal.value=true }
  function openEdit(o:any){ form.reset(); form.reset();

// Set all form fields from the order object
form.id = o.id;
form.tenant_id = o.tenant_id;
form.ro_number = o.ro_number;
form.ro_open_date = o.ro_open_date;
form.ro_close_date = o.ro_close_date || '';
form.truck_id = o.truck_id;
form.vendor_id = o.vendor_id;
form.wo_number = o.wo_number;
form.wo_status_id = o.wo_status_id;
form.invoice = o.invoice || '';
form.invoice_amount = o.invoice_amount || '';
// Handle boolean values that might come as 0/1 or true/false
form.invoice_received = Boolean(o.invoice_received);
form.on_qs = o.on_qs || 'no';
form.qs_invoice_date = o.qs_invoice_date || '';
form.disputed = Boolean(o.disputed);
form.dispute_outcome = o.dispute_outcome || '';
form.repairs_made = o.repairs_made || '';


// Handle areas of concern with better error checking - use areas_of_concern (snake_case)
form.area_of_concerns = [];
if (o.areas_of_concern && Array.isArray(o.areas_of_concern)) {
    // Extract just the IDs from the areas_of_concern relationship
    form.area_of_concerns = o.areas_of_concern.map((area) => area.id);
}
formAction.value='Update'; showModal.value=true }
  function closeModal(){ form.reset(); showModal.value=false }
  function submitForm(){ const endpoint = formAction.value==='Update'? (props.tenantSlug? route('repair_orders.update',[props.tenantSlug,form.id]): route('repair_orders.update.admin',form.id)) : (props.tenantSlug? route('repair_orders.store',{tenantSlug:props.tenantSlug}): route('repair_orders.store.admin')); form[formAction.value==='Update'?'put':'post'](endpoint,{ onSuccess:()=>{ successMessage.value=`${formAction.value}d!`; showModal.value=false; } }) }
  
  // Delete handlers
  let deleteId:number|null=null
  function deleteOne(id:number){ deleteId=id; showDeleteModal.value=true }
  function confirmDelete(){ if(deleteId===null)return; const r = props.tenantSlug? route('repair_orders.destroy',[props.tenantSlug,deleteId]): route('repair_orders.destroy.admin',deleteId); router.delete(r,{ onSuccess:()=>{ successMessage.value='Deleted'; showDeleteModal.value=false } }) }
  function confirmBulkDelete(){ showBulkDeleteModal.value=true }
  function deleteBulk(){ const r = props.tenantSlug? route('repair_orders.destroyBulk',{tenantSlug:props.tenantSlug}): route('repair_orders.destroyBulk.admin'); useForm({ids:selectedIds.value}).delete(r,{ onSuccess:()=>{ successMessage.value=`${selectedIds.value.length} deleted`; selectedIds.value=[]; showBulkDeleteModal.value=false } }) }
  
  // Areas handlers
  function openAreasModal(){ areaForm.reset(); showAreasModal.value=true }
  function submitArea(){ areaForm.post(route('area_of_concerns.store.admin'),{ onSuccess:()=>areaForm.reset() }) }
  function deleteArea(id:number){ router.delete(route('area_of_concerns.destroy.admin',id)) }
  function restoreArea(id:number){ router.post(route('area_of_concerns.restore.admin',id)) }
  
  // Vendors handlers
  function openVendorsModal(){ vendorForm.reset(); showVendorsModal.value=true }
  function submitVendor(){ vendorForm.post(route('vendors.store.admin'),{onSuccess:()=>vendorForm.reset()}) }
  function deleteVendor(id:number){ router.delete(route('vendors.destroy.admin',id)) }
  function restoreVendor(id:number){ router.post(route('vendors.restore.admin',id)) }
  
  // Status handlers
  function openStatusModal(){ statusForm.reset(); showStatusModal.value=true }
  function submitStatus(){ statusForm.post(route('wo_statuses.store.admin'),{onSuccess:()=>statusForm.reset()}) }
  function deleteStatus(id:number){ router.delete(route('wo_statuses.destroy.admin',id)) }
  function restoreStatus(id:number){ router.post(route('wo_statuses.restore.admin',id)) }
  const hasCanceledQSInvoices = computed(() => {
    return props.canceledQSInvoices.length > 0;
});
const showCanceledQSInvoicesDialog = ref(false);
  // Map areas for display
  const areasMap = computed(()=> Object.fromEntries(props.areasOfConcern.map(a=>[a.id,a.concern])))
  const availableAreas = computed(()=> props.areasOfConcern.filter(a=>!form.area_of_concerns.includes(a.id)))
  function addArea(e:any){ const id=Number(e.target.value); if(id&&!form.area_of_concerns.includes(id)) form.area_of_concerns.push(id); e.target.value='' }
  function removeArea(id:number){ form.area_of_concerns = form.area_of_concerns.filter(x=>x!==id) }
  const weekNumberText = computed(() => {
    // For yesterday and current-week, show single week
    if ((props.dateFilter === 'yesterday' || props.dateFilter === 'current-week') && props.weekNumber && props.year) {
        return `Week ${props.weekNumber}, ${props.year}`;
    }

    // For 6w and quarterly, show start-end week range if available
    if ((props.dateFilter === '6w' || props.dateFilter === 'quarterly') && props.startWeekNumber && props.endWeekNumber && props.year) {
        return `Weeks ${props.startWeekNumber}-${props.endWeekNumber}, ${props.year}`;
    }

    return '';
});
  // Click outside
  onMounted(()=>{ const h=(e:Event)=>{ if(showUpload.value&&! (e.target as HTMLElement).closest('.relative')) showUpload.value=false }; document.addEventListener('click',h); onUnmounted(()=>document.removeEventListener('click',h)) })
  
  // Clear flash
  setTimeout(()=> successMessage.value='',5000)

  // Get top 5 areas of concern
const topAreasOfConcern = computed(() => {
    const areas = props.workOrderByAreasOfConcern || [];
    return areas.slice(0, 5);
});
const totalOutstanding = computed(() => {
    return props.outstandingInvoices.reduce((sum, inv) => {
        const amt = Number(inv.invoice_amount) || 0;
        return sum + amt;
    }, 0);
});
// Get top 5 trucks by work orders
const topWorkOrdersByTruck = computed(() => {
    const trucks = props.workOrdersByTruck || [];
    return trucks.slice(0, 5);
});
const minInvoiceAmount = ref<number | null>(null)
const outstandingDate = ref<string | null>(null)
const showOutstandingInvoicesSection = ref(false);

// Add this function to handle the filter application
const filteredOutstandingInvoices = computed(() => {
  return props.outstandingInvoices.filter(inv => {
    const meetsAmount = minInvoiceAmount.value != null
      ? Number(inv.invoice_amount) >= minInvoiceAmount.value
      : true
    const meetsDate = outstandingDate.value
      ? new Date(inv.qs_invoice_date) > new Date(outstandingDate.value)
      : true
    return meetsAmount && meetsDate
  })
})

const totalFilteredOutstanding = computed(() => {
  return filteredOutstandingInvoices.value.reduce(
    (sum, inv) => sum + Number(inv.invoice_amount || 0),
    0
  )
})

  </script>
  
  <style scoped>
  .input { @apply rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 w-full transition-colors }
  .badge { @apply inline-flex items-center bg-primary/10 text-primary rounded px-2 py-0.5 text-xs transition-colors }
  .active-page { @apply border-primary bg-primary/10 text-primary }
  </style>