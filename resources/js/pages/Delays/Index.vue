<template>
  <AppLayout
    :breadcrumbs="breadcrumbs"
    :tenantSlug="tenantSlug"
    :permissions="props.permissions"
  >
    <Head title="On-Time" />

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
      <div
        class="mb-2 flex flex-col items-center justify-between px-2 sm:flex-row md:mb-4 lg:mb-6"
      >
        <h1
          class="text-lg font-bold text-gray-800 dark:text-gray-200 md:text-xl lg:text-2xl"
        >
          On-Time Management
        </h1>

        <div class="flex flex-wrap gap-3 ml-3">
          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            @click="openForm()"
            variant="default"
            v-if="permissionNames.includes('delays.create')"
          >
            <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" />
            Add Delay
          </Button>

          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            v-if="selectedDelays.length > 0 && permissionNames.includes('delays.delete')"
            @click="confirmDeleteSelected()"
            variant="destructive"
          >
            <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" />
            Delete Selected ({{ selectedDelays.length }})
          </Button>

          <Button
            @click="showImportModal = true"
            v-if="permissionNames.includes('delays.import')"
            variant="secondary"
            class="px-2 py-0 md:px-4 md:py-2 shadow-sm hover:shadow transition-all"
          >
            <Icon name="upload" class="mr-1 h-4 w-4 md:mr-2" />
            Import CSV
          </Button>

          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            @click.prevent="exportCSV"
            variant="outline"
            v-if="permissionNames.includes('delays.export')"
          >
            <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" />
            Download CSV
          </Button>

          <Button
            class="px-2 py-0 md:px-4 md:py-2"
            v-if="isSuperAdmin"
            @click="openCodeModal()"
            variant="outline"
          >
            <Icon name="settings" class="mr-1 h-4 w-4 md:mr-2" />
            Manage Delay Codes
          </Button>
        </div>
      </div>

      <!-- Hidden Export Form -->
      <form ref="exportForm" :action="exportUrl" method="GET" class="hidden"></form>

      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-2 md:p-4 lg:p-6">
          <div class="flex flex-col items-center gap-2 md:items-start">
            <div class="flex flex-wrap gap-1 md:gap-2">
              <Button
                @click="selectDateFilter('yesterday')"
                variant="outline"
                size="sm"
                :class="{
                  'border-primary bg-primary/10 text-primary': activeTab === 'yesterday',
                }"
              >
                Yesterday
              </Button>

              <Button
                @click="selectDateFilter('current-week')"
                variant="outline"
                size="sm"
                :class="{
                  'border-primary bg-primary/10 text-primary':
                    activeTab === 'current-week',
                }"
              >
                WTD
              </Button>

              <Button
                @click="selectDateFilter('6w')"
                variant="outline"
                size="sm"
                :class="{
                  'border-primary bg-primary/10 text-primary': activeTab === '6w',
                }"
              >
                T6W
              </Button>

              <Button
                @click="selectDateFilter('quarterly')"
                variant="outline"
                size="sm"
                :class="{
                  'border-primary bg-primary/10 text-primary': activeTab === 'quarterly',
                }"
              >
                Quarterly
              </Button>
            </div>

            <div v-if="dateRange" class="text-sm text-muted-foreground">
              <span v-if="activeTab === 'yesterday' && dateRange.start">
                Showing data from {{ formatDate(dateRange.start) }}
              </span>
              <span v-else-if="dateRange.start && dateRange.end">
                Showing data from {{ formatDate(dateRange.start) }} to
                {{ formatDate(dateRange.end) }}
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
        <CardHeader class="p-2 md:p-4 lg:p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <CardTitle class="text-lg md:text-xl lg:text-2xl">Filters</CardTitle>

              <div
                v-if="!showFilters && hasActiveFilters"
                class="ml-4 flex flex-wrap gap-2"
              >
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
                  Type: {{ filters.delayType === "origin" ? "Origin" : "Destination" }}
                </div>

                <div
                  v-if="filters.disputed"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                >
                  Disputed: {{ filters.disputed === "true" ? "Yes" : "No" }}
                </div>

                <div
                  v-if="filters.controllable"
                  class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
                >
                  Driver Controllable:
                  {{
                    filters.controllable === "true"
                      ? "Yes"
                      : filters.controllable === "false"
                      ? "No"
                      : "N/A"
                  }}
                </div>
              </div>
            </div>

            <Button variant="ghost" size="sm" @click="showFilters = !showFilters">
              {{ showFilters ? "Hide Filters" : "Show Filters" }}
              <Icon
                :name="showFilters ? 'chevron-up' : 'chevron-down'"
                class="ml-2 h-4 w-4"
              />
            </Button>
          </div>
        </CardHeader>

        <CardContent v-if="showFilters" class="p-2 md:p-4 lg:p-6">
          <div class="flex flex-col justify-between gap-1 md:gap-4">
            <div class="grid w-full grid-cols-1 gap-1 sm:grid-cols-3 md:gap-4">
              <div>
                <Label for="search">Search</Label>
                <Input
                  class="h-9 px-1 py-1 md:px-2 md:py-1 lg:h-10 lg:px-3 lg:py-2"
                  id="search"
                  v-model="filters.search"
                  type="text"
                  placeholder="Search by driver..."
                />
              </div>

              <div>
                <Label for="delayCode">Delay Code</Label>
                <select
                  id="delayCode"
                  v-model="filters.delayCode"
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
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">All Categories</option>
                  <option value="1_60">1-60 mins</option>
                  <option value="61_240">61-240 mins</option>
                  <option value="241_600">241-600 mins</option>
                  <option value="601_plus">601+ mins</option>
                </select>
              </div>

              <div>
                <Label for="delayType">Delay Type</Label>
                <select
                  id="delayType"
                  v-model="filters.delayType"
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
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                >
                  <option value="">All</option>
                  <option value="true">Yes</option>
                  <option value="false">No</option>
                  <option value="NA">N/A</option>
                </select>
              </div>
            </div>

            <div class="flex justify-end space-x-2">
              <Button @click="resetFilters" variant="ghost" size="sm">
                <Icon name="rotate_ccw" class="mr-2 h-4 w-4" />
                Reset Filters
              </Button>
              <Button @click="applyFilters" variant="default" size="sm">
                <Icon name="filter" class="mr-2 h-4 w-4" />
                Apply Filters
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
        :currentDateFilter="props.dateRange?.label"
        :delayType="filters.delayType"
      />

      <!-- Delays Table -->
      <Card class="mx-auto max-w-[95vw] overflow-x-auto md:max-w-[64vw] lg:max-w-full">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table class="relative h-[500px] overflow-auto">
              <TableHeader>
                <TableRow
                  class="sticky top-0 z-10 border-b bg-background hover:bg-background"
                >
                  <TableHead
                    class="w-[50px]"
                    v-if="permissionNames.includes('delays.delete')"
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

                  <TableHead v-if="isSuperAdmin">Company Name</TableHead>

                  <TableHead
                    v-for="col in tableColumns"
                    :key="col"
                    class="cursor-pointer"
                    @click="sortBy(col)"
                  >
                    <div class="flex items-center">
                      <template v-if="col === 'delay_category'"> Delay </template>
                      <template v-else>
                        {{
                          col
                            .replace(/_/g, " ")
                            .split(" ")
                            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                            .join(" ")
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

                  <TableHead
                    class="text-right"
                    v-if="
                      permissionNames.includes('delays.delete') ||
                      permissionNames.includes('delays.update')
                    "
                    >Actions</TableHead
                  >
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-if="filteredDelays.length === 0">
                  <TableCell
                    :colspan="
                      isSuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2
                    "
                    class="py-8 text-center text-primary font-medium"
                  >
                    No delays found matching your criteria
                  </TableCell>
                </TableRow>

                <TableRow
                  v-for="delay in filteredDelays"
                  :key="delay.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell
                    class="text-center"
                    v-if="permissionNames.includes('delays.delete')"
                  >
                    <input
                      type="checkbox"
                      :value="delay.id"
                      v-model="selectedDelays"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                  </TableCell>

                  <TableCell v-if="isSuperAdmin">
                    {{ delay.tenant?.name || "—" }}
                  </TableCell>

                  <TableCell
                    v-for="col in tableColumns"
                    :key="col"
                    class="whitespace-nowrap"
                  >
                    <template v-if="col === 'date'">
                      {{ formatDate(delay[col]) }}
                    </template>

                    <template v-else-if="col === 'disputed'">
                      {{ delay[col] ? "Yes" : "No" }}
                    </template>

                    <template v-else-if="col === 'driver_controllable'">
                      {{ delay[col] === null ? "N/A" : delay[col] ? "Yes" : "No" }}
                    </template>

                    <template v-else-if="col === 'delay_code'">
                      {{ delay.delay_code?.code || "—" }}
                      <span
                        v-if="delay.delay_code?.deleted_at"
                        class="ml-1 text-xs text-red-500"
                        >(Deleted Code)</span
                      >
                    </template>

                    <template v-else-if="col === 'delay_category'">
                      {{ formatDelayCategory(delay.delay_category) }}
                    </template>

                    <template v-else>
                      {{ delay[col] }}
                    </template>
                  </TableCell>

                  <TableCell
                    v-if="
                      permissionNames.includes('delays.update') ||
                      permissionNames.includes('delays.delete')
                    "
                    class="text-right"
                  >
                    <div class="flex justify-end space-x-2">
                      <Button
                        @click="openForm(delay)"
                        variant="warning"
                        size="sm"
                        v-if="permissionNames.includes('delays.update')"
                      >
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>

                      <Button
                        @click="confirmDelete(delay.id)"
                        variant="destructive"
                        size="sm"
                        v-if="permissionNames.includes('delays.delete')"
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
          <div class="border-t bg-muted/20 px-4 py-3" v-if="delays.links">
            <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
              <div class="text-sm text-muted-foreground">
                Showing {{ filteredDelays.length }} of {{ delays.data.length }} entries
              </div>

              <div
                class="flex w-full flex-col items-center gap-2 sm:w-auto sm:flex-row sm:gap-4"
              >
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

                <div class="flex flex-wrap">
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

      <!-- Delay Form Modal -->
      <Dialog v-model:open="formModal">
        <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl"
              >{{ selectedDelay ? "Edit" : "Add" }} Delay</DialogTitle
            >
            <DialogDescription class="text-xs sm:text-sm">
              Fill in the details to {{ selectedDelay ? "update" : "create" }} a delay
              record.
            </DialogDescription>
          </DialogHeader>

          <DelayForm
            :delay="selectedDelay"
            :delay-codes="activeDelayCodes"
            :tenants="tenants"
            :is-super-admin="isSuperAdmin"
            :tenant-slug="tenantSlug"
            @close="formModal = false"
            class="max-h-[75vh] overflow-y-auto p-4 sm:p-6"
          />
        </DialogContent>
      </Dialog>

      <!-- Code Manager Modal for Delay Codes -->
      <Dialog v-model:open="codeModal">
        <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-2xl">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Manage Delay Codes</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Create and manage delay codes for your organization.
            </DialogDescription>
          </DialogHeader>

          <div class="max-h-[75vh] space-y-4 overflow-y-auto p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium sm:text-base">Current Delay Codes</h3>
              <Button
                @click="openNewCodeForm"
                size="sm"
                variant="outline"
                class="h-8 px-3 text-xs sm:h-9 sm:px-4 sm:text-sm"
              >
                <Icon name="plus" class="mr-1 h-3 w-3 sm:h-3.5 sm:w-3.5" />
                Add New Code
              </Button>
            </div>

            <div class="max-h-[400px] overflow-y-auto rounded-md border">
              <div
                v-if="!delay_codes || delay_codes.length === 0"
                class="rounded-md border py-8 text-center text-xs text-muted-foreground sm:text-sm"
              >
                No delay codes found
              </div>

              <div v-else class="divide-y">
                <div
                  v-for="code in delay_codes"
                  :key="code.id"
                  class="group flex items-center justify-between p-3 hover:bg-muted/50"
                >
                  <div class="flex-1 cursor-pointer" @click="editCode(code)">
                    <div class="text-xs font-medium sm:text-sm">
                      {{ code.code }}
                      <span
                        v-if="code.deleted_at"
                        class="ml-2 text-[0.65rem] text-red-500 sm:text-xs"
                        >(Deleted)</span
                      >
                    </div>
                    <div
                      v-if="code.description"
                      class="mt-1 text-xs text-muted-foreground sm:text-sm"
                    >
                      {{ code.description }}
                    </div>
                  </div>

                  <div
                    class="flex space-x-1 opacity-0 transition-opacity group-hover:opacity-100"
                  >
                    <template v-if="isSuperAdmin">
                      <template v-if="code.deleted_at">
                        <Button
                          @click="restoreCode(code.id)"
                          size="sm"
                          variant="outline"
                          class="h-8 px-2 text-xs sm:h-9 sm:px-3 sm:text-sm"
                        >
                          <Icon name="refresh" class="mr-1 h-3 w-3 sm:h-3 sm:w-3" />
                          Restore
                        </Button>

                        <Button
                          @click="forceDeleteCode(code.id)"
                          size="sm"
                          variant="destructive"
                          class="h-8 px-2 text-xs sm:h-9 sm:px-3 sm:text-sm"
                        >
                          <Icon name="trash" class="mr-1 h-3 w-3 sm:h-3 sm:w-3" />
                          Delete
                        </Button>
                      </template>

                      <template v-else>
                        <Button
                          @click="confirmDeleteCode(code.id)"
                          size="sm"
                          variant="destructive"
                          class="h-8 px-2 text-xs sm:h-9 sm:px-3 sm:text-sm"
                        >
                          <Icon name="trash" class="mr-1 h-3 w-3 sm:h-3 sm:w-3" />
                          Delete
                        </Button>
                      </template>
                    </template>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="showCodeForm" class="space-y-4 rounded-md border p-4">
              <h3 class="text-sm font-medium sm:text-base">
                {{ editingCode ? "Edit" : "Add" }} Delay Code
              </h3>

              <div class="space-y-3">
                <div>
                  <Label for="code" class="text-xs sm:text-sm">Code</Label>
                  <Input
                    id="code"
                    v-model="codeForm.code"
                    placeholder="Enter code"
                    class="h-9 text-xs sm:h-10 sm:text-sm"
                  />
                </div>

                <div>
                  <Label for="description" class="text-xs sm:text-sm">Description</Label>
                  <Input
                    id="description"
                    v-model="codeForm.description"
                    placeholder="Enter description"
                    class="h-9 text-xs sm:h-10 sm:text-sm"
                  />
                </div>

                <div class="flex justify-end space-x-2">
                  <Button
                    @click="cancelCodeEdit"
                    variant="ghost"
                    size="sm"
                    class="h-8 px-3 text-xs sm:h-9 sm:px-4 sm:text-sm"
                    >Cancel</Button
                  >
                  <Button
                    @click="saveCode"
                    variant="default"
                    size="sm"
                    class="h-8 px-3 text-xs sm:h-9 sm:px-4 sm:text-sm"
                    >Save</Button
                  >
                </div>
              </div>
            </div>
          </div>

          <DialogFooter class="px-4 sm:px-6">
            <Button
              @click="codeModal = false"
              variant="outline"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
              >Close</Button
            >
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Delete Code Confirmation Dialog -->
      <Dialog v-model:open="codeDeleteConfirm">
        <DialogContent class="max-w-[95vw] sm:max-w-md">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Confirm Deletion</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Are you sure you want to delete this delay code? This action cannot be
              undone.
            </DialogDescription>
          </DialogHeader>

          <DialogFooter class="px-4 sm:px-6">
            <Button
              type="button"
              @click="codeDeleteConfirm = false"
              variant="outline"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Cancel
            </Button>
            <Button
              type="button"
              @click="deleteCode(codeToDelete)"
              variant="destructive"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Delete Delay Confirmation Dialog -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Confirm Deletion</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Are you sure you want to delete this delay record? This action cannot be
              undone.
            </DialogDescription>
          </DialogHeader>

          <DialogFooter class="px-4 sm:px-6">
            <Button
              type="button"
              @click="showDeleteModal = false"
              variant="outline"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Cancel
            </Button>

            <Button
              type="button"
              @click="deleteDelay(delayToDelete)"
              variant="destructive"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Delete Selected Delays Confirmation Dialog -->
      <Dialog v-model:open="showDeleteSelectedModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Confirm Bulk Deletion</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Are you sure you want to delete {{ selectedDelays.length }} delay records?
              This action cannot be undone.
            </DialogDescription>
          </DialogHeader>

          <DialogFooter class="px-4 sm:px-6">
            <Button
              type="button"
              @click="showDeleteSelectedModal = false"
              variant="outline"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Cancel
            </Button>
            <Button
              type="button"
              @click="deleteSelectedDelays()"
              variant="destructive"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Delete Selected
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>

    <!-- Import Modal -->
    <Dialog v-model:open="showImportModal">
      <DialogContent
        class="max-w-[95vw] sm:max-w-[90vw] md:max-w-5xl max-h-[90vh] overflow-hidden flex flex-col"
      >
        <DialogHeader class="px-4 sm:px-6 border-b pb-3">
          <div class="flex items-center gap-2">
            <Icon name="upload" class="h-5 w-5 text-primary" />
            <DialogTitle class="text-lg sm:text-xl font-semibold">
              Import Delays
            </DialogTitle>
          </div>
          <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">
            Upload a CSV file to import delays. The file will be validated before import.
          </DialogDescription>
        </DialogHeader>

        <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4">
          <!-- Step 1: File Upload -->
          <div v-if="!importValidationResults">
            <div class="space-y-4">
              <!-- ✅ Dropzone (drag & drop fixed) -->
              <div
                class="flex flex-col items-center justify-center border-2 border-dashed rounded-lg p-8 bg-muted/20 transition-colors"
                :class="{
                  'border-primary bg-primary/5': isDragging,
                  'opacity-60 pointer-events-none': isValidating,
                }"
                @dragenter.prevent="onDragEnter"
                @dragover.prevent="onDragOver"
                @dragleave.prevent="onDragLeave"
                @drop.prevent="onDrop"
              >
                <Icon
                  name="file-spreadsheet"
                  class="h-12 w-12 text-muted-foreground mb-3"
                />

                <div class="text-center">
                  <div class="text-sm font-medium">
                    <span class="text-primary">Drag & drop</span> your CSV here
                  </div>
                  <p class="text-xs text-muted-foreground mt-1">or</p>
                </div>

                <label class="cursor-pointer mt-3">
                  <span class="text-sm font-medium text-primary hover:underline">
                    Choose CSV file
                  </span>
                  <input
                    ref="importFileInput"
                    type="file"
                    class="hidden"
                    @change="onImportInputChange"
                    accept=".csv,text/csv"
                    :disabled="isValidating"
                  />
                </label>

                <p class="text-xs text-muted-foreground mt-2">CSV only</p>

                <div v-if="isDragging" class="mt-3 text-xs text-primary font-medium">
                  Drop file to validate
                </div>
              </div>

              <!-- Template Download -->
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
import DelayForm from "@/components/DelayForm.vue";
import Icon from "@/components/Icon.vue";
import OnTimeDashboard from "@/components/ontime/OnTimeDashboard.vue";
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
} from "@/components/ui";
import Button from "@/components/ui/button/Button.vue";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
  delays: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenantSlug: { type: String, default: null },
  delay_codes: { type: Array, default: () => [] },
  tenants: { type: Array, default: () => [] },
  isSuperAdmin: { type: Boolean, default: false },
  dateRange: { type: Object, default: null },
  dateFilter: { type: String, default: "yesterday" },
  weekNumber: { type: Number, default: null },
  startWeekNumber: { type: Number, default: null },
  endWeekNumber: { type: Number, default: null },
  year: { type: Number, default: null },
  delay_breakdown: { type: Object, default: null },
  line_chart_data: { type: Array, default: () => [] },
  average_ontime: { type: Number, default: null },
  filters: {
    type: Object,
    default: () => ({
      search: "",
      delayCode: "",
      delayCategory: "",
      delayType: "",
      disputed: "",
      controllable: "",
    }),
  },
  permissions: Array,
});

