<template>
  <AppLayout
    :breadcrumbs="breadcrumbs"
    :tenantSlug="tenantSlug"
    :permissions="props.permissions"
  >
    <Head title="Performance" />

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
        class="w-full md:max-w-xl lg:max-w-2xl xl:max-w-6xl m-auto pt-2 space-y-2 md:space-y-4 lg:space-y-6"
      >
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
          <h1
            class="text-lg md:text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-200"
          >
            Performance Management
          </h1>

          <div class="flex flex-wrap gap-3">
            <Button
              class="px-2 py-0 md:px-4 md:py-2"
              @click="openCreateModal"
              variant="default"
              v-if="permissionNames.includes('performance.create')"
            >
              <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2" />
              Create New Performance
            </Button>

            <Button
              class="px-2 py-0 md:px-4 md:py-2"
              v-if="
                selectedPerformances.length > 0 &&
                permissionNames.includes('performance.delete')
              "
              @click="confirmDeleteSelected()"
              variant="destructive"
            >
              <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2" />
              Delete Selected ({{ selectedPerformances.length }})
            </Button>

            <Button
              @click="showImportModal = true"
              v-if="permissionNames.includes('performance.import')"
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
              v-if="permissionNames.includes('performance.export')"
            >
              <Icon name="download" class="mr-1 h-4 w-4 md:mr-2" />
              Download CSV
            </Button>
          </div>
        </div>
      </div>

      <!-- Date Filter Tabs -->
      <Card>
        <CardContent class="p-2 md:p-4 lg:p-6">
          <div class="flex flex-col items-center md:items-start gap-2">
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

      <!-- Performance Table -->
      <Card class="mx-auto max-w-[95vw] md:max-w-[64vw] lg:max-w-full overflow-x-auto">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table class="relative h-[600px] overflow-auto">
              <TableHeader>
                <TableRow
                  class="sticky top-0 z-10 border-b bg-background hover:bg-background"
                >
                  <TableHead
                    class="w-[50px]"
                    v-if="permissionNames.includes('performance.delete')"
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
                      permissionNames.includes('performance.update') ||
                      permissionNames.includes('performance.delete')
                    "
                    >Actions</TableHead
                  >
                </TableRow>
              </TableHeader>

              <TableBody>
                <TableRow v-if="filteredPerformances.length === 0">
                  <TableCell
                    :colspan="
                      SuperAdmin ? tableColumns.length + 3 : tableColumns.length + 2
                    "
                    class="py-8 text-center"
                  >
                    No performance records found matching your criteria
                  </TableCell>
                </TableRow>

                <TableRow
                  v-for="item in filteredPerformances"
                  :key="item.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell
                    class="text-center"
                    v-if="permissionNames.includes('performance.delete')"
                  >
                    <input
                      type="checkbox"
                      :value="item.id"
                      v-model="selectedPerformances"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                  </TableCell>

                  <TableCell v-if="SuperAdmin" class="min-w-[120px]">
                    {{ item.tenant?.name ?? "—" }}
                  </TableCell>

                  <TableCell class="min-w-[100px]">{{ formatDate(item.date) }}</TableCell>

                  <TableCell class="min-w-[120px]">
                    <div>{{ formatDecimal(item.acceptance) }}%</div>
                    <div class="text-xs italic text-gray-500 whitespace-normal">
                      ({{ formatRating(item.acceptance_rating) }})
                    </div>
                  </TableCell>

                  <TableCell class="min-w-[120px]"
                    >{{ formatDecimal(item.on_time_to_origin) }}%</TableCell
                  >
                  <TableCell class="min-w-[120px]"
                    >{{ formatDecimal(item.on_time_to_destination) }}%</TableCell
                  >

                  <TableCell class="min-w-[120px]">
                    <div>{{ formatDecimal(item.on_time) }}%</div>
                    <div class="text-xs italic text-gray-500 whitespace-normal">
                      ({{ formatRating(item.on_time_rating) }})
                    </div>
                  </TableCell>

                  <TableCell class="min-w-[140px]">
                    <div>{{ formatDecimal(item.maintenance_variance_to_spend) }}%</div>
                    <div class="text-xs italic text-gray-500 whitespace-normal">
                      ({{ formatRating(item.maintenance_variance_to_spend_rating) }})
                    </div>
                  </TableCell>

                  <TableCell class="min-w-[120px]">
                    <div>{{ item.open_boc }}</div>
                    <div class="text-xs italic text-gray-500 whitespace-normal">
                      ({{ formatRating(item.open_boc_rating) }})
                    </div>
                  </TableCell>

                  <TableCell class="min-w-[140px]">
                    <div>{{ item.meets_safety_bonus_criteria ? "Yes" : "No" }}</div>
                    <div class="text-xs italic text-gray-500 whitespace-normal">
                      ({{ formatRating(item.meets_safety_bonus_criteria_rating) }})
                    </div>
                  </TableCell>

                  <TableCell class="min-w-[120px]">
                    <div>{{ item.vcr_preventable }}</div>
                    <div class="text-xs italic text-gray-500 whitespace-normal">
                      ({{ formatRating(item.vcr_preventable_rating) }})
                    </div>
                  </TableCell>

                  <TableCell class="min-w-[120px]">
                    <div>{{ item.vmcr_p }}</div>
                    <div class="text-xs italic text-gray-500 whitespace-normal">
                      ({{ formatRating(item.vmcr_p_rating) }})
                    </div>
                  </TableCell>

                  <TableCell
                    class="min-w-[120px]"
                    v-if="
                      permissionNames.includes('performance.update') ||
                      permissionNames.includes('performance.delete')
                    "
                  >
                    <div class="flex space-x-2">
                      <Button
                        @click="openEditModal(item)"
                        variant="warning"
                        size="sm"
                        v-if="permissionNames.includes('performance.update')"
                      >
                        <Icon name="pencil" class="mr-1 h-4 w-4" />
                        Edit
                      </Button>

                      <Button
                        @click="deletePerformance(item.id)"
                        variant="destructive"
                        size="sm"
                        v-if="permissionNames.includes('performance.delete')"
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
          <div class="border-t bg-muted/20 px-4 py-3" v-if="performances.links">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
              <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <span
                  >Showing {{ filteredPerformances.length }} of
                  {{ performances.data.length }} entries</span
                >

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
        <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">{{ formTitle }}</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Fill in the details to {{ formAction.toLowerCase() }} a performance record.
            </DialogDescription>
          </DialogHeader>

          <form
            @submit.prevent="submitForm"
            class="grid max-h-[75vh] grid-cols-1 gap-2 overflow-y-auto p-4 sm:grid-cols-2 sm:gap-3 sm:p-6"
          >
            <!-- Tenant Dropdown for SuperAdmin -->
            <div v-if="SuperAdmin" class="col-span-2">
              <Label for="tenant" class="text-xs sm:text-sm">Company Name</Label>
              <div class="relative mt-1">
                <select
                  id="tenant"
                  v-model="form.tenant_id"
                  required
                  class="flex h-9 w-full appearance-none items-center rounded-md border border-input bg-background px-3 py-1 text-xs ring-offset-background focus:outline-none focus:ring-1 focus:ring-ring focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50 sm:h-10 sm:text-sm"
                >
                  <option disabled value="">Select a Company</option>
                  <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                    {{ tenant.name }}
                  </option>
                </select>
                <div
                  class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-gray-700"
                >
                  <svg
                    class="h-3 w-3 opacity-50 sm:h-4 sm:w-4"
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

            <!-- Date Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="date" class="text-xs sm:text-sm">Date</Label>
              <Input
                id="date"
                v-model="form.date"
                type="date"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- Acceptance Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="acceptance" class="text-xs sm:text-sm">Acceptance</Label>
              <Input
                id="acceptance"
                v-model="form.acceptance"
                type="number"
                step="0.01"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- On Time to Origin Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="on_time_to_origin" class="text-xs sm:text-sm"
                >On Time to Origin</Label
              >
              <Input
                id="on_time_to_origin"
                v-model="form.on_time_to_origin"
                type="number"
                step="0.01"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- On Time to Destination Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="on_time_to_destination" class="text-xs sm:text-sm"
                >On Time to Destination</Label
              >
              <Input
                id="on_time_to_destination"
                v-model="form.on_time_to_destination"
                type="number"
                step="0.01"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- Maintenance Variance to Spend Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="maintenance_variance_to_spend" class="text-xs sm:text-sm"
                >Maintenance Variance to Spend</Label
              >
              <Input
                id="maintenance_variance_to_spend"
                v-model="form.maintenance_variance_to_spend"
                type="number"
                step="0.001"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- Open BOC Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="open_boc" class="text-xs sm:text-sm">Open BOC</Label>
              <Input
                id="open_boc"
                v-model="form.open_boc"
                type="number"
                step="1"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- VCR Preventable Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="vcr_preventable" class="text-xs sm:text-sm"
                >VCR Preventable</Label
              >
              <Input
                id="vcr_preventable"
                v-model="form.vcr_preventable"
                type="number"
                step="1"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- VMCR P Field -->
            <div class="col-span-2 sm:col-span-1">
              <Label for="vmcr_p" class="text-xs sm:text-sm">VMCR P</Label>
              <Input
                id="vmcr_p"
                v-model="form.vmcr_p"
                type="number"
                step="1"
                required
                class="h-9 px-3 py-1 text-xs sm:h-10 sm:text-sm"
              />
            </div>

            <!-- Safety Bonus Checkbox -->
            <div class="col-span-2 flex items-center gap-2">
              <input
                id="meets_safety_bonus_criteria"
                v-model="form.meets_safety_bonus_criteria"
                type="checkbox"
                class="h-3.5 w-3.5 rounded border-gray-300 text-primary focus:ring-1 focus:ring-primary sm:h-4 sm:w-4"
              />
              <Label for="meets_safety_bonus_criteria" class="text-xs sm:text-sm">
                Meets Safety Bonus Criteria
              </Label>
            </div>

            <DialogFooter
              class="col-span-2 mt-3 flex flex-col gap-2 sm:flex-row sm:gap-3"
            >
              <Button
                type="button"
                @click="closeModal"
                variant="outline"
                class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                variant="default"
                class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
              >
                {{ formAction }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <!-- Delete Confirmation Dialog -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Confirm Deletion</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Are you sure you want to delete this performance record? This action cannot
              be undone.
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
              @click="confirmDelete"
              variant="destructive"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Delete Selected Confirmation Dialog -->
      <Dialog v-model:open="showDeleteSelectedModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md">
          <DialogHeader class="px-4 sm:px-6">
            <DialogTitle class="text-lg sm:text-xl">Confirm Bulk Deletion</DialogTitle>
            <DialogDescription class="text-xs sm:text-sm">
              Are you sure you want to delete
              {{ selectedPerformances.length }} performance records? This action cannot be
              undone.
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
              @click="deleteSelectedPerformances()"
              variant="destructive"
              class="h-9 px-4 py-1 text-xs sm:h-10 sm:text-sm"
            >
              Delete Selected
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Import Validation Modal -->
      <Dialog v-model:open="showImportModal">
        <DialogContent
          class="max-w-[95vw] sm:max-w-[90vw] md:max-w-5xl max-h-[90vh] overflow-hidden flex flex-col"
        >
          <DialogHeader class="px-4 sm:px-6 border-b pb-3">
            <div class="flex items-center gap-2">
              <Icon name="upload" class="h-5 w-5 text-primary" />
              <DialogTitle class="text-lg sm:text-xl font-semibold">
                Import Performances
              </DialogTitle>
            </div>
            <DialogDescription class="text-xs sm:text-sm mt-1 text-muted-foreground">
              Upload a CSV file to import performances. The file will be validated before
              import.
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

                <!-- ✅ Template Download -->
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

              <!-- Header Error -->
              <Alert v-if="importValidationResults.header_error" variant="destructive">
                <AlertTitle class="flex items-center gap-2">
                  <Icon name="alert_circle" class="h-5 w-5" />
                  Header Error
                </AlertTitle>
                <AlertDescription>
                  {{ importValidationResults.header_error }}
                </AlertDescription>
              </Alert>

              <!-- Invalid Rows Details -->
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
                                <span class="font-medium text-foreground">
                                  {{ p.label }}:
                                </span>
                                {{ p.value }}
                              </span>

                              <span
                                v-if="!row.preview?.length"
                                class="italic text-muted-foreground"
                              >
                                —
                              </span>
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
                            >
                              —
                            </span>
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

      <!-- Hidden Export Form -->
      <form ref="exportForm" method="GET" class="hidden"></form>
    </div>
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
  performances: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  tenantSlug: { type: String, default: null },
  SuperAdmin: { type: Boolean, default: false },
  tenants: { type: Array, default: () => [] },
  dateFilter: { type: String, default: "yesterday" },
  dateRange: { type: Object, default: () => ({ label: "All Time" }) },
  perPage: { type: Number, default: 10 },
  weekNumber: { type: Number, default: null },
  startWeekNumber: { type: Number, default: null },
  endWeekNumber: { type: Number, default: null },
  year: { type: Number, default: null },
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

/** Drag state */
const importFileInput = ref(null);
const isDragging = ref(false);
let dragDepth = 0;

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

/** UI state */
const errorMessage = ref("");
const successMessage = ref("");
const showModal = ref(false);
const formTitle = ref("Create Performance");
const formAction = ref("Create");
const activeTab = ref(props.dateFilter || "full");
const perPage = ref(props.perPage || 10);
const selectedPerformances = ref([]);
const showDeleteSelectedModal = ref(false);
const showDeleteModal = ref(false);
const performanceToDelete = ref(null);
const exportForm = ref(null);

const showImportModal = ref(false);
const importValidationResults = ref(null);
const isValidating = ref(false);
const isImporting = ref(false);

/** Breadcrumbs */
const breadcrumbs = [
  {
    title: props.tenantSlug ? "Dashboard" : "Admin Dashboard",
    href: props.tenantSlug
      ? route("dashboard", { tenantSlug: props.tenantSlug })
      : route("admin.dashboard"),
  },
  {
    title: "Performance",
    href: props.tenantSlug
      ? route("performance.index", { tenantSlug: props.tenantSlug })
      : route("performance.index.admin"),
  },
];

/** Pagination visitPage (kept with perPage/dateFilter preservation) */
function visitPage(url) {
  if (url) {
    const urlObj = new URL(url);
    urlObj.searchParams.set("perPage", perPage.value);
    urlObj.searchParams.set("dateFilter", activeTab.value);

    router.get(urlObj.href, {}, { only: ["performances"] });
  }
}

/** Sorting */
const sortColumn = ref("date");
const sortDirection = ref("desc");

/** Table columns */
const tableColumns = [
  "date",
  "acceptance",
  "on_time_to_origin",
  "on_time_to_destination",
  "on_time",
  "maintenance_variance_to_spend",
  "open_boc",
  "meets_safety_bonus_criteria",
  "vcr_preventable",
  "vmcr_p",
];

/** Form state */
const form = useForm({
  tenant_id: null,
  date: "",
  acceptance: "",
  on_time_to_origin: "",
  on_time_to_destination: "",
  maintenance_variance_to_spend: "",
  open_boc: "",
  meets_safety_bonus_criteria: false,
  vcr_preventable: "",
  vmcr_p: "",
  id: null,
});

const deleteForm = useForm({});

/** Filtered & sorted performances */
const filteredPerformances = computed(() => {
  let result = [...props.performances.data];

  result.sort((a, b) => {
    let valA = a[sortColumn.value];
    let valB = b[sortColumn.value];

    if (valA === null) return 1;
    if (valB === null) return -1;

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

function sortBy(column) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortColumn.value = column;
    sortDirection.value = "asc";
  }
}

/** Date filter */
function selectDateFilter(filter) {
  activeTab.value = filter;

  const routeName = props.tenantSlug
    ? route("performance.index", { tenantSlug: props.tenantSlug })
    : route("performance.index.admin");

  router.get(
    routeName,
    {
      dateFilter: filter,
      perPage: perPage.value,
    },
    { preserveState: true }
  );
}

function submitForm() {
  const isCreate = formAction.value === "Create";
  const routeName = isCreate
    ? props.SuperAdmin
      ? route("performance.store.admin")
      : route("performance.store", props.tenantSlug)
    : props.SuperAdmin
    ? route("performance.update.admin", [form.id])
    : route("performance.update", [props.tenantSlug, form.id]);

  const method = isCreate ? "post" : "put";

  form[method](routeName, {
    onSuccess: () => {
      successMessage.value = isCreate
        ? "Performance created successfully."
        : "Performance updated successfully.";
      closeModal();
    },
    onError: () => {
      alert("Something went wrong.");
    },
  });
}

function exportCSV() {
  if (filteredPerformances.value.length === 0) {
    errorMessage.value = "No data available to export";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
    return;
  }

  const routeName = props.SuperAdmin
    ? route("performance.export.admin")
    : route("performance.export", props.tenantSlug);

  exportForm.value?.setAttribute("action", routeName);
  exportForm.value?.submit();
}

/** Auto-hide success message */
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = "";
    }, 5000);
  }
});

