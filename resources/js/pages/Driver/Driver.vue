<template>
  <AppLayout
    :breadcrumbs="breadcrumbs"
    :tenantSlug="tenantSlug"
    :permissions="props.permissions"
  >
    <Head title="Drivers" />
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
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold">Driver Management</h1>
        <div class="flex flex-wrap gap-3">
          <!-- responsive here -->
          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            @click="openCreateModal"
            variant="default"
            v-if="permissionNames.includes('drivers.create')"
          >
            <!-- responsive here -->
            <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" />
            Create New Driver
          </Button>
          <!-- Add Delete Selected button -->
          <!-- responsive here -->
          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            v-if="
              selectedDrivers.length > 0 && permissionNames.includes('drivers.delete')
            "
            @click="confirmDeleteSelected()"
            variant="destructive"
          >
            <!-- responsive here -->
            <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" />
            Delete Selected ({{ selectedDrivers.length }})
          </Button>
          <Button
            @click="showImportModal = true"
            v-if="permissionNames.includes('drivers.import')"
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
            v-if="permissionNames.includes('drivers.export')"
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
                  placeholder="Search by name or email..."
                  @input="applyFilters"
                />
              </div>
              <div>
                <Label for="dateFrom">Hiring Date From</Label>
                <!-- responsive here -->
                <Input
                  class="py-1 px-1 md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10"
                  id="dateFrom"
                  v-model="filters.dateFrom"
                  type="date"
                  @change="applyFilters"
                />
              </div>
              <div>
                <Label for="dateTo">Hiring Date To</Label>
                <!-- responsive here -->
                <Input
                  class="py-1 px-1 md:px-2 md:py-1 h-9 lg:px-3 lg:py-2 lg:h-10"
                  id="dateTo"
                  v-model="filters.dateTo"
                  type="date"
                  @change="applyFilters"
                />
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
                <TableRow
                  class="sticky top-0 z-10 border-b bg-background hover:bg-background"
                >
                  <!-- Add checkbox column for selecting all -->
                  <TableHead
                    class="w-[50px]"
                    v-if="permissionNames.includes('drivers.delete')"
                  >
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
                  <TableHead
                    v-for="col in tableColumns"
                    :key="col"
                    class="cursor-pointer"
                    @click="sortBy(col)"
                  >
                    <div class="flex items-center">
                      <div v-if="col === 'hiring_date'">Hire Date</div>
                      <div v-else>
                        {{
                          col
                            .replace(/_/g, " ")
                            .split(" ")
                            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(" ")
                        }}
                      </div>
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
                  <TableHead
                    v-if="
                      permissionNames.includes('drivers.view') ||
                      permissionNames.includes('drivers.update') ||
                      permissionNames.includes('drivers.delete')
                    "
                  >
                    Actions
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="entries.data.length === 0">
                  <TableCell
                    :colspan="
                      SuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2
                    "
                    class="py-8 text-center"
                  >
                    No driver records found matching your criteria
                  </TableCell>
                </TableRow>
                <TableRow v-for="driver in entries.data" :key="driver.id">
                  <!-- Checkbox for row selection -->
                  <TableCell
                    class="text-center"
                    v-if="permissionNames.includes('drivers.delete')"
                  >
                    <input
                      type="checkbox"
                      :value="driver.id"
                      v-model="selectedDrivers"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                  </TableCell>
                  <!-- Company Name column for SuperAdmin -->
                  <TableCell v-if="SuperAdmin">{{
                    driver.tenant?.name ?? "—"
                  }}</TableCell>
                  <!-- Dynamic columns based on tableColumns array -->
                  <TableCell v-for="col in tableColumns" :key="col">
                    <template v-if="col === 'hiring_date'">
                      {{ formatDate(driver[col]) }}
                    </template>
                    <template v-else-if="col === 'mobile_phone'">
                      <div class="whitespace-nowrap min-w-[70px]">
                        {{ driver[col] }}
                      </div>
                    </template>
                    <template v-else>
                      {{ driver[col] }}
                    </template>
                  </TableCell>
                  <!-- Actions column -->
                  <TableCell
                    v-if="
                      permissionNames.includes('drivers.profile.view') ||
                      permissionNames.includes('drivers.update') ||
                      permissionNames.includes('drivers.delete')
                    "
                  >
                    <div class="flex space-x-2">
                      <!-- VIEW BUTTON -->
                      <Link
                        v-if="permissionNames.includes('drivers.profile.view')"
                        :href="
                          tenantSlug
                            ? route('driver.show', [tenantSlug, driver.id])
                            : route('driver.show.admin', driver.id)
                        "
                        class="flex items-center gap-1"
                      >
                        <Button
                          variant="default"
                          size="sm"
                          class="flex items-center gap-1"
                        >
                          <Eye class="h-4 w-4" />
                          <span>View</span>
                        </Button>
                      </Link>

                      <Button
                        @click="openEditModal(driver)"
                        variant="warning"
                        size="sm"
                        v-if="permissionNames.includes('drivers.update')"
                      >
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>
                      <Button
                        @click="deleteEntry(driver.id)"
                        variant="destructive"
                        size="sm"
                        v-if="permissionNames.includes('drivers.delete')"
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
          <!-- pagination -->
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
            <DialogTitle class="text-lg md:text-xl lg:text-2xl">{{
              formTitle
            }}</DialogTitle>
            <DialogDescription class="text-sm md:text-base">
              Fill in the details to {{ formAction.toLowerCase() }} a driver.
            </DialogDescription>
          </DialogHeader>

          <!-- SCROLLABLE WRAPPER ADDED HERE -->
          <div class="max-h-[80vh] overflow-y-auto pr-2">
            <form
              @submit.prevent="submitForm"
              class="grid grid-cols-1 gap-2 md:gap-4 sm:grid-cols-2 mt-2 md:mt-4"
            >
              <!-- Company (SuperAdmin) -->
              <div v-if="SuperAdmin" class="col-span-2">
                <Label for="tenant" class="text-sm md:text-base">Company</Label>
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
              </div>

              <!-- First Name -->
              <div>
                <Label for="first_name" class="text-sm md:text-base">First Name</Label>
                <Input
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  required
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2"
                />
              </div>

              <!-- Last Name -->
              <div>
                <Label for="last_name" class="text-sm md:text-base">Last Name</Label>
                <Input
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  required
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2"
                />
              </div>

              <!-- Email -->
              <div class="sm:col-span-2">
                <Label for="email" class="text-sm md:text-base">Email Address</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2"
                />
              </div>

              <!-- Password -->
              <div class="sm:col-span-2">
                <Label for="password" class="flex items-center">
                  <span>Password</span>
                  <span v-if="formAction === 'Create'" class="text-destructive ml-1"
                    >*</span
                  >
                </Label>
                <div class="relative">
                  <Input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Enter password"
                    class="w-full pr-10"
                    :class="{ 'border-destructive': form.errors.password }"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                  >
                    <Eye v-if="!showPassword" class="h-5 w-5" />
                    <EyeOff v-else class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- Netradyne User Name -->
              <div class="sm:col-span-2">
                <Label for="netradyne_user_name" class="text-sm md:text-base"
                  >Netradyne User Name</Label
                >
                <Input
                  id="netradyne_user_name"
                  v-model="form.netradyne_user_name"
                  type="text"
                  required
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2"
                />
              </div>

              <!-- Mobile Phone -->
              <div class="sm:col-span-2">
                <Label for="mobile_phone" class="text-sm md:text-base"
                  >Mobile Phone Number</Label
                >
                <Input
                  id="mobile_phone"
                  v-model="form.mobile_phone"
                  type="tel"
                  required
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2"
                  @input="onPhoneInput"
                />
              </div>

              <!-- Hiring Date -->
              <div class="sm:col-span-2">
                <Label for="hiring_date" class="text-sm md:text-base">Hiring Date</Label>
                <Input
                  id="hiring_date"
                  v-model="form.hiring_date"
                  type="date"
                  required
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2"
                />
              </div>

              <!-- Driver Image -->
              <div class="sm:col-span-2">
                <Label for="image" class="text-sm md:text-base">Driver Image</Label>
                <Input
                  id="image"
                  type="file"
                  accept="image/*"
                  @change="onImageChange"
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-3 py-1 md:py-2"
                />

                <!-- Image preview -->
                <div v-if="imagePreview" class="mt-2">
                  <img
                    :src="imagePreview"
                    alt="Image Preview"
                    class="h-24 w-24 object-cover rounded-md"
                  />
                </div>
              </div>

              <!-- Footer buttons -->
              <DialogFooter
                class="col-span-2 mt-4 flex flex-col sm:flex-row gap-2 sm:gap-4 justify-end"
              >
                <Button
                  type="button"
                  @click="closeModal"
                  variant="outline"
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"
                  >Cancel</Button
                >
                <Button
                  type="submit"
                  variant="default"
                  class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"
                >
                  {{ formAction }}
                </Button>
              </DialogFooter>
            </form>
          </div>
        </DialogContent>
      </Dialog>

      <!-- Delete Confirmation Dialog -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md mx-auto p-2 md:p-4 lg:p-6">
          <DialogHeader class="space-y-1 md:space-y-2">
            <DialogTitle class="text-lg md:text-xl lg:text-2xl"
              >Confirm Deletion</DialogTitle
            >
            <DialogDescription class="text-sm md:text-base">
              Are you sure you want to delete this driver? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter
            class="mt-2 md:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-4 justify-end"
          >
            <Button
              type="button"
              @click="showDeleteModal = false"
              variant="outline"
              class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"
            >
              Cancel
            </Button>
            <Button
              type="button"
              @click="confirmDelete"
              variant="destructive"
              class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"
            >
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Add Delete Selected Confirmation Dialog -->
      <Dialog v-model:open="showDeleteSelectedModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md mx-auto p-2 md:p-4 lg:p-6">
          <DialogHeader class="space-y-1 md:space-y-2">
            <DialogTitle class="text-lg md:text-xl lg:text-2xl"
              >Confirm Bulk Deletion</DialogTitle
            >
            <DialogDescription class="text-sm md:text-base">
              Are you sure you want to delete {{ selectedDrivers.length }} driver records?
              This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter
            class="mt-2 md:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-4 justify-end"
          >
            <Button
              type="button"
              @click="showDeleteSelectedModal = false"
              variant="outline"
              class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"
            >
              Cancel
            </Button>
            <Button
              type="button"
              @click="deleteSelectedDrivers()"
              variant="destructive"
              class="h-8 md:h-10 text-xs md:text-sm px-2 md:px-4 py-1 md:py-2"
            >
              Delete Selected
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <!-- Import Validation Modal -->

      <form ref="exportForm" method="GET" class="hidden" />
    </div>
    <Dialog v-model:open="showImportModal">
      <DialogContent
        class="max-w-[95vw] sm:max-w-[90vw] md:max-w-5xl max-h-[90vh] overflow-hidden flex flex-col"
      >
        <DialogHeader class="px-4 sm:px-6 border-b pb-3">
          <div class="flex items-center gap-2">
            <Icon name="upload" class="h-5 w-5 text-primary" />
            <DialogTitle class="text-lg sm:text-xl font-semibold">
              Import Drivers
            </DialogTitle>
          </div>
          <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">
            Upload a CSV file to import drivers. The file will be validated before import.
          </DialogDescription>
        </DialogHeader>

        <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4">
          <!-- Step 1: File Upload -->
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
                  <span class="text-sm font-medium text-primary hover:underline">
                    Choose CSV file
                  </span>
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

              <div v-if="isValidating" class="flex items-center justify-center gap-2 p-4">
                <div
                  class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
                ></div>
                <span class="text-sm text-muted-foreground">Validating CSV file...</span>
              </div>
            </div>
          </div>

          <!-- Step 2: Validation Results -->
          <div v-else class="space-y-4">
            <!-- CSV Headers chips -->
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

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4">
              <Card class="border-2">
                <CardContent class="p-4 text-center">
                  <div class="text-2xl font-bold">
                    {{ importValidationResults.summary.total }}
                  </div>
                  <div class="text-sm text-muted-foreground">Total Rows</div>
                </CardContent>
              </Card>

              <Card class="border-2 border-green-500/50 bg-green-50 dark:bg-green-900/10">
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

            <!-- Header Error -->
            <Alert v-if="importValidationResults.header_error" variant="destructive">
              <AlertTitle class="flex items-center gap-2">
                <Icon name="alert_circle" class="h-5 w-5" />
                Header Error
              </AlertTitle>
              <AlertDescription>{{
                importValidationResults.header_error
              }}</AlertDescription>
            </Alert>

            <!-- Invalid Rows -->
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

            <!-- Valid Rows Preview -->
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

        <!-- Modal Footer -->
        <div class="border-t p-4 flex justify-end gap-3">
          <Button @click="closeImportModal" variant="outline" :disabled="isImporting">
            Close
          </Button>

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
  </AppLayout>
