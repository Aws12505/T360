<template>
    <AppLayout :breadcrumbs="breadcrumbs" :tenantSlug="tenantSlug">
      <Head title="Summary"/>
      <div class="container mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-bold">Performance Summaries</h1>
          <Badge variant="outline" class="text-sm">
            <Icon name="calendar" class="mr-1 h-4 w-4" />
            Last updated: {{ formatDate(new Date()) }}
          </Badge>
        </div>
        
        <!-- Display summaries as a responsive grid of cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
          <Card v-for="(summary, range) in summaries" :key="range" class="overflow-hidden">
            <CardHeader>
              <CardTitle>{{ formatRange(range) }}</CardTitle>
              <div class="text-sm text-muted-foreground">Performance & Safety Summary</div>
            </CardHeader>
            
            <CardContent>
              <!-- Performance Data Section -->
              <div class="space-y-4">
                <div v-for="(value, key) in summary.performance.data" :key="key" class="space-y-1">
                  <div class="flex justify-between text-sm">
                    <span class="capitalize text-muted-foreground">{{ formatKey(key) }}</span>
                    <span class="font-medium">{{ formatValue(key, value) }}</span>
                  </div>
                  <div>
                    <Badge :variant="getBadgeVariant(summary.performance.ratings[normalizeKey(key)])">
                      {{ summary.performance.ratings[normalizeKey(key)] || 'N/A' }}
                    </Badge>
                  </div>
                </div>
              </div>
              
              <!-- Safety Data Section -->
              <div class="my-4 h-px bg-border"></div>
              <h3 class="text-sm font-semibold mb-3">Safety Summary</h3>
              
              <!-- Basic Safety Metrics -->
              <div class="space-y-2 mb-4">
                <div v-for="(value, key) in getBasicSafetyMetrics(summary.safety)" :key="'safety-' + key" 
                  class="flex justify-between text-sm">
                  <span class="capitalize text-muted-foreground">{{ formatKey(key) }}</span>
                  <span class="font-medium">{{ formatValue(key, value) }}</span>
                </div>
              </div>
              
              <!-- Safety Violation Ratings -->
              <div v-if="summary.safety.ratings" class="space-y-3 mt-4">
                <h4 class="text-xs font-medium text-muted-foreground">Violation Ratings</h4>
                <div class="grid grid-cols-2 gap-2">
                  <div v-for="(rating, violation) in summary.safety.ratings" :key="violation" 
                    class="border rounded p-2">
                    <div class="text-xs text-muted-foreground">{{ formatKey(violation) }}</div>
                    <div class="flex items-center justify-between mt-1">
                      <Badge :variant="getSafetyBadgeVariant(rating)">
                        {{ rating }}
                      </Badge>
                      <span class="text-xs font-medium">
                        {{ formatRateValue(summary.safety.rates, violation) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Rejection, Delay & Maintenance Breakdown Links -->
              <div v-if="rejectionBreakdowns?.[range] || delayBreakdowns?.[range] || maintenanceBreakdowns?.[range]" class="mt-4 flex flex-wrap gap-2">
                <Button v-if="rejectionBreakdowns?.[range]" variant="outline" size="sm" @click="showRejectionDetails(range)">
                  <Icon name="x-circle" class="mr-1 h-3 w-3" />
                  Rejections
                </Button>
                <Button v-if="delayBreakdowns?.[range]" variant="outline" size="sm" @click="showDelayDetails(range)">
                  <Icon name="clock" class="mr-1 h-3 w-3" />
                  Delays
                </Button>
                <Button v-if="maintenanceBreakdowns?.[range]" variant="outline" size="sm" @click="showMaintenanceDetails(range)">
                  <Icon name="wrench" class="mr-1 h-3 w-3" />
                  Maintenance
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
      
      <!-- Dialogs for detailed breakdowns -->
      <Dialog v-model:open="showRejectionDialog">
        <DialogContent class="max-w-md max-h-[80vh]">
          <DialogHeader>
            <DialogTitle>{{ selectedRange ? formatRange(selectedRange) : '' }} Rejection Breakdown</DialogTitle>
            <DialogDescription>
              Detailed breakdown of rejections by driver
            </DialogDescription>
          </DialogHeader>
          <div class="overflow-y-auto h-[50vh] pr-1">
            <div v-if="selectedRange && rejectionBreakdowns?.[selectedRange]" class="space-y-4 p-1">
              <!-- Rejection breakdown content -->
              <div>
                <h3 class="text-sm font-semibold mb-2">By Driver</h3>
                <div v-for="driver in rejectionBreakdowns[selectedRange].by_driver" :key="driver.driver_name" 
                  class="text-sm mb-3 p-3 border rounded-md hover:bg-muted/50 transition-colors">
                  <div class="font-medium flex items-center">
                    <Icon name="user" class="mr-2 h-4 w-4 text-muted-foreground" />
                    {{ driver.driver_name }}
                  </div>
                  <div class="grid grid-cols-3 text-xs text-muted-foreground mt-2 gap-2">
                    <Badge variant="outline" class="flex items-center justify-center">
                      Total: {{ driver.total_rejections }}
                    </Badge>
                    <Badge variant="outline" class="flex items-center justify-center">
                      Block: {{ driver.total_block_rejections }}
                    </Badge>
                    <Badge variant="outline" class="flex items-center justify-center">
                      Load: {{ driver.total_load_rejections }}
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <DialogFooter>
            <Button @click="showRejectionDialog = false" variant="outline">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      
      <Dialog v-model:open="showDelayDialog">
        <DialogContent class="max-w-md max-h-[80vh]">
          <DialogHeader>
            <DialogTitle>{{ selectedRange ? formatRange(selectedRange) : '' }} Delay Breakdown</DialogTitle>
            <DialogDescription>
              Detailed breakdown of delays by driver
            </DialogDescription>
          </DialogHeader>
          <div class="overflow-y-auto h-[50vh] pr-1">
            <div v-if="selectedRange && delayBreakdowns?.[selectedRange]" class="space-y-4 p-1">
              <!-- Delay breakdown content -->
              <div>
                <h3 class="text-sm font-semibold mb-2">By Driver</h3>
                <div v-for="driver in delayBreakdowns[selectedRange].by_driver" :key="driver.driver_name" 
                  class="text-sm mb-3 p-3 border rounded-md hover:bg-muted/50 transition-colors">
                  <div class="font-medium flex items-center">
                    <Icon name="user" class="mr-2 h-4 w-4 text-muted-foreground" />
                    {{ driver.driver_name }}
                  </div>
                  <div class="grid grid-cols-3 text-xs text-muted-foreground mt-2 gap-2">
                    <Badge variant="outline" class="flex items-center justify-center">
                      Total: {{ driver.total_delays }}
                    </Badge>
                    <Badge variant="outline" class="flex items-center justify-center">
                      Origin: {{ driver.total_origin_delays }}
                    </Badge>
                    <Badge variant="outline" class="flex items-center justify-center">
                      Dest: {{ driver.total_destination_delays }}
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <DialogFooter>
            <Button @click="showDelayDialog = false" variant="outline">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      
      <!-- New Maintenance Breakdown Dialog -->
      <Dialog v-model:open="showMaintenanceDialog">
        <DialogContent class="max-w-md max-h-[80vh]">
          <DialogHeader>
            <DialogTitle>{{ selectedRange ? formatRange(selectedRange) : '' }} Maintenance Breakdown</DialogTitle>
            <DialogDescription>
              Detailed breakdown of maintenance metrics
            </DialogDescription>
          </DialogHeader>
          <div class="overflow-y-auto h-[50vh] pr-1">
            <div v-if="selectedRange && maintenanceBreakdowns?.[selectedRange]" class="space-y-4 p-1">
              <!-- Maintenance breakdown content -->
              <div class="space-y-4">
                <div class="p-3 border rounded-md">
                  <h3 class="text-sm font-semibold mb-3">Repair Orders</h3>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="text-sm">
                      <div class="text-xs text-muted-foreground">Total Repair Orders</div>
                      <div class="font-medium mt-1">{{ maintenanceBreakdowns[selectedRange].total_repair_orders }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="p-3 border rounded-md">
                  <h3 class="text-sm font-semibold mb-3">Financial Metrics</h3>
                  <div class="grid grid-cols-1 gap-3">
                    <div class="text-sm">
                      <div class="text-xs text-muted-foreground">Total Invoice Amount</div>
                      <div class="font-medium mt-1">${{ formatCurrency(maintenanceBreakdowns[selectedRange].total_invoice_amount) }}</div>
                    </div>
                    <div class="text-sm">
                      <div class="text-xs text-muted-foreground">QS Invoice Amount</div>
                      <div class="font-medium mt-1">${{ formatCurrency(maintenanceBreakdowns[selectedRange].qs_invoice_amount) }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="p-3 border rounded-md">
                  <h3 class="text-sm font-semibold mb-3">Cost Per Mile (CPM) Metrics</h3>
                  <div class="grid grid-cols-1 gap-3">
                    <div class="text-sm">
                      <div class="text-xs text-muted-foreground">Total Miles</div>
                      <div class="font-medium mt-1">{{ formatNumber(maintenanceBreakdowns[selectedRange].total_miles) }}</div>
                    </div>
                    <div class="text-sm">
                      <div class="text-xs text-muted-foreground">Overall CPM</div>
                      <div class="font-medium mt-1">${{ formatCurrency(maintenanceBreakdowns[selectedRange].cpm) }}</div>
                    </div>
                    <div class="text-sm">
                      <div class="text-xs text-muted-foreground">QS CPM</div>
                      <div class="font-medium mt-1">${{ formatCurrency(maintenanceBreakdowns[selectedRange].qs_cpm) }}</div>
                    </div>
                    <div class="text-sm">
                      <div class="text-xs text-muted-foreground">QS MVtS</div>
                      <div class="font-medium mt-1">${{ formatCurrency(maintenanceBreakdowns[selectedRange].qs_MVtS) }}</div>
                    </div>
                  </div>
                </div>
                
                <!-- Areas of Concern Section -->
                <div v-if="maintenanceBreakdowns[selectedRange].areas_of_concern && maintenanceBreakdowns[selectedRange].areas_of_concern.length > 0" 
                  class="p-3 border rounded-md">
                  <h3 class="text-sm font-semibold mb-3">Areas of Concern</h3>
                  <div class="space-y-3">
                    <div v-for="area in maintenanceBreakdowns[selectedRange].areas_of_concern" :key="area.id" 
                      class="flex justify-between items-center text-sm">
                      <div class="text-xs text-muted-foreground capitalize">{{ area.concern }}</div>
                      <Badge variant="outline">{{ area.count }}</Badge>
                    </div>
                    <div v-if="maintenanceBreakdowns[selectedRange].areas_of_concern.length === 0" 
                      class="text-xs text-muted-foreground text-center py-2">
                      No areas of concern recorded for this period
                    </div>
                  </div>
                </div>
                
                <!-- Work Orders by Truck Section -->
                <div v-if="maintenanceBreakdowns[selectedRange].work_orders_by_truck && maintenanceBreakdowns[selectedRange].work_orders_by_truck.length > 0" 
                  class="p-3 border rounded-md">
                  <h3 class="text-sm font-semibold mb-3">Work Orders by Truck</h3>
                  <div class="space-y-3">
                    <div v-for="truck in maintenanceBreakdowns[selectedRange].work_orders_by_truck" :key="truck.truckid" 
                      class="flex justify-between items-center text-sm">
                      <div class="text-xs text-muted-foreground">Truck #{{ truck.truckid }}</div>
                      <Badge variant="outline">{{ truck.work_order_count }}</Badge>
                    </div>
                    <div v-if="maintenanceBreakdowns[selectedRange].work_orders_by_truck.length === 0" 
                      class="text-xs text-muted-foreground text-center py-2">
                      No work orders recorded for this period
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <DialogFooter>
            <Button @click="showMaintenanceDialog = false" variant="outline">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </AppLayout>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue'
  import { Head } from '@inertiajs/vue3'
  import AppLayout from '@/layouts/AppLayout.vue'
  import Icon from '@/components/Icon.vue'
  import { 
    Card, CardHeader, CardTitle, CardContent,
    Badge, Button,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
  } from '@/components/ui'
  
  /**
   * Props:
   * - summaries: Object containing summaries with separate performance and safety data.
   * - rejectionBreakdowns: Object with breakdown data for rejections.
   * - delayBreakdowns: Object with breakdown data for delays.
   * - maintenanceBreakdowns: Object with breakdown data for maintenance.
   * - tenantSlug: Optional tenant identifier.
   * - SuperAdmin: Boolean flag indicating SuperAdmin status.
   * - tenants: Array of tenant objects.
   */
  const props = defineProps({
    summaries: Object,
    rejectionBreakdowns: Object,
    delayBreakdowns: Object,
    maintenanceBreakdowns: Object,
    tenantSlug: String,
    SuperAdmin: Boolean,
    tenants: Array,
  })
  
  // State for dialogs
  const showRejectionDialog = ref(false)
  const showDelayDialog = ref(false)
  const showMaintenanceDialog = ref(false)
  const selectedRange = ref(null)
  
  // Show rejection details dialog
  const showRejectionDetails = (range) => {
    selectedRange.value = range
    showRejectionDialog.value = true
  }
  
  // Show delay details dialog
  const showDelayDetails = (range) => {
    selectedRange.value = range
    showDelayDialog.value = true
  }
  
  // Show maintenance details dialog
  const showMaintenanceDetails = (range) => {
    selectedRange.value = range
    showMaintenanceDialog.value = true
  }
  
  // Breadcrumbs for navigation
  const breadcrumbs = [
    {
      title: props.tenantSlug ? 'Dashboard' : 'Admin Dashboard',
      href: props.tenantSlug
        ? route('dashboard', { tenantSlug: props.tenantSlug })
        : route('admin.dashboard'),
    },
  ]
  
  // Helper: Format currency values
  const formatCurrency = (value) => {
    if (value === undefined || value === null) return '0.00'
    return Number(value).toFixed(2)
  }
  
  // Helper: Format number with commas
  const formatNumber = (value) => {
    if (value === undefined || value === null) return '0'
    return Number(value).toLocaleString()
  }
  
  // Helper: Format rate values with better handling for zero/undefined values
  const formatRateValue = (rates, violation) => {
    if (!rates) return '0.00'
    
    const mappedKey = mapViolationToRate(violation)
    const value = rates[mappedKey]
    
    if (value === undefined || value === null) return '0.00'
    if (value === 0) return '0.00'
    
    return value.toFixed(2)
  }
  
  // Helper function to format summary range keys into human-friendly labels.
  const formatRange = (key) => {
    const labels = {
      yesterday: 'Yesterday',
      current_week: 'WtD',
      rolling_6_weeks: 'Rolling 6 Weeks',
      quarterly: 'Quarterly',
    }
    return labels[key] ?? key
  }
  
  // Helper: Format date for display
  const formatDate = (date) => {
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date)
  }
  
  // Helper: Replace underscores with spaces.
  const formatKey = (key) => key.replace(/_/g, ' ')
  
  // Helper: Normalize key names for rating lookup.
  const normalizeKey = (key) => {
    if (key === 'sum_vcr_preventable') return 'vcr_preventable'
    if (key === 'sum_open_boc') return 'open_boc'
    return key
  }
  
  // Helper: Format values and add "%" for specific keys if needed.
  const formatValue = (key, val) => {
    if (key === 'meets_safety_bonus_criteria') {
      return val == 1 ? 'Yes' : 'No'
    }
    const num = Number(val)
    if (!isNaN(num)) {
      const formatted = num.toFixed(2)
      return key === 'average_maintenance_variance_to_spend'
        ? `${formatted}%`
        : formatted
    }
    return val
  }
  
  // Helper: Get badge variant based on performance rating
  const getBadgeVariant = (rating) => {
    switch (rating) {
      case 'fantastic_plus': return 'success'
      case 'fantastic': return 'success'
      case 'good': return 'secondary'
      case 'fair': return 'warning'
      case 'poor': return 'destructive'
      default: return 'outline'
    }
  }
  
  // Helper: Get badge variant based on safety rating
  const getSafetyBadgeVariant = (rating) => {
    switch (rating) {
      case 'gold': return 'success'
      case 'silver': return 'secondary'
      case 'not_eligible': return 'destructive'
      default: return 'outline'
    }
  }
  
  // Helper: Filter basic safety metrics (excluding rates and ratings)
  const getBasicSafetyMetrics = (safetyData) => {
    if (!safetyData) return {}
    
    const basicMetrics = {}
    for (const [key, value] of Object.entries(safetyData)) {
      if (key !== 'rates' && key !== 'ratings') {
        basicMetrics[key] = value
      }
    }
    return basicMetrics
  }
  
  // Helper: Map violation name in ratings to corresponding rate key
  const mapViolationToRate = (violation) => {
    // Handle any naming differences between ratings and rates objects
    return violation
  }
  </script>
  