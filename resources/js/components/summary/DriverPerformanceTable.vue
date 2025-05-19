<template>
  <Card class="shadow-sm border bg-card">
    <CardHeader class="p-4 border-b">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
          <CardTitle class="text-lg font-semibold flex items-center gap-2">
            <Icon name="users" class="h-5 w-5 text-primary" />
            Driver Performance
          </CardTitle>
          <CardDescription>Overall driver performance metrics</CardDescription>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative w-full md:w-auto">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <Icon name="search" class="h-4 w-4 text-muted-foreground" />
            </div>
            <Input 
              v-model="searchQuery" 
              placeholder="Search drivers..." 
              class="pl-10 w-full md:w-[200px]"
            />
          </div>
          <Button 
            variant="outline" 
            size="sm" 
            @click="resetFilters"
            class="hidden md:flex items-center gap-1"
          >
            <Icon name="rotate-ccw" class="h-4 w-4" />
            Reset
          </Button>
        </div>
      </div>
    </CardHeader>
    <CardContent class="p-0">
      <div class="overflow-x-auto">
        <Table class="relative h-[500px] overflow-auto">
          <TableHeader>
            <TableRow class="sticky top-0 z-10 border-b bg-background hover:bg-background">
              <TableHead class="w-12 text-center">
                <div class="flex items-center justify-center">
                  #
                </div>
              </TableHead>
              <TableHead @click="sortBy('driver_name')" class="cursor-pointer whitespace-nowrap">
                <div class="flex items-center gap-1">
                  Driver Name
                  <Icon 
                    v-if="sortColumn === 'driver_name'" 
                    :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" 
                    class="h-4 w-4" 
                  />
                  <Icon v-else name="arrow-up-down" class="h-4 w-4 opacity-50" />
                </div>
              </TableHead>
              <TableHead @click="sortBy('acceptance_score')" class="cursor-pointer text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1">
                  Acceptance
                  <Icon 
                    v-if="sortColumn === 'acceptance_score'" 
                    :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" 
                    class="h-4 w-4" 
                  />
                  <Icon v-else name="arrow-up-down" class="h-4 w-4 opacity-50" />
                </div>
              </TableHead>
              <TableHead @click="sortBy('on_time_score')" class="cursor-pointer text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1">
                  On-Time
                  <Icon 
                    v-if="sortColumn === 'on_time_score'" 
                    :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" 
                    class="h-4 w-4" 
                  />
                  <Icon v-else name="arrow-up-down" class="h-4 w-4 opacity-50" />
                </div>
              </TableHead>
              <TableHead @click="sortBy('safety_score')" class="cursor-pointer text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1">
                  Green Zone Score
                  <Icon 
                    v-if="sortColumn === 'safety_score'" 
                    :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'" 
                    class="h-4 w-4" 
                  />
                  <Icon v-else name="arrow-up-down" class="h-4 w-4 opacity-50" />
                </div>
              </TableHead>
              
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow 
              v-for="(driver, index) in paginatedDrivers" 
              :key="driver.driver_name"
              class="hover:bg-muted/20 transition-colors"
            >
              <TableCell class="text-center">
                <Badge variant="outline" class="min-w-[28px] justify-center">
                  {{ startIndex + index + 1 }}
                </Badge>
              </TableCell>
              <TableCell class="font-medium">{{ driver.driver_name }}</TableCell>
              <TableCell class="text-right">
                <Badge :variant="getScoreBadgeVariant(driver.acceptance_score)">
                  {{ driver.acceptance_score }}%
                </Badge>
              </TableCell>
              <TableCell class="text-right">
                <Badge :variant="getScoreBadgeVariant(driver.on_time_score)">
                  {{ driver.on_time_score }}%
                </Badge>
              </TableCell>
              <TableCell class="text-right">
                <Badge :variant="getScoreBadgeVariant(driver.safety_score)">
                  <!-- {{ driver.safety_score }} -->
                1050
                </Badge>
              </TableCell>
              <!-- <TableCell class="text-right font-semibold" :class="getScoreColorClass(driver.overall_score)">
                <div class="flex items-center justify-end gap-2">
                  <div class="w-16 bg-muted rounded-full h-2 overflow-hidden">
                    <div 
                      class="h-full rounded-full" 
                      :class="getProgressBarColorClass(driver.overall_score)"
                      :style="{ width: `${driver.overall_score}%` }"
                    ></div>
                  </div>
                  {{ driver.overall_score }}%
                </div>
              </TableCell> -->
            </TableRow>
            <TableRow v-if="filteredDrivers.length === 0">
              <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                <div class="flex flex-col items-center justify-center gap-2">
                  <Icon name="users-x" class="h-10 w-10 opacity-50" />
                  <p v-if="searchQuery">No drivers found matching "{{ searchQuery }}"</p>
                  <p v-else>No driver performance data available for the selected period.</p>
                  <Button v-if="searchQuery" variant="outline" size="sm" @click="searchQuery = ''">
                    Clear search
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      
      <!-- Pagination Controls -->
      <div class="border-t bg-muted/20 px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-2">
        <div class="flex items-center gap-4 text-sm text-muted-foreground">
          <span class="flex items-center gap-1">
            <Icon name="list" class="h-4 w-4" />
            Showing {{ startIndex + 1 }}-{{ endIndex }} of {{ totalItems }} drivers
          </span>
          <span class="flex items-center gap-1">
            <Icon name="layout-grid" class="h-4 w-4" />
            Per page:
          </span>
          <div class="relative">
            <select 
              v-model.number="pageSize" 
              class="h-8 appearance-none rounded-md border bg-background px-2 py-1 text-sm focus:ring-2 focus:ring-ring"
            >
              <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">{{ n }}</option>
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
          <Button 
            size="sm" 
            variant="ghost" 
            @click="currentPage--" 
            :disabled="currentPage === 1"
            class="flex items-center gap-1"
          >
            <Icon name="chevron-left" class="h-4 w-4" /> Prev
          </Button>
          
          <Button 
            v-for="page in visiblePageNumbers" 
            :key="page" 
            size="sm" 
            variant="ghost"
            @click="page !== -1 ? currentPage = page : null"
            :class="{ 
              'border-primary bg-primary/10 text-primary font-medium': page === currentPage,
              'opacity-50 pointer-events-none': page === -1
            }"
          >
            {{ page === -1 ? '...' : page }}
          </Button>
          
          <Button 
            size="sm" 
            variant="ghost" 
            @click="currentPage++" 
            :disabled="currentPage === totalPages"
            class="flex items-center gap-1"
          >
            Next <Icon name="chevron-right" class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Table,
  TableHeader,
  TableBody,
  TableHead,
  TableRow,
  TableCell,
  Badge,
  Button,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Input
} from '@/components/ui';
import Icon from '@/components/Icon.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';