</template>

<script setup>
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
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { Eye, EyeOff } from "lucide-vue-next";
import { usePage } from "@inertiajs/vue3";

// Import or create Select components

const props = defineProps({
  entries: Object,
  tenantSlug: String,
  SuperAdmin: Boolean,
  tenants: Array,
  permissions: Array,
});

// Add activeTab ref
const activeTab = ref(props.dateFilter || "full");

const perPage = ref(props.perPage || 10);
// UI state
const viewUrl = (driver) =>
  tenantSlug
    ? route("driver.show", [tenantSlug, driver.id])
    : route("driver.show.admin", driver.id);

const selectedDrivers = ref([]);
const showDeleteSelectedModal = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const showModal = ref(false);
const showDeleteModal = ref(false);
const formTitle = ref("Create Driver");
const formAction = ref("Create");
const exportForm = ref(null);
const driverToDelete = ref(null);

// Sorting state
const sortColumn = ref("last_name");
const sortDirection = ref("asc");
const page = usePage();

const showImportModal = ref(false);
const importValidationResults = ref(null);
const isValidating = ref(false);
const isImporting = ref(false);
// Filtering state
const filters = ref({
  search: "",
  dateFrom: "",
  dateTo: "",
});

const breadcrumbs = [
  {
    title: props.tenantSlug ? "Dashboard" : "Admin Dashboard",
    href: props.tenantSlug
      ? route("dashboard", { tenantSlug: props.tenantSlug })
      : route("admin.dashboard"),
  },
  {
    title: "Drivers",
    href: props.tenantSlug
      ? route("driver.index", { tenantSlug: props.tenantSlug })
      : route("driver.index.admin"),
  },
];