const page = usePage();

/** ✅ Prevent browser from opening file if dropped outside dropzone */
onMounted(() => {
  const prevent = (e) => e.preventDefault();
  window.addEventListener("dragover", prevent);
  window.addEventListener("drop", prevent);

  onUnmounted(() => {
    window.removeEventListener("dragover", prevent);
    window.removeEventListener("drop", prevent);
  });
});

/** Drag state for import dropzone */
const importFileInput = ref(null);
const isDragging = ref(false);
let dragDepth = 0;

function onDragEnter() {
  dragDepth += 1;
  isDragging.value = true;
}
function onDragOver() {
  isDragging.value = true;
}
function onDragLeave() {
  dragDepth -= 1;
  if (dragDepth <= 0) {
    dragDepth = 0;
    isDragging.value = false;
  }
}
function onDrop(e) {
  dragDepth = 0;
  isDragging.value = false;

  const file = e.dataTransfer?.files?.[0];
  if (!file) return;

  handleImportFile(file);
}

function onImportInputChange(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  handleImportFile(file);

  // reset so choosing same file again triggers change
  e.target.value = "";
}

/** Breadcrumbs */
const breadcrumbs = [
  {
    title: props.tenantSlug ? "Dashboard" : "Admin Dashboard",
    href: props.tenantSlug
      ? route("dashboard", { tenantSlug: props.tenantSlug })
      : route("admin.dashboard"),
  },
  {
    title: "On-Time",
    href: props.tenantSlug
      ? route("ontime.index", { tenantSlug: props.tenantSlug })
      : route("ontime.index.admin"),
  },
];