const props = defineProps({
  driversData: {
    type: Array,
    default: () => []
  }
});

// Search functionality
const searchQuery = ref('');

// Sorting functionality
const sortColumn = ref('overall_score');
const sortDirection = ref('desc');

const sortBy = (column) => {
  if (sortColumn.value === column) {
    // Toggle direction if clicking the same column
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column and default to descending for scores
    sortColumn.value = column;
    sortDirection.value = column === 'driver_name' ? 'asc' : 'desc';
  }
};

// Reset filters
const resetFilters = () => {
  searchQuery.value = '';
  sortColumn.value = 'overall_score';
  sortDirection.value = 'desc';
  currentPage.value = 1;
  pageSize.value = 10;
};

// Filter drivers by search query
const filteredDrivers = computed(() => {
  if (!props.driversData || props.driversData.length === 0) return [];
  
  if (!searchQuery.value) return props.driversData;
  
  const query = searchQuery.value.toLowerCase();
  return props.driversData.filter(driver => 
    driver.driver_name.toLowerCase().includes(query)
  );
});

// Sort filtered drivers
const sortedDrivers = computed(() => {
  if (filteredDrivers.value.length === 0) return [];
  
  return [...filteredDrivers.value].sort((a, b) => {
    const valueA = a[sortColumn.value];
    const valueB = b[sortColumn.value];
    
    // For string values (like driver_name)
    if (typeof valueA === 'string') {
      if (sortDirection.value === 'asc') {
        return valueA.localeCompare(valueB);
      } else {
        return valueB.localeCompare(valueA);
      }
    }
    
    // For numeric values
    if (sortDirection.value === 'asc') {
      return valueA - valueB;
    } else {
      return valueB - valueA;
    }
  });
});

// Pagination functionality
const currentPage = ref(1);
const pageSize = ref(10);

// Reset to page 1 when sorting changes or search query changes
watch([sortColumn, sortDirection, searchQuery], () => {
  currentPage.value = 1;
});

// Reset to page 1 when page size changes
watch(pageSize, () => {
  currentPage.value = 1;
});

// Calculate total pages
const totalItems = computed(() => sortedDrivers.value.length);
const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / pageSize.value)));

// Ensure current page is valid
watch([totalPages, currentPage], () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = totalPages.value;
  }
});

// Calculate visible page numbers (show 5 pages at most)
const visiblePageNumbers = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  
  if (total <= 5) {
    // If 5 or fewer pages, show all
    return Array.from({ length: total }, (_, i) => i + 1);
  }
  
  // Always include first, last, current, and one on each side of current if possible
  let pages = [1, total];
  
  // Add current page and neighbors
  const neighbors = [current - 1, current, current + 1].filter(p => p > 1 && p < total);
  pages.push(...neighbors);
  
  // Sort and deduplicate
  pages = [...new Set(pages)].sort((a, b) => a - b);
  
  // Add ellipses where needed
  const result = [];
  let prev = 0;
  
  for (const page of pages) {
    if (page - prev > 1) {
      // Add ellipsis for gaps
      result.push(-1); // Use -1 to represent ellipsis
    }
    result.push(page);
    prev = page;
  }
  
  return result;
});

// Get paginated data
const startIndex = computed(() => (currentPage.value - 1) * pageSize.value);
const endIndex = computed(() => Math.min(startIndex.value + pageSize.value, totalItems.value));

const paginatedDrivers = computed(() => {
  return sortedDrivers.value.slice(startIndex.value, endIndex.value);
});

// Helper functions for styling
const getScoreBadgeVariant = (score) => {
  if (score >= 90) return 'success';
  if (score >= 75) return 'default';
  if (score >= 60) return 'warning';
  return 'destructive';
};

const getScoreColorClass = (score) => {
  if (score >= 90) return 'text-green-600 dark:text-green-500';
  if (score >= 75) return 'text-blue-600 dark:text-blue-500';
  if (score >= 60) return 'text-amber-600 dark:text-amber-500';
  return 'text-red-600 dark:text-red-500';
};

const getProgressBarColorClass = (score) => {
  if (score >= 90) return 'bg-green-600 dark:bg-green-500';
  if (score >= 75) return 'bg-blue-600 dark:bg-blue-500';
  if (score >= 60) return 'bg-amber-600 dark:bg-amber-500';
  return 'bg-red-600 dark:bg-red-500';
};
</script>