// Define columns for the data table
const columns = computed(() => {
  const baseColumns = [
    {
      accessorKey: "first_name",
      header: "First Name",
      cell: (info) => info.getValue(),
      enableSorting: true,
    },
    {
      accessorKey: "last_name",
      header: "Last Name",
      cell: (info) => info.getValue(),
      enableSorting: true,
    },
    {
      accessorKey: "email",
      header: "Email",
      cell: (info) => info.getValue(),
      enableSorting: true,
    },
    {
      accessorKey: "mobile_phone",
      header: "Mobile Phone",
      cell: (info) => info.getValue(),
      enableSorting: true,
    },
    {
      accessorKey: "hiring_date",
      header: "Hiring Date",
      cell: (info) => formatDate(info.getValue()),
      enableSorting: true,
    },
    {
      id: "actions",
      header: "Actions",
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
      accessorKey: "tenant.name",
      header: "Company Name",
      cell: (info) => info.row.original.tenant?.name ?? "—",
      enableSorting: true,
    });
  }

  return baseColumns;
});

const tableColumns = [
  "first_name",
  "last_name",
  "email",
  "netradyne_user_name",
  "mobile_phone",
  "hiring_date",
];

const form = useForm({
  id: null,
  first_name: "",
  last_name: "",
  email: "",
  mobile_phone: "",
  hiring_date: "",
  netradyne_user_name: "",
  tenant_id: null,
  password: "", // NEW
  image: null, // NEW
});

