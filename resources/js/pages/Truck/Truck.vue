<template>
  <AppLayout
    :breadcrumbs="breadcrumbs"
    :tenantSlug="tenantSlug"
    :permissions="props.permissions"
  >
    <Head title="Trucks" />
    <!-- responsive here -->
    <div
      class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto m-0 p-2 md:p-4 lg:p-6 space-y-2 md:space-y-4 lg:space-y-6"
    >
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
      <div
        class="flex flex-col sm:flex-row justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6"
      >
        <!-- responsive here -->
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold">Truck Management</h1>
        <div class="flex flex-wrap gap-3">
          <!-- responsive here -->
          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            @click="openCreateModal"
            variant="default"
          >
            <!-- responsive here -->
            <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" />
            Create New Truck
          </Button>

          <!-- Add Delete Selected button -->
          <!-- responsive here -->
          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            v-if="selectedTrucks.length > 0"
            @click="confirmDeleteSelected()"
            variant="destructive"
          >
            <!-- responsive here -->
            <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" />
            Delete Selected ({{ selectedTrucks.length }})
          </Button>

          <Button
            @click="showImportModal = true"
            v-if="permissionNames.includes('trucks.import')"
            variant="secondary"
            class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
          >
            <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2" />
            Import CSV
          </Button>

          <!-- responsive here -->
          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            @click.prevent="exportCSV"
            variant="outline"
          >
            <!-- responsive here -->
            <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" />
            Download CSV
          </Button>
        </div>
      </div>

      <!-- Filters Section -->
      <Card>
        <!-- responsive here -->
        <CardHeader class="p-2 md:p-4 lg:p-6">
          <!-- responsive here -->
          <CardTitle class="text-lg md:text-xl lg:text-2xl">Filters</CardTitle>
        </CardHeader>
        <!-- responsive here -->
        <CardContent class="p-2 md:p-4 lg:p-6">
          <!-- responsive here -->
          <div class="flex flex-col gap-1 md:gap-4">
            <!-- responsive here -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 md:gap-4 w-full">
              <div>
                <Label for="search">Search</Label>
                <!-- responsive here -->
                <Input
                  class="py-1 px-1 md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10"
                  id="search"
                  v-model="filters.search"
                  type="text"
                  placeholder="Search by truck ID or VIN..."
                  @input="applyFilters"
                />
              </div>
              <div>
                <Label for="type">Type</Label>
                <select
                  id="type"
                  v-model="filters.type"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  @change="applyFilters"
                >
                  <option value="">All Types</option>
                  <option value="daycab">Daycab</option>
                  <option value="sleepercab">Sleepercab</option>
                </select>
              </div>
              <div>
                <Label for="make">Make</Label>
                <select
                  id="make"
                  v-model="filters.make"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  @change="applyFilters"
                >
                  <option value="">All Makes</option>
                  <option value="international">International</option>
                  <option value="kenworth">Kenworth</option>
                  <option value="peterbilt">Peterbilt</option>
                  <option value="volvo">Volvo</option>
                  <option value="freightliner">Freightliner</option>
                </select>
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
            <Table class="relative h-[500px] overflow-auto">
              <TableHeader>
                <TableRow
                  class="sticky top-0 z-10 border-b bg-background hover:bg-background"
                >
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
                  <!-- In the TableHead section, update the column label -->
                  <TableHead
                    v-for="col in tableColumns"
                    :key="col"
                    class="cursor-pointer"
                    @click="sortBy(col)"
                  >
                    <div class="flex items-center">
                      <template v-if="col === 'inspection_status'">
                        Annual Inspection Status
                      </template>
                      <template v-else-if="col === 'inspection_expiry_date'">
                        Annual Inspection Expiration Date
                      </template>
                      <template v-else-if="col === 'status'"> Status </template>
                      <template v-else>
                        {{
                          col.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase())
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
                        <svg
                          class="h-4 w-4"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                        >
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
                <TableRow v-if="filteredEntries.length === 0">
                  <TableCell
                    :colspan="
                      SuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2
                    "
                    class="py-8 text-center"
                  >
                    No trucks found matching your criteria
                  </TableCell>
                </TableRow>
                <TableRow
                  v-for="item in filteredEntries"
                  :key="item.id"
                  class="hover:bg-muted/50"
                >
                  <!-- Add checkbox for selecting individual row -->
                  <TableCell class="text-center">
                    <input
                      type="checkbox"
                      :value="item.id"
                      v-model="selectedTrucks"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                  </TableCell>
                  <TableCell v-if="SuperAdmin">
                    {{ item.tenant?.name ?? "—" }}
                  </TableCell>
                  <!-- In the TableCell section, add handling for the new columns -->
                  <TableCell
                    v-for="col in tableColumns"
                    :key="col"
                    class="whitespace-nowrap"
                  >
                    <template v-if="col === 'status'">
                      <span
                        :class="{
                          'text-green-600': item[col] === 'active',
                          'text-red-600': item[col] === 'inactive',
                          'text-blue-600': item[col] === 'Returned to AMZ',
                        }"
                      >
                        {{ item[col] }}
                      </span>
                    </template>
                    <template v-else-if="col === 'inspection_status'">
                      <span
                        :class="item[col] === 'good' ? 'text-green-600' : 'text-red-600'"
                      >
                        {{ item[col] === "good" ? "Good" : "Expired" }}
                      </span>
                    </template>
                    <template v-else-if="col === 'inspection_expiry_date'">
                      {{ formatDate(item[col]) }}
                    </template>
                    <template v-else>
                      {{
                        typeof item[col] === "string"
                          ? item[col].charAt(0).toUpperCase() + item[col].slice(1)
                          : item[col]
                      }}
                    </template>
                  </TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Button @click="openEditModal(item)" variant="warning" size="sm">
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>
                      <Button
                        @click="deleteEntry(item.id)"
                        variant="destructive"
                        size="sm"
                      >
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
          <div class="border-t bg-muted/20 px-4 py-3" v-if="entries.links">
            <!-- responsive here -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
              <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <span
                  >Showing {{ filteredEntries.length }} of
                  {{ entries.data.length }} entries</span
                >
                <!-- responsive here -->
                <div
                  class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 w-full sm:w-auto"
                >
                  <span class="text-sm">Show:</span>
                  <select
                    v-model="perPage"
                    @change="changePerPage"
                    class="h-8 rounded-md border border-input bg-background px-2 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                  >
                    <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">
                      {{ size }}
                    </option>
                  </select>
                </div>
              </div>
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
        <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">{{ formTitle }}</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Fill in the details to {{ formAction.toLowerCase() }} a truck.
            </DialogDescription>
          </DialogHeader>

          <form
            @submit.prevent="submitForm"
            class="grid max-h-[75vh] grid-cols-1 gap-2 overflow-y-auto p-4 sm:grid-cols-2 sm:gap-3 sm:p-6"
          >
            <div v-if="SuperAdmin" class="col-span-2">
              <Label for="tenant">Company Name</Label>
              <div class="relative">
                <select
                  id="tenant"
                  v-model="form.tenant_id"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <option value="">Select Company</option>
                  <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                    {{ tenant.name }}
                  </option>
                </select>
                <div
                  class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                  <svg
                    class="h-4 w-4 opacity-50"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
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
              <Label for="truckid">Truck ID</Label>
              <Input id="truckid" v-model.number="form.truckid" type="number" required />
            </div>

            <div class="col-span-2 sm:col-span-1">
              <Label for="type">Type</Label>
              <div class="relative">
                <select
                  id="type"
                  v-model="form.type"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="daycab">Daycab</option>
                  <option value="sleepercab">Sleepercab</option>
                </select>
                <div
                  class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                  <svg
                    class="h-4 w-4 opacity-50"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
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
              <Label for="make">Make</Label>
              <div class="relative">
                <select
                  id="make"
                  v-model="form.make"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="international">International</option>
                  <option value="kenworth">Kenworth</option>
                  <option value="peterbilt">Peterbilt</option>
                  <option value="volvo">Volvo</option>
                  <option value="freightliner">Freightliner</option>
                </select>
                <div
                  class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                  <svg
                    class="h-4 w-4 opacity-50"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
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
              <Label for="fuel">Fuel</Label>
              <div class="relative">
                <select
                  id="fuel"
                  v-model="form.fuel"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="diesel">Diesel</option>
                  <option value="cng">CNG</option>
                </select>
                <div
                  class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                  <svg
                    class="h-4 w-4 opacity-50"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
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
              <Label for="license">License</Label>
              <Input
                id="license"
                v-model.number="form.license"
                type="number"
                min="0"
                required
              />
            </div>

            <div class="col-span-2 sm:col-span-1">
              <Label for="vin">VIN</Label>
              <Input id="vin" v-model="form.vin" type="text" required />
            </div>

            <!-- Add the new inspection fields here -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="inspection_status">Annual Inspection Status</Label>
              <div class="relative">
                <select
                  id="inspection_status"
                  v-model="form.inspection_status"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="good">Good</option>
                  <option value="expired">Expired</option>
                </select>
                <div
                  class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                  <svg
                    class="h-4 w-4 opacity-50"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
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
              <Label for="inspection_expiry_date">Annual Inspection Expiry Date</Label>
              <Input
                id="inspection_expiry_date"
                v-model="form.inspection_expiry_date"
                type="date"
                required
              />
            </div>

            <!-- Replace is_active and is_returned checkboxes with status dropdown -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="status">Status</Label>
              <div class="relative">
                <select
                  id="status"
                  v-model="form.status"
                  class="flex h-10 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  required
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="Returned to AMZ">Returned to AMZ</option>
                </select>
                <div
                  class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                  <svg
                    class="h-4 w-4 opacity-50"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
              </div>
            </div>

            <DialogFooter class="col-span-2 mt-4">
              <Button type="button" @click="closeModal" variant="outline">
                Cancel
              </Button>
              <Button type="submit" variant="default">
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
              Are you sure you want to delete this truck? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="mt-4">
            <Button type="button" @click="showDeleteModal = false" variant="outline">
              Cancel
            </Button>
            <Button type="button" @click="confirmDelete" variant="destructive">
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- After the Delete Confirmation Dialog -->
      <!-- Delete Selected Confirmation Dialog -->
      <Dialog v-model:open="showDeleteSelectedModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Bulk Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete {{ selectedTrucks.length }} trucks? This
              action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="mt-4">
            <Button
              type="button"
              @click="showDeleteSelectedModal = false"
              variant="outline"
            >
              Cancel
            </Button>
            <Button type="button" @click="deleteSelectedTrucks()" variant="destructive">
              Delete Selected
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <Dialog v-model:open="showImportModal">
        <DialogContent
          class="max-w-[95vw] sm:max-w-[90vw] md:max-w-5xl max-h-[90vh] overflow-hidden flex flex-col"
        >
          <DialogHeader class="px-4 sm:px-6 border-b pb-3">
            <div class="flex items-center gap-2">
              <Icon name="upload" class="h-5 w-5 text-primary" />
              <DialogTitle class="text-lg sm:text-xl font-semibold">
                Import Trucks
              </DialogTitle>
            </div>
            <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">
              Upload a CSV file to import trucks. The file will be validated before
              import.
            </DialogDescription>
          </DialogHeader>

          <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4">
            <!-- Step 1 -->
            <div v-if="!importValidationResults">
              <div class="space-y-4">
                <div
                  class="flex flex-col items-center justify-center border-2 border-dashed rounded-lg p-8 bg-muted/20"
                >
                  <Icon
                    name="file-spreadsheet"
                    class="h-12 w-12 text-muted-foreground mb-3"
                  />
                  <label class="cursor-pointer">
                    <span class="text-sm font-medium text-primary hover:underline"
                      >Choose CSV file</span
                    >
                    <input
                      type="file"
                      class="hidden"
                      @change="validateImportFile"
                      accept=".csv,.txt"
                      :disabled="isValidating"
                    />
                  </label>
                  <p class="text-xs text-muted-foreground mt-2">or drag and drop</p>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                  <Icon name="info" class="h-4 w-4" />
                  <a :href="templateUrl" download class="text-primary hover:underline">
                    Download CSV Template
                  </a>
                </div>

                <div
                  v-if="isValidating"
                  class="flex items-center justify-center gap-2 p-4"
                >
                  <div
                    class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
                  ></div>
                  <span class="text-sm text-muted-foreground"
                    >Validating CSV file...</span
                  >
                </div>
              </div>
            </div>

            <!-- Step 2 -->
            <div v-else class="space-y-4">
              <div
                v-if="importValidationResults.headers?.length"
                class="rounded-lg border p-3"
              >
                <div class="flex items-center justify-between">
                  <div class="text-sm font-semibold">CSV Headers</div>
                  <div class="text-xs text-muted-foreground">
                    {{ importValidationResults.headers.length }} columns
                  </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                  <span
                    v-for="h in importValidationResults.headers"
                    :key="h"
                    class="rounded-full bg-muted px-2 py-0.5 text-xs"
                  >
                    {{ h }}
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-3 gap-4">
                <Card class="border-2">
                  <CardContent class="p-4 text-center">
                    <div class="text-2xl font-bold">
                      {{ importValidationResults.summary.total }}
                    </div>
                    <div class="text-sm text-muted-foreground">Total Rows</div>
                  </CardContent>
                </Card>

                <Card
                  class="border-2 border-green-500/50 bg-green-50 dark:bg-green-900/10"
                >
                  <CardContent class="p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">
                      {{ importValidationResults.summary.valid }}
                    </div>
                    <div class="text-sm text-muted-foreground">Valid</div>
                  </CardContent>
                </Card>

                <Card class="border-2 border-red-500/50 bg-red-50 dark:bg-red-900/10">
                  <CardContent class="p-4 text-center">
                    <div class="text-2xl font-bold text-red-600">
                      {{ importValidationResults.summary.invalid }}
                    </div>
                    <div class="text-sm text-muted-foreground">Invalid</div>
                  </CardContent>
                </Card>
              </div>

              <Alert v-if="importValidationResults.header_error" variant="destructive">
                <AlertTitle class="flex items-center gap-2">
                  <Icon name="alert_circle" class="h-5 w-5" />
                  Header Error
                </AlertTitle>
                <AlertDescription>{{
                  importValidationResults.header_error
                }}</AlertDescription>
              </Alert>

              <div v-if="importValidationResults.invalid?.length">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-lg font-semibold text-red-600 flex items-center gap-2">
                    <Icon name="alert-triangle" class="h-5 w-5" />
                    Validation Errors ({{ importValidationResults.invalid.length }})
                  </h3>

                  <Button
                    @click="downloadErrorReport"
                    variant="outline"
                    size="sm"
                    class="flex items-center gap-2"
                  >
                    <Icon name="download" class="h-4 w-4" />
                    Download Error Report
                  </Button>
                </div>

                <div class="border rounded-lg overflow-hidden">
                  <div class="max-h-96 overflow-y-auto">
                    <Table>
                      <TableHeader class="sticky top-0 bg-background">
                        <TableRow>
                          <TableHead class="w-20">Row #</TableHead>
                          <TableHead>Preview</TableHead>
                          <TableHead>Errors</TableHead>
                        </TableRow>
                      </TableHeader>

                      <TableBody>
                        <TableRow
                          v-for="row in importValidationResults.invalid"
                          :key="row.rowNumber"
                          class="hover:bg-muted/50"
                        >
                          <TableCell class="font-medium">{{ row.rowNumber }}</TableCell>

                          <TableCell class="text-sm text-muted-foreground">
                            <div class="flex flex-wrap gap-x-3 gap-y-1">
                              <span
                                v-for="p in row.preview || []"
                                :key="p.key"
                                class="whitespace-nowrap"
                              >
                                <span class="font-medium text-foreground"
                                  >{{ p.label }}:</span
                                >
                                {{ p.value }}
                              </span>
                              <span
                                v-if="!row.preview?.length"
                                class="italic text-muted-foreground"
                                >—</span
                              >
                            </div>
                          </TableCell>

                          <TableCell>
                            <div class="space-y-1">
                              <div
                                v-for="(error, idx) in row.errors || []"
                                :key="idx"
                                class="text-xs text-red-600 flex items-start gap-1"
                              >
                                <Icon
                                  name="x-circle"
                                  class="h-3 w-3 mt-0.5 flex-shrink-0"
                                />
                                <span>{{ error }}</span>
                              </div>
                              <div
                                v-if="!row.errors?.length"
                                class="text-xs text-muted-foreground"
                              >
                                —
                              </div>
                            </div>
                          </TableCell>
                        </TableRow>
                      </TableBody>
                    </Table>
                  </div>
                </div>
              </div>

              <div v-if="importValidationResults.valid?.length">
                <h3
                  class="text-lg font-semibold text-green-600 flex items-center gap-2 mb-3"
                >
                  <Icon name="check-circle" class="h-5 w-5" />
                  Valid Rows ({{ importValidationResults.valid.length }})
                </h3>

                <div class="text-sm text-muted-foreground mb-2">
                  Showing first 5 valid rows
                </div>

                <div class="border rounded-lg overflow-hidden">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Row #</TableHead>
                        <TableHead>Preview</TableHead>
                      </TableRow>
                    </TableHeader>

                    <TableBody>
                      <TableRow
                        v-for="row in importValidationResults.valid.slice(0, 5)"
                        :key="row.rowNumber"
                      >
                        <TableCell class="font-medium">{{ row.rowNumber }}</TableCell>
                        <TableCell class="text-sm">
                          <div class="flex flex-wrap gap-x-3 gap-y-1">
                            <span
                              v-for="p in row.preview || []"
                              :key="p.key"
                              class="whitespace-nowrap"
                            >
                              <span class="font-medium">{{ p.label }}:</span>
                              {{ p.value }}
                            </span>
                            <span
                              v-if="!row.preview?.length"
                              class="italic text-muted-foreground"
                              >—</span
                            >
                          </div>
                        </TableCell>
                      </TableRow>
                    </TableBody>
                  </Table>
                </div>
              </div>
            </div>
          </div>

          <div class="border-t p-4 flex justify-end gap-3">
            <Button @click="closeImportModal" variant="outline" :disabled="isImporting"
              >Close</Button
            >

            <Button
              v-if="importValidationResults && importValidationResults.summary.valid > 0"
              @click="confirmImport"
              variant="default"
              :disabled="
                isImporting ||
                importValidationResults.summary.invalid > 0 ||
                Boolean(importValidationResults.header_error)
              "
              class="flex items-center gap-2"
            >
              <div
                v-if="isImporting"
                class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
              ></div>
              <Icon v-else name="check" class="h-4 w-4" />
              {{
                isImporting
                  ? "Importing..."
                  : `Import ${importValidationResults.summary.valid} Rows`
              }}
            </Button>
          </div>
        </DialogContent>
      </Dialog>

      <form ref="exportForm" method="GET" class="hidden" />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import Icon from "@/components/Icon.vue";
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
} from "@/components/ui";
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
  entries: Object,
  tenantSlug: String,
  SuperAdmin: Boolean,
  tenants: Array,
  perPage: { type: Number, default: 10 },
});