/** Date formatting */
function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString + "T12:00:00");
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const year = date.getFullYear();
  return `${month}/${day}/${year}`;
}

/** Modals */
function openCreateModal() {
  formTitle.value = "Create Performance";
  formAction.value = "Create";
  form.reset();
  form.clearErrors();

  if (props.SuperAdmin && props.tenants.length > 0) {
    form.tenant_id = "";
  }

  showModal.value = true;
}

function openEditModal(item) {
  formTitle.value = "Edit Performance";
  formAction.value = "Update";

  form.reset();
  form.clearErrors();

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

/** Delete */
function deletePerformance(id) {
  performanceToDelete.value = id;
  showDeleteModal.value = true;
}

function confirmDelete() {
  if (!performanceToDelete.value) return;

  const routeName = props.SuperAdmin
    ? route("performance.destroy.admin", [performanceToDelete.value])
    : route("performance.destroy", [props.tenantSlug, performanceToDelete.value]);

  deleteForm.delete(routeName, {
    onSuccess: () => {
      successMessage.value = "Performance deleted successfully.";
      showDeleteModal.value = false;
      performanceToDelete.value = null;
    },
    onError: () => {
      alert("Failed to delete performance record.");
    },
  });
}

/** Select all */
const isAllSelected = computed(() => {
  return (
    filteredPerformances.value.length > 0 &&
    selectedPerformances.value.length === filteredPerformances.value.length
  );
});

function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedPerformances.value = filteredPerformances.value.map((p) => p.id);
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
  const bulkForm = useForm({ ids: selectedPerformances.value });

  const routeName = props.SuperAdmin
    ? "performance.destroyBulk.admin"
    : "performance.destroyBulk";
  const routeParams = props.SuperAdmin ? {} : { tenantSlug: props.tenantSlug };

  bulkForm.delete(route(routeName, routeParams), {
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

/** Ratings display */
const formatRating = (rating) => {
  if (!rating) return "Not Available";

  switch (rating) {
    case "gold":
      return "Gold Tier";
    case "silver":
      return "Silver Tier";
    case "not_eligible":
      return "Not Eligible";
    case "fantastic_plus":
      return "Fantastic +";
    case "fantastic":
      return "Fantastic";
    case "good":
      return "Good";
    case "fair":
      return "Fair";
    case "poor":
      return "Poor";
    default:
      return rating;
  }
};

/** Column display helpers */
const getDisplayColumnName = (column) => {
  const displayNames = {
    on_time_to_origin: "OTO",
    on_time_to_destination: "OTD",
    maintenance_variance_to_spend: "MVtS",
    open_boc: "Open BOC",
    vcr_preventable: "VCR-P",
    vmcr_p: "VMCR-P",
  };

  return (
    displayNames[column] ||
    column
      .replace(/_/g, " ")
      .split(" ")
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ")
  );
};

const getFullColumnName = (column) => {
  const fullNames = {
    on_time_to_origin: "On Time to Origin",
    on_time_to_destination: "On Time to Destination",
    maintenance_variance_to_spend: "Maintenance Variance to Spend",
    open_boc: "Open BOC",
    vcr_preventable: "VCR Preventable",
    vmcr_p: "VMCR P",
  };

  return (
    fullNames[column] ||
    column
      .replace(/_/g, " ")
      .split(" ")
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ")
  );
};

function changePerPage() {
  const routeName = props.tenantSlug
    ? route("performance.index", { tenantSlug: props.tenantSlug })
    : route("performance.index.admin");

  router.get(
    routeName,
    {
      dateFilter: activeTab.value,
      perPage: perPage.value,
    },
    { preserveState: true }
  );
}

const formatDecimal = (value) => {
  if (value === null || value === undefined) return "—";

  const num = typeof value === "string" ? parseFloat(value) : value;

  if (Number.isInteger(num) || num.toFixed(2).endsWith(".00")) return Math.floor(num);
  if (num.toFixed(2).endsWith("0")) return num.toFixed(1);
  return num.toFixed(2);
};

/** Permissions */
const permissionNames = computed(() => props.permissions.map((p) => p.name));

/** Template URL */
const templateUrl = computed(() => {
  return "/storage/upload-data-temps/Performances Template.csv";
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

  const endpoint = props.tenantSlug
    ? route("performance.validateImport", { tenantSlug: props.tenantSlug })
    : route("performance.validateImport.admin");

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

/** ✅ Import: input change */
function onImportInputChange(event) {
  const file = event.target.files?.[0];
  if (!file) return;

  handleImportFile(file);

  // reset so choosing same file again triggers change
  event.target.value = "";
}

/** ✅ Drag handlers */
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

function confirmImport() {
  if (!importValidationResults.value) return;
  if ((importValidationResults.value.summary?.invalid ?? 0) > 0) return;
  if (importValidationResults.value.header_error) return;

  isImporting.value = true;

  const endpoint = props.tenantSlug
    ? route("performance.confirmImport", { tenantSlug: props.tenantSlug })
    : route("performance.confirmImport.admin");

  router.post(
    endpoint,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        successMessage.value = `Successfully imported ${
          importValidationResults.value.summary?.valid ?? 0
        } performances`;
        closeImportModal();
      },
      onError: () => {
        errorMessage.value = "Failed to import performances";
      },
      onFinish: () => {
        isImporting.value = false;
      },
    }
  );
}

function downloadErrorReport() {
  const endpoint = props.tenantSlug
    ? route("performance.downloadErrorReport", { tenantSlug: props.tenantSlug })
    : route("performance.downloadErrorReport.admin");

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
</script>