/** UI state */
const formModal = ref(false);
const codeModal = ref(false);
const selectedDelay = ref(null);
const successMessage = ref("");
const errorMessage = ref("");
const showDeleteModal = ref(false);
const delayToDelete = ref(null);
const selectedDelays = ref([]);
const showDeleteSelectedModal = ref(false);
const exportForm = ref(null);

const showImportModal = ref(false);
const importValidationResults = ref(null);
const isValidating = ref(false);
const isImporting = ref(false);

const showFilters = ref(false);

/** Code management state */
const showCodeForm = ref(false);
const editingCode = ref(null);
const codeForm = ref({ code: "", description: "" });
const codeDeleteConfirm = ref(false);
const codeToDelete = ref(null);

/** Sorting state */
const sortColumn = ref("date");
const sortDirection = ref("desc");

/** Filters */
const filters = ref({ ...props.filters });

/** Pagination & Date filter state */
const perPage = ref(props.delays?.per_page || 10);
const activeTab = ref(props.dateFilter || "full");

/** Week number text */
const weekNumberText = computed(() => {
  if (
    (activeTab.value === "yesterday" || activeTab.value === "current-week") &&
    props.weekNumber &&
    props.year
  ) {
    return `Week ${props.weekNumber}, ${props.year}`;
  }

  if (
    (activeTab.value === "6w" || activeTab.value === "quarterly") &&
    props.startWeekNumber &&
    props.endWeekNumber &&
    props.year
  ) {
    return `Weeks ${props.startWeekNumber}-${props.endWeekNumber}, ${props.year}`;
  }

  return "";
});