// Add this ref for perPage
const perPage = ref(props.perPage || 10);

// Add this function to handle per page changes
function changePerPage() {
  const routeName = props.SuperAdmin
    ? route("truck.index.admin")
    : route("truck.index", { tenantSlug: props.tenantSlug });

  router.get(
    routeName,
    {
      perPage: perPage.value,
    },
    { preserveState: true }
  );
}

// Update the visitPage function to preserve perPage
function visitPage(url) {
  if (url) {
    // Add perPage parameter to the URL
    const urlObj = new URL(url);
    urlObj.searchParams.set("perPage", perPage.value);

    router.get(urlObj.href, {}, { only: ["entries"] });
  }
}
const errorMessage = ref("");
const successMessage = ref("");
const showModal = ref(false);
const showDeleteModal = ref(false);
const formTitle = ref("Create Entry");
const formAction = ref("Create");
const exportForm = ref(null);
const truckToDelete = ref(null);
const pageProps = usePage();

const showImportModal = ref(false);
const importValidationResults = ref(null);
const isValidating = ref(false);
const isImporting = ref(false);
// Sorting state
const sortColumn = ref("truckid");
const sortDirection = ref("asc");

// Filtering state
const filters = ref({
  search: "",
  type: "",
  make: "",
});

const breadcrumbs = [
  {
    title: props.tenantSlug ? "Dashboard" : "Admin Dashboard",
    href: props.tenantSlug
      ? route("dashboard", { tenantSlug: props.tenantSlug })
      : route("admin.dashboard"),
  },
  {
    title: "Trucks",
    href: "#",
  },
];