const importForm = useForm({
  csv_file: null,
});
const showPassword = ref(false);

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
        item.email?.toLowerCase().includes(searchTerm)
    );
  }

  // Apply date filters
  if (filters.value.dateFrom) {
    result = result.filter(
      (item) => item.hiring_date && item.hiring_date >= filters.value.dateFrom
    );
  }

  if (filters.value.dateTo) {
    result = result.filter(
      (item) => item.hiring_date && item.hiring_date <= filters.value.dateTo
    );
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
    dateFrom: "",
    dateTo: "",
  };
}

function openCreateModal() {
  form.reset();
  form.tenant_id = null;
  form.netradyne_user_name = "";
  formTitle.value = "Create Driver";
  formAction.value = "Create";
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
  form.netradyne_user_name = item.netradyne_user_name;
  form.password = item.password;
  formTitle.value = "Edit Driver";
  formAction.value = "Update";
  // Set imagePreview if there's an image
  if (item.image) {
    imagePreview.value = `/storage/${item.image}`; // Show existing image in the modal
  } else {
    imagePreview.value = null; // No image to preview
  }
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  form.reset(); // Add this to clear form
  imagePreview.value = null; // Clear image preview
}

function submitForm() {
  // Handle undefined password as null
  if (form.password === undefined) {
    form.password = null;
  }

  // Create a FormData object to handle file uploads
  const formData = new FormData();

  // Append form data to the FormData object
  Object.keys(form.data()).forEach((key) => {
    if (key === "image" && form.image instanceof File) {
      formData.append("image", form.image); // Add image if it's a valid File object
    } else if (form[key] !== undefined && form[key] !== null) {
      formData.append(key, form[key]); // Append other form fields
    }
  });

  // If it's an update, make sure to append the ID
  if (form.id) {
    formData.append("id", form.id);
  }

  const isCreate = formAction.value === "Create"; // Check if the action is Create or Update
  const routeName = isCreate
    ? props.SuperAdmin
      ? route("driver.store.admin") // For super admin create route
      : route("driver.store", props.tenantSlug) // For tenant create route
    : props.SuperAdmin
    ? route("driver.update.admin", [form.id]) // For super admin update route
    : route("driver.update", [props.tenantSlug, form.id]); // For tenant update route

  const method = isCreate ? "post" : "post"; // Set method based on create or update

  // Submit the form
  form[method](routeName, {
    data: formData, // Send FormData object
    forceFormData: true, // Ensure the form uses FormData for submission
    onSuccess: () => {
      successMessage.value = isCreate
        ? "Driver created successfully."
        : "Driver updated successfully.";
      closeModal(); // Close modal after successful submission
    },
    onError: (errors) => {
      console.error("Form submission error:", errors);
      if (errors && Object.keys(errors).length > 0) {
        // Show the first error message
        const firstError = Object.values(errors)[0];
        errorMessage.value = firstError;
      } else {
        errorMessage.value = "An error occurred. Please try again."; // Default error message
      }
    },
  });
}

