<template>
  <div class="w-full pt-6 md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto p-1  space-y-6">
    <!-- Success / Error Alerts -->
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
        <Icon name="truck" class="h-6 w-6 text-primary hidden sm:block" />
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-200">Truck Management</h1>
      </div>
      <div class="flex flex-wrap gap-3">
        <Button @click="openCreateModal" variant="default" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2"/> Create New Truck
        </Button>
        <Button v-if="selectedTrucks.length" @click="confirmDeleteSelected" variant="destructive" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2"/> Delete Selected ({{ selectedTrucks.length }})
        </Button>
        <div class="relative">
          <Button @click="showUploadOpts = !showUploadOpts" variant="secondary" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
            <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2"/> Upload CSV <Icon name="chevron-down" class="ml-2 h-4 w-4"/>
          </Button>
          <div v-if="showUploadOpts" class="absolute right-0 mt-1 w-48 rounded-md border bg-background shadow-lg z-10">
            <div class="py-1">
              <label class="block cursor-pointer px-4 py-2 text-sm hover:bg-muted">
                <span>Upload CSV File</span>
                <input type="file" class="hidden" @change="handleImport" accept=".csv,.txt" />
              </label>
              <a :href="templateUrl" download class="block px-4 py-2 text-sm hover:bg-muted">Download Template</a>
            </div>
          </div>
        </div>
        <Button @click.prevent="exportCSV" variant="outline" class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all">
          <Icon name="download" class="mr-1 h-4 w-4 md:mr-2"/> Download CSV
        </Button>
      </div>
    </div>

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
                  v-model="filters.search" 
                  placeholder="Search by truck ID or VIN..." 
                  @input="resetPage"
                  class="py-1 px-1 md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10" 
                />
              </div>
              <div>
                <Label for="type" class="flex items-center gap-1.5 mb-2">
                  <Icon name="truck" class="h-4 w-4 text-muted-foreground" />
                  Type
                </Label>
                <div class="relative">
                  <select 
                    id="type" 
                    v-model="filters.type" 
                    @change="resetPage"
                    class="flex h-10 w-full appearance-none items-center rounded-md border bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="">All Types</option>
                    <option value="daycab">Daycab</option>
                    <option value="sleepercab">Sleepercab</option>
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
                <Label for="make" class="flex items-center gap-1.5 mb-2">
                  <Icon name="tag" class="h-4 w-4 text-muted-foreground" />
                  Make
                </Label>
                <div class="relative">
                  <select 
                    id="make" 
                    v-model="filters.make" 
                    @change="resetPage"
                    class="flex h-10 w-full appearance-none items-center rounded-md border bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="">All Makes</option>
                    <option value="international">International</option>
                    <option value="kenworth">Kenworth</option>
                    <option value="peterbilt">Peterbilt</option>
                    <option value="volvo">Volvo</option>
                    <option value="freightliner">Freightliner</option>
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
            <Button @click="clearFilters" variant="ghost" size="sm" class="self-start">
              <Icon name="rotate_ccw" class="mr-2 h-4 w-4" /> Reset Filters
            </Button>
          </div>
        </CardContent>
      </Transition>
    </Card>

    <!-- Data Table -->
    <Card class="mx-auto max-w-[90vw] md:max-w-[64vw] lg:max-w-full overflow-x-auto shadow-sm border">
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <Table class="relative h-[500px] overflow-auto">
            <TableHeader>
              <TableRow class="sticky top-0 z-10 border-b bg-background hover:bg-background">
                <TableHead class="w-12">
                  <div class="flex items-center justify-center">
                    <input 
                      type="checkbox" 
                      :checked="isAllSelected" 
                      @change="toggleSelectAll" 
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" 
                    />
                  </div>
                </TableHead>
                <TableHead v-if="SuperAdmin" class="font-semibold">Company</TableHead>
                <TableHead 
                  v-for="col in tableColumns" 
                  :key="col" 
                  @click="sortBy(col)" 
                  class="cursor-pointer font-semibold"
                >
                  <div class="flex items-center space-x-1">
                    <template v-if="col === 'inspection_status'">Annual Inspection Status</template>
                    <template v-else-if="col === 'inspection_expiry_date'">Annual Inspection Expiration Date</template>
                    <template v-else-if="col === 'status'">Status</template>
                    <template v-else>{{ humanize(col) }}</template>
                    <div v-if="sortColumn===col" class="ml-2">
                      <svg 
                        v-if="sortDirection==='asc'" 
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
                <TableHead class="font-semibold">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="!paginatedEntries.length">
                <TableCell :colspan="SuperAdmin?tableColumns.length+3:tableColumns.length+2" class="py-8 text-center">
                  <div class="flex flex-col items-center justify-center text-muted-foreground">
                    <Icon name="search_x" class="h-12 w-12 mb-2 opacity-30" />
                    <p>No trucks found matching your criteria.</p>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-for="t in paginatedEntries" :key="t.id" class="hover:bg-muted/50">
                <TableCell class="text-center">
                  <input 
                    type="checkbox" 
                    :value="t.id" 
                    v-model="selectedTrucks"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" 
                  />
                </TableCell>
                <TableCell v-if="SuperAdmin">{{ t.tenant?.name||'—' }}</TableCell>
                <TableCell v-for="col in tableColumns" :key="col" class="whitespace-nowrap">
                  <template v-if="col === 'status'">
                    <span
                      :class="{
                        'px-2 py-1 rounded-full text-xs font-medium': true,
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': t[col] === 'active',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': t[col] === 'inactive',
                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': t[col] === 'Returned to AMZ'
                      }"
                    >
                    {{ formatCell(t, col) }}
                    </span>
                  </template>
                  <template v-else-if="col === 'inspection_status'">
                    <span
                      :class="{
                        'px-2 py-1 rounded-full text-xs font-medium': true,
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': t[col] === 'good',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': t[col] === 'expired'
                      }"
                    >
                      {{ t[col] === 'good' ? 'Good' : 'Expired' }}
                    </span>
                  </template>
                  <template v-else>
                    {{ formatCell(t, col) }}
                  </template>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-2">
                    <Button variant="warning" size="sm" @click="openEditModal(t)" class="shadow-sm hover:shadow transition-all">
                      <Icon name="pencil" class="mr-1 h-4 w-4"/> Edit
                    </Button>
                    <Button variant="destructive" size="sm" @click="confirmDelete(t)" class="shadow-sm hover:shadow transition-all">
                      <Icon name="trash" class="mr-1 h-4 w-4"/> Delete
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination -->
        <div class="border-t bg-muted/20 px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-2">
          <div class="flex items-center gap-4 text-sm text-muted-foreground">
            <span class="flex items-center gap-1">
              <Icon name="list" class="h-4 w-4" />
              Showing {{ paginatedEntries.length }} of {{ sortedEntries.length }} entries
            </span>
            <span class="flex items-center gap-1">
              <Icon name="layout-grid" class="h-4 w-4" />
              Per page:
            </span>
            <div class="relative">
              <select 
                v-model.number="localPerPage" 
                @change="onPerPageChange"
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
            <Button size="sm" variant="ghost" @click="goToPage(page-1)" :disabled="page===1" class="flex items-center gap-1">
              <Icon name="chevron-left" class="h-4 w-4" /> Prev
            </Button>
            <Button 
              v-for="n in displayedPageNumbers" 
              :key="n" 
              size="sm" 
              variant="ghost" 
              @click="goToPage(n)"
              :class="{'border-primary bg-primary/10 text-primary font-medium':n===page}"
            >
              {{ n }}
            </Button>
            <Button size="sm" variant="ghost" @click="goToPage(page+1)" :disabled="page===totalPages" class="flex items-center gap-1">
              Next <Icon name="chevron-right" class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Create/Edit Modal -->
    <Dialog v-model:open="showModal">
      <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
        <DialogHeader class="px-4 sm:px-6 border-b pb-4">
          <div class="flex items-center gap-2">
            <Icon :name="form.id ? 'pencil' : 'plus-circle'" class="h-5 w-5 text-primary" />
            <DialogTitle class="text-lg sm:text-xl">{{ form.id ? 'Edit Truck' : 'Create Truck' }}</DialogTitle>
          </div>
          <DialogDescription class="text-xs sm:text-sm mt-1">Fill in the details to {{ form.id ? 'update' : 'create' }} a truck.</DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitForm" class="grid max-h-[75vh] grid-cols-1 gap-4 overflow-y-auto p-4 sm:grid-cols-2 sm:gap-5 sm:p-6">
          <!-- Form sections -->
          <div class="col-span-2 mb-2 border-b pb-2">
            <h3 class="text-sm font-medium text-muted-foreground">Basic Information</h3>
          </div>
          
          <div v-if="SuperAdmin" class="col-span-2 sm:col-span-1">
            <Label for="tenant" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="building" class="h-4 w-4 text-muted-foreground" />
              Company Name
            </Label>
            <div class="relative">
              <select 
                id="tenant" 
                v-model="form.tenant_id" 
                required
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <option value="">Select Company</option>
                <option v-for="tn in tenants" :key="tn.id" :value="tn.id">{{ tn.name }}</option>
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
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="truckid" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="hash" class="h-4 w-4 text-muted-foreground" />
              Truck ID
            </Label>
            <Input 
              id="truckid" 
              v-model.number="form.truckid" 
              type="number" 
              required 
              placeholder="Enter truck ID number"
              class="transition-shadow hover:shadow-sm focus:shadow-sm"
            />
          </div>
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="type" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="truck" class="h-4 w-4 text-muted-foreground" />
              Type
            </Label>
            <div class="relative">
              <select 
                id="type" 
                v-model="form.type" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="daycab">Daycab</option>
                <option value="sleepercab">Sleepercab</option>
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
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="make" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="tag" class="h-4 w-4 text-muted-foreground" />
              Make
            </Label>
            <div class="relative">
              <select 
                id="make" 
                v-model="form.make" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="international">International</option>
                <option value="kenworth">Kenworth</option>
                <option value="peterbilt">Peterbilt</option>
                <option value="volvo">Volvo</option>
                <option value="freightliner">Freightliner</option>
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
          
          <!-- Additional Information Section -->
          <div class="col-span-2 mt-2 mb-2 border-b pb-2">
            <h3 class="text-sm font-medium text-muted-foreground">Additional Information</h3>
          </div>
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="fuel" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="fuel" class="h-4 w-4 text-muted-foreground" />
              Fuel
            </Label>
            <div class="relative">
              <select 
                id="fuel" 
                v-model="form.fuel" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="diesel">Diesel</option>
                <option value="cng">CNG</option>
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
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="license" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="id_card" class="h-4 w-4 text-muted-foreground" />
              License
            </Label>
            <Input 
              id="license" 
              v-model.number="form.license" 
              type="number" 
              min="0" 
              required 
              placeholder="Enter license number"
              class="transition-shadow hover:shadow-sm focus:shadow-sm"
            />
          </div>
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="vin" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="fingerprint" class="h-4 w-4 text-muted-foreground" />
              VIN
            </Label>
            <Input 
              id="vin" 
              v-model="form.vin" 
              type="text" 
              required 
              placeholder="Enter vehicle identification number"
              class="transition-shadow hover:shadow-sm focus:shadow-sm"
            />
          </div>
          
          <!-- Inspection Section -->
          <div class="col-span-2 mt-2 mb-2 border-b pb-2">
            <h3 class="text-sm font-medium text-muted-foreground">Inspection Details</h3>
          </div>
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="inspection_status" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="clipboard_check" class="h-4 w-4 text-muted-foreground" />
              Annual Inspection Status
            </Label>
            <div class="relative">
              <select 
                id="inspection_status" 
                v-model="form.inspection_status" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="good">Good</option>
                <option value="expired">Expired</option>
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
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="status" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="activity" class="h-4 w-4 text-muted-foreground" />
              Status
            </Label>
            <div class="relative">
              <select 
                id="status" 
                v-model="form.status" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="Returned to AMZ">Returned to AMZ</option>
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
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="make" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="tag" class="h-4 w-4 text-muted-foreground" />
              Make
            </Label>
            <div class="relative">
              <select 
                id="make" 
                v-model="form.make" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="international">International</option>
                <option value="kenworth">Kenworth</option>
                <option value="peterbilt">Peterbilt</option>
                <option value="volvo">Volvo</option>
                <option value="freightliner">Freightliner</option>
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
          
          <!-- Additional Information Section -->
          <div class="col-span-2 mt-2 mb-2 border-b pb-2">
            <h3 class="text-sm font-medium text-muted-foreground">Additional Information</h3>
          </div>
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="fuel" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="fuel" class="h-4 w-4 text-muted-foreground" />
              Fuel
            </Label>
            <div class="relative">
              <select 
                id="fuel" 
                v-model="form.fuel" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="diesel">Diesel</option>
                <option value="cng">CNG</option>
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
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="license" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="id_card" class="h-4 w-4 text-muted-foreground" />
              License
            </Label>
            <Input 
              id="license" 
              v-model.number="form.license" 
              type="number" 
              min="0" 
              required 
              placeholder="Enter license number"
              class="transition-shadow hover:shadow-sm focus:shadow-sm"
            />
          </div>
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="vin" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="fingerprint" class="h-4 w-4 text-muted-foreground" />
              VIN
            </Label>
            <Input 
              id="vin" 
              v-model="form.vin" 
              type="text" 
              required 
              placeholder="Enter vehicle identification number"
              class="transition-shadow hover:shadow-sm focus:shadow-sm"
            />
          </div>
          
          <!-- Inspection Section -->
          <div class="col-span-2 mt-2 mb-2 border-b pb-2">
            <h3 class="text-sm font-medium text-muted-foreground">Inspection Details</h3>
          </div>
          
          <div class="col-span-2 sm:col-span-1">
            <Label for="inspection_status" class="flex items-center gap-1.5 mb-1.5">
              <Icon name="clipboard_check" class="h-4 w-4 text-muted-foreground" />
              Annual Inspection Status
            </Label>
            <div class="relative">
              <select 
                id="inspection_status" 
                v-model="form.inspection_status" 
                required 
                class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 transition-shadow hover:shadow-sm focus:shadow-sm"
              >
                <option value="good">Good</option>
                <option value="expired">Expired</option>
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
          
          <!-- Form Actions -->
          <div class="col-span-2 mt-4 flex justify-end gap-3">
            <Button 
              type="button" 
              variant="outline" 
              @click="showModal = false"
              class="transition-all hover:shadow-sm"
            >
              <Icon name="x" class="mr-2 h-4 w-4" />
              Cancel
            </Button>
            <Button 
              type="submit" 
              variant="default"
              class="transition-all hover:shadow-sm"
            >
              <Icon :name="form.id ? 'pencil' : 'plus'" class="mr-2 h-4 w-4" />
              {{ form.id ? 'Update' : 'Create' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Confirm Delete Dialog -->
    <Dialog v-model:open="showDeleteModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirm Deletion</DialogTitle>
          <DialogDescription>Are you sure you want to delete this truck? This action cannot be undone.</DialogDescription>
        </DialogHeader>
        <DialogFooter class="mt-4">
          <Button variant="outline" @click="showDeleteModal=false">Cancel</Button>
          <Button variant="destructive" @click="deleteEntry">Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Confirm Bulk Delete Dialog -->
    <Dialog v-model:open="showBulkDeleteModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirm Bulk Deletion</DialogTitle>
          <DialogDescription>Are you sure you want to delete {{ selectedTrucks.length }} trucks? This action cannot be undone.</DialogDescription>
        </DialogHeader>
        <DialogFooter class="mt-4">
          <Button variant="outline" @click="showBulkDeleteModal=false">Cancel</Button>
          <Button variant="destructive" @click="deleteSelectedTrucks">Delete Selected</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
    <form ref="exportForm" method="GET" class="hidden" />
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
  import { useForm, router } from '@inertiajs/vue3'
  import Icon from '@/components/Icon.vue'
  import {
    Alert, AlertTitle, AlertDescription,
    Button,Card,CardContent,CardHeader,CardTitle,
    Input, Label, Table, TableHeader, TableRow, TableHead, TableBody, TableCell,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
  } from '@/components/ui'
  
  const props = defineProps<{
    entries: any[], tenantSlug: string|null, SuperAdmin: boolean, tenants: any[], perPage: number
  }>()
  const emit = defineEmits<{'update:perPage':(v:number)=>void}>()
  
  // Reactive state
  const filters = ref({search:'',type:'',make:''})
  const sortColumn = ref('truckid')
  const sortDirection = ref<'asc'|'desc'>('asc')
  const page = ref(1);
  const perPage = ref(10);
  const localPerPage = ref(10);
  const selectedTrucks = ref<number[]>([])
  const showUploadOpts = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')
  const showModal = ref(false)
  const showDeleteModal = ref(false)
  const showBulkDeleteModal = ref(false)
  const deleteTarget = ref<any>(null)
  const isSubmitting = ref(false)
  const showFilters = ref(false)
  // Form states
  const form = useForm({
    id: null,
    truckid: null,
    type: 'daycab',
    make: 'international',
    fuel: 'diesel',
    license: null,
    vin: '',
    tenant_id: props.SuperAdmin ? '' : null,
    status: 'active',
    inspection_status: 'good',
    inspection_expiry_date: new Date().toISOString().split('T')[0], // Today's date as default
});
  const importForm = useForm({csv_file:null})
  const exportForm = ref(null);
  
  // Computeds
  const filteredEntries = computed(() => props.entries.filter(t=>{
    if(filters.value.search && !String(t.truckid).includes(filters.value.search) && !(t.vin||'').toLowerCase().includes(filters.value.search.toLowerCase())) return false
    if(filters.value.type && t.type?.toLowerCase()!=filters.value.type) return false
    if(filters.value.make && t.make?.toLowerCase()!=filters.value.make) return false
    return true
  }))
  const sortedEntries = computed(() => [...filteredEntries.value].sort((a,b)=>{
    let A=a[sortColumn.value], B=b[sortColumn.value]
    if(A==null) return 1
    if(B==null) return -1
    if(typeof A==='string'){A=A.toLowerCase(); B=(B as string).toLowerCase()}
    if(A<B) return sortDirection.value==='asc'? -1:1
    if(A>B) return sortDirection.value==='asc'? 1:-1
    return 0
  }))
  const totalPages = computed(() => {
  return Math.ceil(sortedEntries.value.length / perPage.value);
});

const paginatedEntries = computed(() => {
  const start = (page.value - 1) * perPage.value;
  const end = start + perPage.value;
  return sortedEntries.value.slice(start, end);
});
  const isAllSelected = computed(() => paginatedEntries.value.length>0 && paginatedEntries.value.every(t=>selectedTrucks.value.includes(t.id)))
  const tableColumns = ['truckid', 'type', 'make', 'fuel', 'license', 'vin', 'inspection_status', 'inspection_expiry_date', 'status'];

  // Watchers
  watch(successMessage, v=> v && setTimeout(()=>successMessage.value='',5000))
  watch(perPage, (newValue) => {
  localPerPage.value = newValue;
});  
  // Methods
  function humanize(col:string){ return col.replace(/_/g,' ').replace(/\b\w/g,l=>l.toUpperCase()) }
  function formatDate(s:string){ if(!s) return '—'; const d=new Date(s+'T00:00:00'); return d.toLocaleDateString() }
  function formatCell(t:any,col:string){
    if(col==='inspection_status') return t.inspection_status==='good'?'Good':'Expired'
    if(col==='inspection_expiry_date') return formatDate(t[col])
    if(col==='fuel' && t.fuel==='cng') return 'CNG'
    const v=t[col]; return typeof v==='string'? v.charAt(0).toUpperCase()+v.slice(1): v
  }
  const resetPage = () => {
  page.value = 1;
};
  function clearFilters(){ filters.value={search:'',type:'',make:''}; resetPage() }
  function sortBy(col:string){ if(sortColumn.value===col) sortDirection.value = sortDirection.value==='asc'?'desc':'asc'; else { sortColumn.value=col; sortDirection.value='asc' } }
  const onPerPageChange = () => {
  perPage.value = localPerPage.value;
  resetPage();
};
  const goToPage = (newPage) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage;
  }
};
  function toggleSelectAll(e:Event){ const chk=(e.target as HTMLInputElement).checked; if(chk) paginatedEntries.value.forEach(t=>{ if(!selectedTrucks.value.includes(t.id)) selectedTrucks.value.push(t.id) }); else selectedTrucks.value = selectedTrucks.value.filter(id=>!paginatedEntries.value.some(t=>t.id===id)) }
  
  function openCreateModal(){ form.reset(); form.clearErrors(); form.id=null; showModal.value=true }
  function openEditModal(item:any){
    form.reset(); 
    form.clearErrors();
    form.id = item.id;
    form.truckid = item.truckid;
    form.type = item.type ? item.type.toLowerCase() : '';
    form.make = item.make ? item.make.toLowerCase() : '';
    form.fuel = item.fuel;
    form.license = item.license;
    form.vin = item.vin;
    form.status = item.status; // Use status instead of is_active and is_returned
    form.tenant_id = item.tenant_id;
    form.inspection_status = item.inspection_status || 'good';
    form.inspection_expiry_date = item.inspection_expiry_date || new Date().toISOString().split('T')[0];
 showModal.value=true }
  function closeModal(){ showModal.value=false }
  
  function submitForm(){
    const data = {...form.data(), truckid:Number(form.truckid), license:Number(form.license)}
    const action = form.id ? 'put' : 'post'
    const url = form.id ? props.SuperAdmin? route('truck.update.admin', [ form.id ]):route('truck.update',[props.tenantSlug, form.id]) : route(props.SuperAdmin?'truck.store.admin':'truck.store',{ tenantSlug:props.tenantSlug })
    isSubmitting.value=true
    form[action](url,{ data, onSuccess:()=>{ successMessage.value= form.id?'Truck updated.':'Truck created.'; closeModal() }, onError:()=>errorMessage.value='Save failed', onFinish:()=>isSubmitting.value=false }) 
  }
  
  function confirmDelete(item:any){ deleteTarget.value=item; showDeleteModal.value=true }
  function deleteEntry(){ if(!deleteTarget.value) return; const url = props.SuperAdmin? route('truck.destroy.admin',[ deleteTarget.value.id ]) :  route('truck.destroy',[props.tenantSlug,deleteTarget.value.id])
    form.delete(url,{ onSuccess:()=>{ successMessage.value='Deleted successfully'; showDeleteModal.value=false }} )
  }
  
  function confirmDeleteSelected(){ if(selectedTrucks.value.length) showBulkDeleteModal.value=true }
  function deleteSelectedTrucks(){ const url = route(props.SuperAdmin?'truck.destroyBulk.admin':'truck.destroyBulk',{ tenantSlug:props.tenantSlug })
    useForm({ids:selectedTrucks.value}).delete(url,{ onSuccess:()=>{ successMessage.value=`${selectedTrucks.value.length} deleted`; selectedTrucks.value=[]; showBulkDeleteModal.value=false } })
  }
  
  function handleImport(e:Event){ const file=(e.target as HTMLInputElement).files?.[0]; if(!file) return; importForm.csv_file=file; importForm.post(route(props.SuperAdmin?'truck.import.admin':'truck.import',{ tenantSlug:props.tenantSlug }),{ forceFormData:true, onSuccess:()=>successMessage.value='Imported', onError:()=>errorMessage.value='Import failed' }) }
  function exportCSV(){ if(!paginatedEntries.value.length){ errorMessage.value='No data to export'; return } exportForm.value?.setAttribute('action', route(props.SuperAdmin?'truck.export.admin':'truck.export',{ tenantSlug:props.tenantSlug })); exportForm.value?.submit() }
  
  const templateUrl = computed(() => '/storage/upload-data-temps/Trucks Template.csv')
  
  onMounted(()=>{
    const handler=(e:Event)=>{ if(showUploadOpts.value && !(e.target as HTMLElement).closest('.relative')) showUploadOpts.value=false }
    document.addEventListener('click',handler)
    onUnmounted(()=>document.removeEventListener('click',handler))
  })
  const displayedPageNumbers = computed(() => {
  if (totalPages.value <= 7) {
    // If we have 7 or fewer pages, show all page numbers
    return Array.from({ length: totalPages.value }, (_, i) => i + 1);
  }
  
  // Otherwise, show a window of pages around the current page
  const pageWindow = [];
  
  // Always show first page
  pageWindow.push(1);
  
  // If not on first few pages, show ellipsis
  if (page.value > 3) {
    pageWindow.push('...');
  }
  
  // Calculate window around current page
  const start = Math.max(2, page.value - 1);
  const end = Math.min(totalPages.value - 1, page.value + 1);
  
  for (let i = start; i <= end; i++) {
    pageWindow.push(i);
  }
  
  // If not on last few pages, show ellipsis
  if (page.value < totalPages.value - 2) {
    pageWindow.push('...');
  }
  
  // Always show last page if we have more than 1 page
  if (totalPages.value > 1) {
    pageWindow.push(totalPages.value);
  }
  
  return pageWindow;
});
  </script>
  