// Update the tableColumns array to include the new columns
const tableColumns = [
  "truckid",
  "type",
  "make",
  "fuel",
  "license",
  "vin",
  "inspection_status",
  "inspection_expiry_date",
  "status",
];

// Update the form initialization to include the new field
const form = useForm({
  id: null,
  truckid: null,
  type: "daycab",
  make: "international",
  fuel: "diesel",
  license: null,
  vin: "",
  tenant_id: props.SuperAdmin ? "" : null,
  status: "active",
  inspection_status: "good",
  inspection_expiry_date: new Date().toISOString().split("T")[0], // Today's date as default
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
        String(item.truckid).includes(searchTerm) ||
        item.vin?.toLowerCase().includes(searchTerm)
    );
  }

  // Apply type filter
  if (filters.value.type) {
    result = result.filter(
      (item) => item.type?.toLowerCase() === filters.value.type.toLowerCase()
    );
  }

  // Apply make filter
  if (filters.value.make) {
    result = result.filter(
      (item) => item.make?.toLowerCase() === filters.value.make.toLowerCase()
    );
  }

  // Apply status filter
  if (filters.value.status) {
    result = result.filter((item) => item.status === filters.value.status);
  }

  // Apply sorting
  result.sort((a, b) => {
    let valA = a[sortColumn.value];
    let valB = b[sortColumn.value];

    // Handle null values
    if (valA === null) return 1;
    if (valB === null) return -1;

    // String comparison
    if (typeof valA === "string") {
      valA = valA.toLowerCase();
      valB = valB.toLowerCase();
    }

    if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
    if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });

  return result;
});