function deleteEntry(id) {
  driverToDelete.value = id;
  showDeleteModal.value = true;
}

function confirmDelete() {
  deleteForm.delete(
    props.SuperAdmin
      ? route("driver.destroy.admin", [driverToDelete.value])
      : route("driver.destroy", [props.tenantSlug, driverToDelete.value]),
    {
      onSuccess: () => {
        successMessage.value = "Driver deleted.";
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
      ? route("driver.import.admin")
      : route("driver.import", props.tenantSlug),
    {
      forceFormData: true,
      onSuccess: () => (successMessage.value = "Data imported successfully."),
      onError: () => alert("Import failed"),
    }
  );
}

function exportCSV() {
  if (props.entries.data.length === 0) {
    errorMessage.value = "No data available to export";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
    return;
  }
  const routeName = props.SuperAdmin
    ? route("driver.export.admin")
    : route("driver.export", props.tenantSlug);
  exportForm.value?.setAttribute("action", routeName);
  exportForm.value?.submit();
}

function visitPage(url) {
  if (url) {
    // Parse the URL to add perPage parameter
    const urlObj = new URL(url);
    urlObj.searchParams.set("perPage", perPage.value);

    // Use the modified URL with the perPage parameter
    router.get(
      urlObj.href,
      {},
      {
        only: ["entries"],
        preserveState: true,
      }
    );
  }
}

// Auto-hide success message after 5 seconds
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = "";
    }, 5000);
  }
});