/** Table columns */
const tableColumns = [
  "date",
  "delay_type",
  "driver_name",
  "delay_category",
  "delay_code",
  "disputed",
  "driver_controllable",
];

/** Permissions */
const permissionNames = computed(() => props.permissions.map((p) => p.name));

/** Filter “chips” state */
const hasActiveFilters = computed(() => {
  return (
    filters.value.search ||
    filters.value.delayCode ||
    filters.value.delayCategory ||
    filters.value.disputed ||
    filters.value.delayType ||
    filters.value.controllable
  );
});

const getDelayCodeLabel = (codeId) => {
  if (!codeId) return "";
  const idNum = typeof codeId === "string" ? parseInt(codeId, 10) : codeId;
  const code = props.delay_codes.find((c) => c.id === idNum);
  return code ? code.code : "";
};

/** Data: filtered + sorted delays */
const filteredDelays = computed(() => {
  let result = [...(props.delays?.data || [])];

  result.sort((a, b) => {
    let aVal, bVal;

    if (sortColumn.value === "delay_code") {
      aVal = a.delay_code?.code || "";
      bVal = b.delay_code?.code || "";
    } else {
      aVal = a[sortColumn.value];
      bVal = b[sortColumn.value];
    }

    if (aVal === null) return 1;
    if (bVal === null) return -1;

    if (typeof aVal === "string") {
      aVal = aVal.toLowerCase();
      bVal = bVal.toLowerCase();
    }

    if (aVal < bVal) return sortDirection.value === "asc" ? -1 : 1;
    if (aVal > bVal) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });

  return result;
});