// Sort function
function sortBy(column) {
  if (sortColumn.value === column) {
    // Toggle direction if clicking the same column
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    // Set new column and default to ascending
    sortColumn.value = column;
    sortDirection.value = "asc";
  }
}

// Filter functions
function applyFilters() {
  // This function is triggered by input/change events
  // The filtering is handled by the computed property
}

function resetFilters() {
  filters.value = {
    search: "",
    type: "",
    make: "",
  };
}

function openCreateModal() {
  form.reset();
  form.status = "active"; // Set default status to active
  form.tenant_id = null;
  formTitle.value = "Create Truck";
  formAction.value = "Create";
  showModal.value = true;
}

// Update the openEditModal function to include the status field
function openEditModal(item) {
  form.id = item.id;
  form.truckid = item.truckid;
  form.type = item.type ? item.type.toLowerCase() : "";
  form.make = item.make ? item.make.toLowerCase() : "";
  form.fuel = item.fuel;
  form.license = item.license;
  form.vin = item.vin;
  form.status = item.status; // Use status instead of is_active and is_returned
  form.tenant_id = item.tenant_id;
  form.inspection_status = item.inspection_status || "good";
  form.inspection_expiry_date =
    item.inspection_expiry_date || new Date().toISOString().split("T")[0];

  formTitle.value = "Edit Truck";
  formAction.value = "Update";
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

// Update the submitForm function to include the status field in the payload
function submitForm() {
  const payload = {
    truckid: Number(form.truckid),
    type: form.type,
    make: form.make,
    fuel: form.fuel,
    license: Number(form.license),
    vin: form.vin,
    status: form.status, // Use status instead of is_active and is_returned
    tenant_id: form.tenant_id,
    inspection_status: form.inspection_status,
    inspection_expiry_date: form.inspection_expiry_date,
  };

  if (form.id) {
    form.put(
      props.SuperAdmin
        ? route("truck.update.admin", [form.id])
        : route("truck.update", [props.tenantSlug, form.id]),
      {
        data: payload,
        onSuccess: () => {
          successMessage.value = "Truck updated successfully.";
          closeModal();
        },
        onError: () => alert("Error updating truck"),
      }
    );
  } else {
    form.post(
      props.SuperAdmin
        ? route("truck.store.admin")
        : route("truck.store", props.tenantSlug),
      {
        data: payload,
        onSuccess: () => {
          successMessage.value = "Truck created successfully.";
          closeModal();
        },
        onError: () => alert("Error creating truck"),
      }
    );
  }
}

function deleteEntry(id) {
  truckToDelete.value = id;
  showDeleteModal.value = true;
}

function confirmDelete() {
  deleteForm.delete(
    props.SuperAdmin
      ? route("truck.destroy.admin", [truckToDelete.value])
      : route("truck.destroy", [props.tenantSlug, truckToDelete.value]),
    {
      onSuccess: () => {
        successMessage.value = "Truck deleted successfully.";
        showDeleteModal.value = false;
      },
    }
  );
}

function handleImport(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  importForm.csv_file = file;
  importForm.post(
    props.SuperAdmin
      ? route("truck.import.admin")
      : route("truck.import", props.tenantSlug),
    {
      forceFormData: true,
      onSuccess: () => (successMessage.value = "Data imported successfully."),
      onError: () => alert("Import failed"),
    }
  );
}

function exportCSV() {
  if (filteredEntries.value.length === 0) {
    errorMessage.value = "No data available to export";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
    return;
  }
  const routeName = props.SuperAdmin
    ? route("truck.export.admin")
    : route("truck.export", props.tenantSlug);
  exportForm.value?.setAttribute("action", routeName);
  exportForm.value?.submit();
}

// Add this function to format dates properly
function formatDate(dateString) {
  if (!dateString) return "—";

  // Fix timezone issue by parsing the date and adjusting for local timezone
  const date = new Date(dateString + "T00:00:00");

  // Format the date using toLocaleDateString
  return date.toLocaleDateString();
}

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = "";
    }, 5000);
  }
});