// Format date string from YYYY-MM-DD to m/d/Y without using Date()
// to avoid timezone-related day shifts.
function formatDate(dateStr) {
  if (!dateStr) return "";
  const parts = dateStr.split("-");
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}

function onPhoneInput(e) {
  // 1) grab only digits, up to 10
  let digits = e.target.value.replace(/\D/g, "").slice(0, 10);

  // 2) build the formatted string
  let formatted = digits;
  if (digits.length > 6) {
    formatted = `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
  } else if (digits.length > 3) {
    formatted = `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
  } else if (digits.length > 0) {
    formatted = `(${digits}`;
  }

  // 3) overwrite both the visible input and your form state
  e.target.value = formatted;
  form.mobile_phone = formatted;
}

// Function to handle per page change
function changePerPage() {
  const routeName = props.tenantSlug
    ? route("driver.index", { tenantSlug: props.tenantSlug })
    : route("driver.index.admin");

  router.get(
    routeName,
    {
      dateFilter: activeTab.value,
      perPage: perPage.value,
    },
    { preserveState: true }
  );
}

// Computed property for "Select All" checkbox state
const isAllSelected = computed(() => {
  return (
    filteredEntries.value.length > 0 &&
    selectedDrivers.value.length === filteredEntries.value.length
  );
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

  const routeName = props.SuperAdmin ? "driver.destroyBulk.admin" : "driver.destroyBulk";
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
  return "/storage/upload-data-temps/Drivers Template.csv";
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

const permissionNames = computed(() => props.permissions.map((p) => p.name));

const imagePreview = ref(null);

function onImageChange(e) {
  const file = e.target.files[0];
  if (file) {
    form.image = file;

    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    form.image = null;
    imagePreview.value = null;
  }
}

function validateImportFile(event) {
  const target = event.target;
  const file = target.files?.[0];
  if (!file) return;

  isValidating.value = true;

  const formData = new FormData();
  formData.append("file", file);

  const endpoint = props.tenantSlug
    ? route("driver.validateImport", { tenantSlug: props.tenantSlug })
    : route("driver.validateImport.admin");

  router.post(endpoint, formData, {
    forceFormData: true,
    preserveScroll: true,
    only: ["flash"],
    onFinish: () => {
      isValidating.value = false;
    },
    onError: () => {
      isValidating.value = false;
      errorMessage.value = "Failed to validate CSV file";
    },
  });

  target.value = "";
}

function confirmImport() {
  if (!importValidationResults.value) return;
  if (importValidationResults.value.summary.invalid > 0) return;

  isImporting.value = true;

  const endpoint = props.tenantSlug
    ? route("driver.confirmImport", { tenantSlug: props.tenantSlug })
    : route("driver.confirmImport.admin");

  router.post(
    endpoint,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        isImporting.value = false;
        successMessage.value = `Successfully imported ${importValidationResults.value.summary.valid} drivers`;
        closeImportModal();
      },
      onError: () => {
        isImporting.value = false;
        errorMessage.value = "Failed to import drivers";
      },
    }
  );
}

function downloadErrorReport() {
  const endpoint = props.tenantSlug
    ? route("driver.downloadErrorReport", { tenantSlug: props.tenantSlug })
    : route("driver.downloadErrorReport.admin");

  window.location.href = endpoint;
}

function closeImportModal() {
  showImportModal.value = false;
  importValidationResults.value = null;
  isValidating.value = false;
  isImporting.value = false;
}

watch(
  () => page.props.flash?.importValidation,
  (payload) => {
    if (!payload) return;

    if (payload.results) {
      importValidationResults.value = payload.results;

      if (payload.header_error) {
        importValidationResults.value.header_error = payload.header_error;
      }

      showImportModal.value = true;
      return;
    }

    if (payload.message) errorMessage.value = payload.message;
  },
  { immediate: true }
);
</script>