/** Active delay codes for the form */
const activeDelayCodes = computed(() => {
  return (props.delay_codes || []).filter((c) => !c.deleted_at);
});

/** Sorting handler */
function sortBy(column) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortColumn.value = column;
    sortDirection.value = "asc";
  }
}

/** Date format */
function formatDate(dateStr) {
  if (!dateStr) return "";
  // If backend returns "YYYY-MM-DD", format to M/D/YYYY
  const parts = dateStr.split("-");
  if (parts.length !== 3) return dateStr;
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}

/** Delay category label */
function formatDelayCategory(category) {
  if (!category) return "—";
  switch (category) {
    case "1_120":
      return "1-120 mins";
    case "121_600":
      return "121-600 mins";
    case "601_plus":
      return "601+ mins";
    case "1_60":
      return "1-60 mins";
    case "61_240":
      return "61-240 mins";
    case "241_600":
      return "241-600 mins";
    default:
      return category;
  }
}

/** Filters handlers */
function applyFilters() {
  const routeName = props.tenantSlug
    ? route("ontime.index", { tenantSlug: props.tenantSlug })
    : route("ontime.index.admin");

  router.get(routeName, {
    ...filters.value,
    perPage: perPage.value,
    dateFilter: activeTab.value,
  });
}

function resetFilters() {
  filters.value = {
    search: "",
    delayCode: "",
    delayCategory: "",
    delayType: "",
    disputed: "",
    controllable: "",
  };
  applyFilters();
}