// Add these new refs
const selectedTrucks = ref([]);
const showDeleteSelectedModal = ref(false);

// Add this computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
  return (
    filteredEntries.value.length > 0 &&
    filteredEntries.value.every((truck) => selectedTrucks.value.includes(truck.id))
  );
});

// Add these new functions for bulk selection and deletion
function toggleSelectAll(event) {
  if (event.target.checked) {
    // Select all visible trucks (filtered entries)
    selectedTrucks.value = filteredEntries.value.map((truck) => truck.id);
  } else {
    // Deselect all
    selectedTrucks.value = [];
  }
}

function confirmDeleteSelected() {
  if (selectedTrucks.value.length > 0) {
    showDeleteSelectedModal.value = true;
  }
}

function deleteSelectedTrucks() {
  const bulkDeleteForm = useForm({
    ids: selectedTrucks.value,
  });

  const routeName = props.SuperAdmin ? "truck.destroyBulk.admin" : "truck.destroyBulk";
  const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };

  bulkDeleteForm.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = `${selectedTrucks.value.length} trucks deleted successfully.`;
      selectedTrucks.value = [];
      showDeleteSelectedModal.value = false;
    },
    onError: (errors) => {
      console.error(errors);
      alert("Error deleting trucks");
    },
  });
}
// Add these new refs and computed properties
const showUploadOptions = ref(false);

