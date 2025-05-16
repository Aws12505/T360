<template>
    <div class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto p-2 md:p-4 lg:p-6 space-y-6">
      <!-- Alerts -->
      <Alert v-if="successMessage" variant="success">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>
      <Alert v-if="errorMessage" variant="destructive">
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>{{ errorMessage }}</AlertDescription>
      </Alert>
  
      <!-- Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-center px-2 mb-4 space-y-2 sm:space-y-0">
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold">Repair Orders</h1>
        <div class="flex flex-wrap gap-3">
          <Button @click="openCreateModal" variant="default"><Icon name="plus" /> Create New</Button>
          <Button v-if="selectedIds.length" @click="confirmBulkDelete" variant="destructive">
            <Icon name="trash" /> Delete Selected ({{ selectedIds.length }})
          </Button>
          <Button v-if="isAdmin" @click="openAreasModal" variant="outline"><Icon name="settings" /> Areas</Button>
          <Button v-if="isAdmin" @click="openVendorsModal" variant="outline"><Icon name="settings" /> Vendors</Button>
          <Button v-if="isAdmin" @click="openStatusModal" variant="outline"><Icon name="settings" /> Statuses</Button>
          <div class="relative">
            <Button @click="showUpload = !showUpload" variant="secondary"><Icon name="upload" /> Upload CSV</Button>
            <div v-if="showUpload" class="absolute right-0 mt-1 w-48 rounded-md border bg-background shadow">
              <label class="block cursor-pointer px-4 py-2 text-sm hover:bg-muted">
                <span>Upload CSV</span><input type="file" class="hidden" @change="importCsv" accept=".csv"/>
              </label>
              <a :href="templateUrl" download class="block px-4 py-2 text-sm hover:bg-muted">Download Template</a>
            </div>
          </div>
          <Button @click.prevent="exportCsv" variant="outline"><Icon name="download" /> Download CSV</Button>
        </div>
      </div>
  
      <!-- Date Filter Tabs -->
      <Card>
        <CardContent>
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
          <div v-if="dateRange" class="mt-2 text-sm text-muted-foreground">
            <span v-if="dateRange.start&&dateRange.end">
              Showing {{ formatDate(dateRange.start) }} to {{ formatDate(dateRange.end) }}
            </span>
          </div>
        </CardContent>
      </Card>
  
      <!-- Filters Card -->
      <Card>
        <CardHeader class="flex justify-between items-center">
          <CardTitle>Filters</CardTitle>
          <Button size="sm" variant="ghost" @click="showFilters = !showFilters">
            {{ showFilters?'Hide':'Show' }} Filters <Icon :name="showFilters?'chevron-up':'chevron-down'" />
          </Button>
        </CardHeader>
        <CardContent v-if="showFilters">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div><Label>Search</Label><Input v-model="filter.search" @input="applyFilters" placeholder="RO#, Invoice..."/></div>
            <div><Label>Vendor</Label>
              <select v-model="filter.vendor_id" @change="applyFilters" class="input">
                <option value="">All Vendors</option>
                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.vendor_name }}</option>
              </select>
            </div>
            <div><Label>Status</Label>
              <select v-model="filter.status_id" @change="applyFilters" class="input">
                <option value="">All Statuses</option>
                <option v-for="s in woStatuses" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
          </div>
          <div class="mt-4 flex justify-end gap-2">
            <Button variant="ghost" size="sm" @click="resetFilters">Reset</Button>
            <Button variant="default" size="sm" @click="applyFilters">Apply</Button>
          </div>
        </CardContent>
      </Card>
  
      <!-- Table -->
      <Card v-if="hasData">
        <CardContent class="p-0 overflow-x-auto">
          <Table class="min-w-full">
            <TableHeader>
              <TableRow>
                <TableHead class="w-12"><input type="checkbox" :checked="allSelected" @change="toggleAll"/></TableHead>
                <TableHead @click="sort('ro_number')" class="cursor-pointer whitespace-nowrap">RO#<SortIndicator column="ro_number" :sortState="sortState"/></TableHead>
                <TableHead v-if="isAdmin" class="whitespace-nowrap">Company</TableHead>
                <TableHead @click="sort('ro_open_date')" class="cursor-pointer whitespace-nowrap">Open Date<SortIndicator column="ro_open_date" :sortState="sortState"/></TableHead>
                <TableHead @click="sort('ro_close_date')" class="cursor-pointer whitespace-nowrap">Close Date<SortIndicator column="ro_close_date" :sortState="sortState"/></TableHead>
                <TableHead>Vendor</TableHead>
                <TableHead>Invoice</TableHead>
                <TableHead>Amount</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="!repairOrders.data.length">
                <TableCell :colspan="isAdmin?9:8" class="text-center py-8">No repair orders found.</TableCell>
              </TableRow>
              <TableRow v-for="o in repairOrders.data" :key="o.id" class="hover:bg-muted/50">
                <TableCell><input type="checkbox" :value="o.id" v-model="selectedIds"/></TableCell>
                <TableCell class="whitespace-nowrap">{{ o.ro_number }}</TableCell>
                <TableCell v-if="isAdmin" class="whitespace-nowrap">{{ o.tenant?.name||'—' }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ formatDate(o.ro_open_date) }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ o.ro_close_date?formatDate(o.ro_close_date):'N/A' }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ o.vendor?.vendor_name||'—' }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ o.invoice||'—' }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ formatCurrency(o.invoice_amount) }}</TableCell>
                <TableCell>
                  <Button size="sm" variant="warning" @click="openEdit(o)"><Icon name="pencil"/></Button>
                  <Button size="sm" variant="destructive" @click="deleteOne(o.id)"><Icon name="trash"/></Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
        <!-- Pagination -->
        <div class="border-t bg-muted/20 px-4 py-3 flex justify-between items-center">
          <div>Showing {{ repairOrders.data.length }} of {{ repairOrders.meta.total }} entries</div>
          <div class="flex items-center gap-2">
            <Label>Per page</Label>
            <select v-model.number="localPerPage" @change="changePerPage" class="input w-20">
              <option v-for="n in [10,25,50,100]" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div class="flex space-x-1">
            <Button
              v-for="link in repairOrders.links"
              :key="link.label"
              @click="go(link.url)"
              :disabled="!link.url"
              variant="ghost"
              :class="{'active-page': link.active}"><span v-html="link.label"></span></Button>
          </div>
        </div>
      </Card>
      <div v-else class="text-center py-16 text-muted-foreground">
        <Icon name="database-x" class="h-16 w-16 mx-auto mb-4" />
        <h2>There is No Data to give Information about.</h2>
      </div>
  
      <!-- Create/Edit Modal -->
      <Dialog v-model:open="showModal">
        <DialogContent class="max-w-[95vw] sm:max-w-[90vw] md:max-w-4xl">
          <DialogHeader>
            <DialogTitle>{{ formAction }} Repair Order</DialogTitle>
            <DialogDescription>Fill in details to {{ formAction.toLowerCase() }} a repair order.</DialogDescription>
          </DialogHeader>
          <form @submit.prevent="submitForm" class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:gap-6">
            <div v-if="isAdmin" class="col-span-2">
              <Label>Company</Label>
              <select v-model="form.tenant_id" required class="input">
                <option disabled value="">Select</option>
                <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
            </div>
            <div><Label>RO#</Label><Input v-model="form.ro_number" required/></div>
            <div><Label>Open Date</Label><Input type="date" v-model="form.ro_open_date" required/></div>
            <div><Label>Close Date</Label><Input type="date" v-model="form.ro_close_date"/></div>
            <div><Label>Truck</Label>
              <select v-model="form.truck_id" required class="input">
                <option disabled value="">Select</option>
                <option v-for="t in trucks" :key="t.id" :value="t.id">{{ t.truckid }}</option>
              </select>
            </div>
            <div class="col-span-2">
              <Label>Areas of Concern</Label>
              <div class="flex flex-wrap gap-1 mb-2">
                <span v-for="id in form.area_of_concerns" :key="id" class="badge">
                  {{ areasMap[id] }}<button @click="removeArea(id)">×</button>
                </span>
              </div>
              <select @change="addArea($event)" class="input">
                <option value="">Select</option>
                <option v-for="a in availableAreas" :key="a.id" :value="a.id">{{ a.concern }}</option>
              </select>
            </div>
            <div class="col-span-2"><Label>Repairs Made</Label><textarea v-model="form.repairs_made" class="input"></textarea></div>
            <div><Label>Vendor</Label>
              <select v-model="form.vendor_id" required class="input">
                <option disabled value="">Select</option>
                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.vendor_name }}</option>
              </select>
            </div>
            <div><Label>WO#</Label><Input v-model="form.wo_number"/></div>
            <div><Label>WO Status</Label>
              <select v-model="form.wo_status_id" required class="input">
                <option disabled value="">Select</option>
                <option v-for="s in woStatuses" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div><Label>Invoice</Label><Input v-model="form.invoice"/></div>
            <div><Label>Amount</Label><Input type="number" step="0.01" v-model="form.invoice_amount"/></div>
            <div><Label>On QS</Label>
              <select v-model="form.on_qs" required class="input">
                <option value="yes">Yes</option>
                <option value="no">No</option>
                <option value="not expected">Not Expected</option>
              </select>
            </div>
            <div><Label>Invoice Received</Label><input type="checkbox" v-model="form.invoice_received"/></div>
            <div><Label>QS Invoice Date</Label><Input type="date" v-model="form.qs_invoice_date"/></div>
            <div v-if="formAction==='Update'"><Label>Disputed?</Label><input type="checkbox" v-model="form.disputed"/></div>
            <div v-if="formAction==='Update'"><Label>Dispute Outcome</Label><textarea v-model="form.dispute_outcome" class="input"></textarea></div>
            <DialogFooter class="col-span-2 flex justify-end gap-2">
              <Button variant="outline" @click="closeModal">Cancel</Button>
              <Button type="submit">{{ formAction }}</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
  
      <!-- Delete One -->
      <Dialog v-model:open="showDeleteModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>This cannot be undone.</DialogDescription>
          </DialogHeader>
          <DialogFooter class="flex gap-2">
            <Button variant="outline" @click="showDeleteModal=false">Cancel</Button>
            <Button variant="destructive" @click="confirmDelete">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
  
      <!-- Bulk Delete -->
      <Dialog v-model:open="showBulkDeleteModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete {{ selectedIds.length }} Orders?</DialogTitle>
          </DialogHeader>
          <DialogFooter class="flex gap-2">
            <Button variant="outline" @click="showBulkDeleteModal=false">Cancel</Button>
            <Button variant="destructive" @click="deleteBulk">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
  
      <!-- Areas Modal -->
      <Dialog v-model:open="showAreasModal">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Manage Areas</DialogTitle>
          </DialogHeader>
          <form @submit.prevent="submitArea" class="space-y-4">
            <Input v-model="areaForm.concern" placeholder="New Concern" required/>
            <Button type="submit">Add</Button>
          </form>
          <Table class="mt-4">
            <TableHeader><TableRow><TableHead>Concern</TableHead><TableHead>Actions</TableHead></TableRow></TableHeader>
            <TableBody>
              <TableRow v-for="a in areasOfConcern" :key="a.id">
                <TableCell>{{ a.concern }}</TableCell>
                <TableCell>
                  <Button size="sm" @click="deleteArea(a.id)"><Icon name="trash"/></Button>
                  <Button v-if="a.deleted_at" size="sm" @click="restoreArea(a.id)"><Icon name="undo"/></Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <DialogFooter><Button @click="showAreasModal=false">Close</Button></DialogFooter>
        </DialogContent>
      </Dialog>
  
      <!-- Vendors Modal -->
      <Dialog v-model:open="showVendorsModal">
        <DialogContent>
          <DialogHeader><DialogTitle>Manage Vendors</DialogTitle></DialogHeader>
          <form @submit.prevent="submitVendor" class="space-y-4">
            <Input v-model="vendorForm.vendor_name" placeholder="Name" required/>
            <Button type="submit">Add</Button>
          </form>
          <Table class="mt-4">
            <TableHeader><TableRow><TableHead>Name</TableHead><TableHead>Actions</TableHead></TableRow></TableHeader>
            <TableBody>
              <TableRow v-for="v in vendors" :key="v.id">
                <TableCell>{{ v.vendor_name }}</TableCell>
                <TableCell>
                  <Button size="sm" @click="deleteVendor(v.id)"><Icon name="trash"/></Button>
                  <Button v-if="v.deleted_at" size="sm" @click="restoreVendor(v.id)"><Icon name="undo"/></Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <DialogFooter><Button @click="showVendorsModal=false">Close</Button></DialogFooter>
        </DialogContent>
      </Dialog>
  
      <!-- Status Modal -->
      <Dialog v-model:open="showStatusModal">
        <DialogContent>
          <DialogHeader><DialogTitle>Manage Statuses</DialogTitle></DialogHeader>
          <form @submit.prevent="submitStatus" class="space-y-4">
            <Input v-model="statusForm.name" placeholder="Status" required/>
            <Button type="submit">Add</Button>
          </form>
          <Table class="mt-4">
            <TableHeader><TableRow><TableHead>Name</TableHead><TableHead>Actions</TableHead></TableRow></TableHeader>
            <TableBody>
              <TableRow v-for="s in woStatuses" :key="s.id">
                <TableCell>{{ s.name }}</TableCell>
                <TableCell>
                  <Button size="sm" @click="deleteStatus(s.id)"><Icon name="trash"/></Button>
                  <Button v-if="s.deleted_at" size="sm" @click="restoreStatus(s.id)"><Icon name="undo"/></Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <DialogFooter><Button @click="showStatusModal=false">Close</Button></DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref, computed, onMounted, onUnmounted } from 'vue'
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
    repairOrders: Object, tenantSlug: String, SuperAdmin: Boolean,
    tenants: Array, trucks: Array, vendors: Array, areasOfConcern: Array,
    woStatuses: Array, dateRange: Object, filters: Object, dateFilter: String,
    perPage: Number
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
  function formatDate(d:string){ if(!d)return ''; return new Date(d).toLocaleDateString() }
  function formatCurrency(a:any){ return a? new Intl.NumberFormat('en-US',{style:'currency',currency:'USD'}).format(a): '$0.00' }
  const templateUrl = '/storage/upload-data-temps/Repair Orders Template.csv'
  
  // Actions
  function selectDate(val:string){ filter.value.dateFilter=val; applyFilters() }
  function applyFilters(){ const routeName = props.tenantSlug? route('repair_orders.index',{tenantSlug:props.tenantSlug}): route('repair_orders.index.admin'); router.get(routeName,{...filter.value, perPage:localPerPage.value},{preserveState:true}) }
  function resetFilters(){ filter.value={...filter.value, search:'',vendor_id:'',status_id:''}; applyFilters() }
  const sortState = ref({column:'ro_number',direction:'asc'})
  function sort(col:string){ if(sortState.value.column===col) sortState.value.direction = sortState.value.direction==='asc'?'desc':'asc'; else { sortState.value = {column:col,direction:'asc'} }; applyFilters() }
  function changePerPage(){ emit('update:perPage', localPerPage.value); applyFilters() }
  function go(url?:string){ if(url) router.get(new URL(url).pathname,{}, {preserveState:true}) }
  function toggleAll(e:Event){ const chk=(e.target as HTMLInputElement).checked; selectedIds.value = chk? props.repairOrders.data.map(o=>o.id):[] }
  
  // CSV
  function importCsv(evt:any){ const file=evt.target.files[0]; if(!file)return; const f=new FormData(); f.append('file',file); const r= props.tenantSlug? route('repair_orders.import',{tenantSlug:props.tenantSlug}): route('repair_orders.import.admin'); router.post(r,f,{onSuccess:()=>{ successMessage.value='Imported'; evt.target.value=null }}) }
  function exportCsv(){ if(!hasData.value){ errorMessage.value='No data'; return } window.location.href = props.tenantSlug? route('repair_orders.export',{tenantSlug:props.tenantSlug}): route('repair_orders.export.admin') }
  
  // Create/Edit handlers
  function openCreateModal(){ form.reset(); formAction.value='Create'; showModal.value=true }
  function openEdit(o:any){ form.reset(); form.fill(o); formAction.value='Update'; showModal.value=true }
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
  
  // Map areas for display
  const areasMap = computed(()=> Object.fromEntries(props.areasOfConcern.map(a=>[a.id,a.concern])))
  const availableAreas = computed(()=> props.areasOfConcern.filter(a=>!form.area_of_concerns.includes(a.id)))
  function addArea(e:any){ const id=Number(e.target.value); if(id&&!form.area_of_concerns.includes(id)) form.area_of_concerns.push(id); e.target.value='' }
  function removeArea(id:number){ form.area_of_concerns = form.area_of_concerns.filter(x=>x!==id) }
  
  // Click outside
  onMounted(()=>{ const h=(e:Event)=>{ if(showUpload.value&&! (e.target as HTMLElement).closest('.relative')) showUpload.value=false }; document.addEventListener('click',h); onUnmounted(()=>document.removeEventListener('click',h)) })
  
  // Clear flash
  setTimeout(()=> successMessage.value='',5000)
  </script>
  
  <style scoped>
  .input { @apply rounded-md border px-3 py-1 text-sm w-full }
  .badge { @apply inline-flex items-center bg-primary/10 text-primary rounded px-2 py-0.5 text-xs }
  .active-page { @apply border-primary bg-primary/10 text-primary }
  </style>