/** Pagination + date filter */
function changePerPage() {
  const routeName = props.tenantSlug
    ? route("ontime.index", { tenantSlug: props.tenantSlug })
    : route("ontime.index.admin");

  router.get(routeName, {
    ...filters.value,
    perPage: perPage.value,
    dateFilter: activeTab.value,
  });
}

function selectDateFilter(filter) {
  activeTab.value = filter;

  const routeName = props.tenantSlug
    ? route("ontime.index", { tenantSlug: props.tenantSlug })
    : route("ontime.index.admin");

  router.get(routeName, {
    ...filters.value,
    perPage: perPage.value,
    dateFilter: filter,
  });
}

function visitPage(url) {
  if (!url) return;

  const urlObj = new URL(url);
  const baseUrl = urlObj.origin + urlObj.pathname;

  router.get(baseUrl, {
    ...filters.value,
    perPage: perPage.value,
    dateFilter: activeTab.value,
    page: urlObj.searchParams.get("page") || 1,
  });
}

/** Form modal */
const openForm = (delay = null) => {
  selectedDelay.value = delay;
  formModal.value = true;
};

/** Delete delay */
const confirmDelete = (id) => {
  delayToDelete.value = id;
  showDeleteModal.value = true;
};

const deleteDelay = (id) => {
  const f = useForm({});
  const routeName = props.isSuperAdmin ? "ontime.destroy.admin" : "ontime.destroy";
  const routeParams = props.isSuperAdmin
    ? { delay: id }
    : { tenantSlug: props.tenantSlug, delay: id };

  f.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = "Delay record deleted successfully.";
      showDeleteModal.value = false;
      delayToDelete.value = null;
    },
    onError: () => {
      errorMessage.value = "Failed to delete delay record.";
    },
  });
};