// Computed property for template URL
const templateUrl = computed(() => {
  return "/storage/upload-data-temps/Trucks Template.csv";
});

// Close dropdown when clicking outside
onMounted(() => {
  const handleClickOutside = (e) => {
    if (showUploadOptions.value && !e.target.closest(".relative")) {
      showUploadOptions.value = false;
    }
  };

  document.addEventListener("click", handleClickOutside);

  onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
  });
});

function validateImportFile(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (!file) return;

  isValidating.value = true;

  const formData = new FormData();
  formData.append("file", file);

  const endpoint = props.SuperAdmin
    ? route("truck.validateImport.admin")
    : route("truck.validateImport", { tenantSlug: props.tenantSlug });

  router.post(endpoint, formData, {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => {
      isValidating.value = false;
      (e.target as HTMLInputElement).value = "";
    },
    onError: () => {
      isValidating.value = false;
      errorMessage.value = "Failed to validate CSV file";
      (e.target as HTMLInputElement).value = "";
    },
  });
}

function confirmImport() {
  if (!importValidationResults.value) return;
  if (importValidationResults.value.header_error) return;
  if ((importValidationResults.value.summary?.invalid ?? 0) > 0) return;

  isImporting.value = true;

  const endpoint = props.SuperAdmin
    ? route("truck.confirmImport.admin")
    : route("truck.confirmImport", { tenantSlug: props.tenantSlug });

  router.post(
    endpoint,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        successMessage.value = `Successfully imported ${
          importValidationResults.value.summary?.valid ?? 0
        } rows`;
        closeImportModal();
      },
      onError: () => {
        errorMessage.value = "Import failed";
      },
      onFinish: () => {
        isImporting.value = false;
      },
    }
  );
}

function downloadErrorReport() {
  const endpoint = props.SuperAdmin
    ? route("truck.downloadErrorReport.admin")
    : route("truck.downloadErrorReport", { tenantSlug: props.tenantSlug });

  window.location.href = endpoint;
}

function closeImportModal() {
  showImportModal.value = false;
  importValidationResults.value = null;
  isValidating.value = false;
  isImporting.value = false;
}
watch(
  () => (pageProps.props as any).flash?.importValidation,
  (payload: any) => {
    if (!payload) return;
    if (payload.results) {
      importValidationResults.value = payload.results;
      if (payload.header_error)
        importValidationResults.value.header_error = payload.header_error;
      showImportModal.value = true;
    }
    if (payload.message) errorMessage.value = payload.message;
  },
  { immediate: true }
);
</script>
