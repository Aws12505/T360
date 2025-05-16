<template>
    <div class="w-full md:max-w-2xl lg:max-w-3xl xl:max-w-6xl lg:mx-auto p-2 md:p-4 lg:p-6 space-y-6">
      <!-- Success / Error Alerts -->
      <Alert v-if="successMessage" variant="success">
        <AlertTitle>Success</AlertTitle>
        <AlertDescription>{{ successMessage }}</AlertDescription>
      </Alert>
      <Alert v-if="errorMessage" variant="destructive">
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>{{ errorMessage }}</AlertDescription>
      </Alert>
  
      <!-- Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-center px-2 mb-2 md:mb-4 lg:mb-6 space-y-2 sm:space-y-0">
        <h1 class="text-lg md:text-xl lg:text-2xl font-bold">Truck Management</h1>
        <div class="flex flex-wrap gap-3">
          <Button @click="openCreateModal" variant="default" class="px-2 py-0 md:px-4 md:py-2">
            <Icon name="plus" class="mr-1 h-4 w-4 md:mr-2"/> Create New Truck
          </Button>
          <Button v-if="selectedTrucks.length" @click="confirmDeleteSelected" variant="destructive" class="px-2 py-0 md:px-4 md:py-2">
            <Icon name="trash" class="mr-1 h-4 w-4 md:mr-2"/> Delete Selected ({{ selectedTrucks.length }})
          </Button>
          <div class="relative">
            <Button @click="showUploadOpts = !showUploadOpts" variant="secondary" class="px-2 py-0 md:px-4 md:py-2">
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
          <Button @click.prevent="exportCSV" variant="outline" class="px-2 py-0 md:px-4 md:py-2">
            <Icon name="download" class="mr-1 h-4 w-4 md:mr-2"/> Download CSV
          </Button>
        </div>
      </div>
  
      <!-- Filters -->
      <Card>
        <CardHeader class="p-2 md:p-4 lg:p-6">
          <CardTitle class="text-lg md:text-xl lg:text-2xl">Filters</CardTitle>
        </CardHeader>
        <CardContent class="p-2 md:p-4 lg:p-6">
          <div class="flex flex-col gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
              <div>
                <Label for="search">Search</Label>
                <Input id="search" type="text" v-model="filters.search" placeholder="Search by truck ID or VIN..." @input="resetPage" />
              </div>
              <div>
                <Label for="type">Type</Label>
                <select id="type" v-model="filters.type" @change="resetPage"
                  class="flex h-10 w-full items-center rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring"
                >
                  <option value="">All Types</option>
                  <option value="daycab">Daycab</option>
                  <option value="sleepercab">Sleepercab</option>
                </select>
              </div>
              <div>
                <Label for="make">Make</Label>
                <select id="make" v-model="filters.make" @change="resetPage"
                  class="flex h-10 w-full items-center rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring"
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
            <Button @click="clearFilters" variant="ghost" size="sm">
              <Icon name="rotate_ccw" class="mr-2 h-4 w-4" /> Reset
            </Button>
          </div>
        </CardContent>
      </Card>
  
      <!-- Data Table -->
      <Card class="overflow-x-auto">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table class="relative">
              <TableHeader>
                <TableRow class="sticky top-0 z-10 border-b bg-background">
                  <TableHead class="w-12">
                    <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" 
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                  </TableHead>
                  <TableHead v-if="SuperAdmin">Company</TableHead>
                  <TableHead v-for="col in tableColumns" :key="col" @click="sortBy(col)" class="cursor-pointer">
                    <div class="flex items-center space-x-1">
                      <span>{{ humanize(col) }}</span>
                      <Icon v-if="sortColumn===col" :name="sortDirection==='asc'?'chevron-up':'chevron-down'" class="h-4 w-4" />
                    </div>
                  </TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="!paginatedEntries.length">
                  <TableCell :colspan="SuperAdmin?tableColumns.length+3:tableColumns.length+2" class="py-8 text-center">
                    No trucks found.
                  </TableCell>
                </TableRow>
                <TableRow v-for="t in paginatedEntries" :key="t.id" class="hover:bg-muted/50">
                  <TableCell class="text-center">
                    <input type="checkbox" :value="t.id" v-model="selectedTrucks"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                  </TableCell>
                  <TableCell v-if="SuperAdmin">{{ t.tenant?.name||'—' }}</TableCell>
                  <TableCell v-for="col in tableColumns" :key="col" class="whitespace-nowrap">
                    {{ formatCell(t, col) }}
                  </TableCell>
                  <TableCell>
                    <div class="flex space-x-2">
                      <Button variant="warning" size="sm" @click="openEditModal(t)">
                        <Icon name="pencil" class="mr-1 h-4 w-4"/> Edit
                      </Button>
                      <Button variant="destructive" size="sm" @click="confirmDelete(t)">
                        <Icon name="trash" class="mr-1 h-4 w-4"/> Delete
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
  
          <!-- Pagination -->
          <div v-if="totalPages>1" class="border-t bg-muted/20 px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-4 text-sm text-muted-foreground">
              <span>Showing {{ paginatedEntries.length }} of {{ sortedEntries.length }} entries</span>
              <span>Per page:</span>
              <select v-model.number="localPerPage" @change="onPerPageChange"
                class="h-8 rounded-md border bg-background px-2 py-1 text-sm focus:ring-2 focus:ring-ring"
              >
                <option v-for="n in [10,25,50,100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
            <div class="flex space-x-1">
              <Button size="sm" variant="ghost" @click="goToPage(page-1)" :disabled="page===1">Prev</Button>
              <Button v-for="n in totalPages" :key="n" size="sm" variant="ghost" @click="goToPage(n)"
                :class="{'border-primary bg-primary/10 text-primary':n===page}">{{ n }}</Button>
              <Button size="sm" variant="ghost" @click="goToPage(page+1)" :disabled="page===totalPages">Next</Button>
            </div>
          </div>
        </CardContent>
      </Card>
  
      <!-- Create/Edit Modal -->
      <Dialog v-model:open="showModal">
        <DialogContent class="max-w-[95vw] sm:max-w-md w-full">
          <DialogHeader>
            <DialogTitle>{{ form.id?'Edit Truck':'Create Truck' }}</DialogTitle>
            <DialogDescription>Fill in the details to {{ form.id?'update':'create' }} a truck.</DialogDescription>
          </DialogHeader>
          <form @submit.prevent="submitForm" class="grid grid-cols-1 gap-4 p-4">
            <div v-if="SuperAdmin">
              <Label for="tenant">Company Name</Label>
              <select id="tenant" v-model="form.tenant_id" required
                class="flex h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring"
              >
                <option value="">Select Company</option>
                <option v-for="tn in tenants" :key="tn.id" :value="tn.id">{{ tn.name }}</option>
              </select>
            </div>
            <div>
              <Label for="truckid">Truck ID</Label>
              <Input id="truckid" v-model.number="form.truckid" type="number" required />
            </div>
            <div>
              <Label for="type">Type</Label>
              <select id="type" v-model="form.type" required class="h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring">
                <option value="daycab">Daycab</option>
                <option value="sleepercab">Sleepercab</option>
              </select>
            </div>
            <div>
              <Label for="make">Make</Label>
              <select id="make" v-model="form.make" required class="h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring">
                <option value="international">International</option>
                <option value="kenworth">Kenworth</option>
                <option value="peterbilt">Peterbilt</option>
                <option value="volvo">Volvo</option>
                <option value="freightliner">Freightliner</option>
              </select>
            </div>
            <div>
              <Label for="fuel">Fuel</Label>
              <select id="fuel" v-model="form.fuel" required class="h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring">
                <option value="diesel">Diesel</option>
                <option value="cng">CNG</option>
              </select>
            </div>
            <div>
              <Label for="license">License</Label>
              <Input id="license" v-model.number="form.license" type="number" min="0" required />
            </div>
            <div>
              <Label for="vin">VIN</Label>
              <Input id="vin" v-model="form.vin" type="text" required />
            </div>
            <div>
              <Label for="inspection_status">Annual Inspection Status</Label>
              <select id="inspection_status" v-model="form.inspection_status" required class="h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring">
                <option value="good">Good</option>
                <option value="expired">Expired</option>
              </select>
            </div>
            <div>
              <Label for="inspection_expiry_date">Annual Inspection Expiry Date</Label>
              <Input id="inspection_expiry_date" v-model="form.inspection_expiry_date" type="date" required />
            </div>
            <div>
              <Label for="status">Status</Label>
              <select id="status" v-model="form.status" required class="h-10 w-full rounded-md border bg-background px-3 text-sm focus:ring-2 focus:ring-ring">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="Returned to AMZ">Returned to AMZ</option>
              </select>
            </div>
            <DialogFooter class="col-span-2 flex justify-end space-x-2">
              <Button variant="outline" @click="closeModal" type="button">Cancel</Button>
              <Button type="submit">{{ form.id?'Update':'Create' }}</Button>
            </DialogFooter>
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
          <DialogFooter class="flex justify-end space-x-2">
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
          <DialogFooter class="flex justify-end space-x-2">
            <Button variant="outline" @click="showBulkDeleteModal=false">Cancel</Button>
            <Button variant="destructive" @click="deleteSelectedTrucks">Delete Selected</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
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
  const page = ref(1)
  const localPerPage = ref(props.perPage)
  const selectedTrucks = ref<number[]>([])
  const showUploadOpts = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')
  const showModal = ref(false)
  const showDeleteModal = ref(false)
  const showBulkDeleteModal = ref(false)
  const deleteTarget = ref<any>(null)
  
  // Form states
  const form = useForm({
    id:null, truckid:null, type:'daycab', make:'international', fuel:'diesel',
    license:null, vin:'', tenant_id:props.SuperAdmin?null:undefined,
    inspection_status:'good', inspection_expiry_date:new Date().toISOString().split('T')[0],
    status:'active'
  })
  const importForm = useForm({csv_file:null})
  const exportForm = ref<HTMLFormElement|null>(null)
  
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
  const totalPages = computed(() => Math.max(1, Math.ceil(sortedEntries.value.length / localPerPage.value)))
  const paginatedEntries = computed(() => {
    const start=(page.value-1)*localPerPage.value
    return sortedEntries.value.slice(start, start+localPerPage.value)
  })
  const isAllSelected = computed(() => paginatedEntries.value.length>0 && paginatedEntries.value.every(t=>selectedTrucks.value.includes(t.id)))
  
  // Watchers
  watch(successMessage, v=> v && setTimeout(()=>successMessage.value='',5000))
  watch(()=>props.perPage, v=>{ localPerPage.value=v; page.value=1 })
  
  // Methods
  function humanize(col:string){ return col.replace(/_/g,' ').replace(/\b\w/g,l=>l.toUpperCase()) }
  function formatDate(s:string){ if(!s) return '—'; const d=new Date(s+'T00:00:00'); return d.toLocaleDateString() }
  function formatCell(t:any,col:string){
    if(col==='status') return t.status
    if(col==='inspection_status') return t.inspection_status==='good'?'Good':'Expired'
    if(col==='inspection_expiry_date') return formatDate(t[col])
    const v=t[col]; return typeof v==='string'? v.charAt(0).toUpperCase()+v.slice(1): v
  }
  function resetPage(){ page.value=1 }
  function clearFilters(){ filters.value={search:'',type:'',make:''}; resetPage() }
  function sortBy(col:string){ if(sortColumn.value===col) sortDirection.value = sortDirection.value==='asc'?'desc':'asc'; else { sortColumn.value=col; sortDirection.value='asc' } }
  function onPerPageChange(){ page.value=1; emit('update:perPage', localPerPage.value) }
  function goToPage(n:number){ if(n<1||n>totalPages.value) return; page.value=n }
  function toggleSelectAll(e:Event){ const chk=(e.target as HTMLInputElement).checked; if(chk) paginatedEntries.value.forEach(t=>{ if(!selectedTrucks.value.includes(t.id)) selectedTrucks.value.push(t.id) }); else selectedTrucks.value = selectedTrucks.value.filter(id=>!paginatedEntries.value.some(t=>t.id===id)) }
  
  function openCreateModal(){ form.reset(); form.clearErrors(); form.id=null; showModal.value=true }
  function openEditModal(item:any){ form.reset(); form.clearErrors(); form.fill({
    id:item.id, truckid:item.truckid, type:item.type, make:item.make, fuel:item.fuel,
    license:item.license, vin:item.vin, tenant_id:item.tenant_id,
    inspection_status:item.inspection_status, inspection_expiry_date:item.inspection_expiry_date,
    status:item.status
  }); showModal.value=true }
  function closeModal(){ showModal.value=false }
  
  function submitForm(){
    const data = {...form.data(), truckid:Number(form.truckid), license:Number(form.license)}
    const action = form.id ? 'put' : 'post'
    const url = form.id ? route(props.SuperAdmin?'truck.update.admin':'truck.update',[ form.id ]) : route(props.SuperAdmin?'truck.store.admin':'truck.store',{ tenantSlug:props.tenantSlug })
    form[action](url,{ data, onSuccess:()=>{ successMessage.value= form.id?'Truck updated.':'Truck created.'; closeModal() }, onError:()=>errorMessage.value='Save failed' })
  }
  
  function confirmDelete(item:any){ deleteTarget.value=item; showDeleteModal.value=true }
  function deleteEntry(){ if(!deleteTarget.value) return; const url = route(props.SuperAdmin?'truck.destroy.admin':'truck.destroy',[ deleteTarget.value.id ])
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
  </script>
  