/** Bulk selection */
const isAllSelected = computed(() => {
  return (
    filteredDelays.value.length > 0 &&
    selectedDelays.value.length === filteredDelays.value.length
  );
});

function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedDelays.value = filteredDelays.value.map((d) => d.id);
  } else {
    selectedDelays.value = [];
  }
}

function confirmDeleteSelected() {
  if (selectedDelays.value.length > 0) showDeleteSelectedModal.value = true;
}

function deleteSelectedDelays() {
  const f = useForm({ ids: selectedDelays.value });

  const routeName = props.isSuperAdmin
    ? "ontime.destroyBulk.admin"
    : "ontime.destroyBulk";
  const routeParams = props.isSuperAdmin ? {} : { tenantSlug: props.tenantSlug };

  f.delete(route(routeName, routeParams), {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = `${selectedDelays.value.length} delay records deleted successfully.`;
      selectedDelays.value = [];
      showDeleteSelectedModal.value = false;
      router.reload();
    },
    onError: () => {
      errorMessage.value = "Failed to delete selected delays.";
    },
  });
}

/** Export */
function exportCSV() {
  if ((props.delays?.data || []).length === 0) {
    errorMessage.value = "No delay data found to export.";
    return;
  }
  exportForm.value?.submit();
}

const exportUrl = computed(() => {
  return props.tenantSlug
    ? route("ontime.export", { tenantSlug: props.tenantSlug })
    : route("ontime.export.admin");
});

/** Import template */
const templateUrl = computed(() => {
  return "/storage/upload-data-temps/Delays Template.csv";
});

/** ✅ Import: shared file handler (drop + input) */
function handleImportFile(file) {
  if (!file) return;

  const isCsv =
    file.type === "text/csv" ||
    file.name.toLowerCase().endsWith(".csv") ||
    file.type === "";

  if (!isCsv) {
    errorMessage.value = "Please upload a valid CSV file.";
    setTimeout(() => (errorMessage.value = ""), 4000);
    return;
  }

  isValidating.value = true;

  const formData = new FormData();
  formData.append("file", file);

  const endpoint = props.isSuperAdmin
    ? route("ontime.validateImport.admin")
    : route("ontime.validateImport", { tenantSlug: props.tenantSlug });

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
}

function confirmImport() {
  if (!importValidationResults.value) return;
  if (importValidationResults.value.header_error) return;
  if ((importValidationResults.value.summary?.invalid ?? 0) > 0) return;

  isImporting.value = true;

  const endpoint = props.isSuperAdmin
    ? route("ontime.confirmImport.admin")
    : route("ontime.confirmImport", { tenantSlug: props.tenantSlug });

  router.post(
    endpoint,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        successMessage.value = `Successfully imported ${
          importValidationResults.value.summary?.valid ?? 0
        } delays`;
        closeImportModal();
      },
      onError: () => {
        errorMessage.value = "Failed to import delays";
      },
      onFinish: () => {
        isImporting.value = false;
      },
    }
  );
}

function downloadErrorReport() {
  const endpoint = props.isSuperAdmin
    ? route("ontime.downloadErrorReport.admin")
    : route("ontime.downloadErrorReport", { tenantSlug: props.tenantSlug });

  window.location.href = endpoint;
}

function closeImportModal() {
  showImportModal.value = false;
  importValidationResults.value = null;
  isValidating.value = false;
  isImporting.value = false;

  isDragging.value = false;
  dragDepth = 0;

  if (importFileInput.value) importFileInput.value.value = "";
}

/** Listen for server validation payload */
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

/** Code management functions */
const openCodeModal = () => {
  codeModal.value = true;
  showCodeForm.value = false;
  editingCode.value = null;
};

const openNewCodeForm = () => {
  codeForm.value = { code: "", description: "" };
  editingCode.value = null;
  showCodeForm.value = true;
};

const editCode = (code) => {
  codeForm.value = { code: code.code, description: code.description || "" };
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

const deleteCode = (id) => {
  if (!id) return;

  router.delete(route("delay_codes.destroy.admin", id), {
    onSuccess: () => {
      codeDeleteConfirm.value = false;
      codeToDelete.value = null;
      successMessage.value = "Delay code deleted successfully";
      router.reload({ only: ["delay_codes"] });
    },
    onError: () => {
      errorMessage.value = "Failed to delete delay code.";
    },
  });
};

const saveCode = () => {
  const f = useForm({
    code: codeForm.value.code,
    description: codeForm.value.description,
  });

  const routeName = editingCode.value
    ? props.isSuperAdmin
      ? "delay_codes.update.admin"
      : "delay_codes.update"
    : props.isSuperAdmin
    ? "delay_codes.store.admin"
    : "delay_codes.store";

  const routeParams = editingCode.value
    ? props.isSuperAdmin
      ? { id: editingCode.value }
      : { tenantSlug: props.tenantSlug, id: editingCode.value }
    : props.isSuperAdmin
    ? {}
    : { tenantSlug: props.tenantSlug };

  const method = editingCode.value ? f.put : f.post;

  method.call(f, route(routeName, routeParams), {
    onSuccess: () => {
      successMessage.value = editingCode.value
        ? "Delay code updated successfully."
        : "Delay code created successfully.";
      showCodeForm.value = false;
      editingCode.value = null;
      router.reload({ only: ["delay_codes"] });
    },
    onError: () => {
      errorMessage.value = "Failed to save delay code.";
    },
  });
};

const restoreCode = (id) => {
  const f = useForm({});
  f.post(
    route(props.isSuperAdmin ? "delay_codes.restore.admin" : "delay_codes.restore", {
      id,
    }),
    {
      onSuccess: () => {
        successMessage.value = "Delay code restored successfully.";
        router.reload({ only: ["delay_codes"] });
      },
      onError: () => {
        errorMessage.value = "Failed to restore delay code.";
      },
    }
  );
};

const forceDeleteCode = (id) => {
  const f = useForm({});
  f.delete(
    route(
      props.isSuperAdmin ? "delay_codes.forceDelete.admin" : "delay_codes.forceDelete",
      { id }
    ),
    {
      onSuccess: () => {
        successMessage.value = "Delay code permanently deleted successfully.";
        router.reload({ only: ["delay_codes"] });
      },
      onError: () => {
        errorMessage.value = "Failed to permanently delete delay code.";
      },
    }
  );
};

/** On-Time dashboard data */
const ontimeMetrics = computed(() => {
  if (!props.delay_breakdown?.by_category) return null;

  const categoryData = props.delay_breakdown.by_category;
  const type = props.filters?.delayType;

  if (type) {
    return {
      moreThan601Count: categoryData[`category_601_plus_${type}_count`] || "0",
      between1_60Count: categoryData[`category_1_60_${type}_count`] || "0",
      between61_240Count: categoryData[`category_61_240_${type}_count`] || "0",
      between241_600Count: categoryData[`category_241_600_${type}_count`] || "0",
      totalDelays: categoryData[`total_${type}_delays`] || "0",
      by_category: true,
    };
  }

  return {
    between1_120Count: categoryData.category_1_120_count || "0",
    between121_600Count: categoryData.category_121_600_count || "0",
    moreThan601Count: categoryData.category_601_plus_count || "0",
    between1_60Count: categoryData.category_1_60_count || "0",
    between61_240Count: categoryData.category_61_240_count || "0",
    between241_600Count: categoryData.category_241_600_count || "0",
    totalDelays: categoryData.total_delays || "0",
    by_category: true,
  };
});

const bottomDrivers = computed(() => {
  if (props.filters?.delayType === "origin")
    return props.delay_breakdown?.bottom_five_drivers?.origin || [];
  if (props.filters?.delayType === "destination")
    return props.delay_breakdown?.bottom_five_drivers?.destination || [];
  return props.delay_breakdown?.bottom_five_drivers?.total || [];
});

const ontimeChartData = computed(() => {
  if (!props.line_chart_data || props.line_chart_data.length === 0) {
    return {
      labels: [],
      datasets: [
        {
          label: "On-Time Performance",
          data: [],
          borderColor: "#3b82f6",
          backgroundColor: "rgba(59, 130, 246, 0.1)",
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
        label: "On-Time Performance",
        data: props.line_chart_data.map((item) => item.onTimePerformance),
        borderColor: "#3b82f6",
        backgroundColor: "rgba(59, 130, 246, 0.1)",
        tension: 0.3,
        fill: false,
      },
    ],
  };
});

/** Auto-hide messages */
watch(successMessage, (v) => {
  if (v) setTimeout(() => (successMessage.value = ""), 5000);
});
watch(errorMessage, (v) => {
  if (v) setTimeout(() => (errorMessage.value = ""), 5000);
});
</script>
