<template>
  <Card class="shadow-sm border">
    <CardHeader class="p-2 md:p-4 lg:p-6 border-b">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-2">
          <CardTitle class="text-lg md:text-xl lg:text-2xl">Filters</CardTitle>

          <div
            v-if="!showFilters && activeFilterBadges.length"
            class="ml-4 flex flex-wrap gap-2"
          >
            <div
              v-for="(badge, idx) in activeFilterBadges"
              :key="idx"
              class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold"
            >
              {{ badge }}
            </div>
          </div>
        </div>

        <Button
          variant="ghost"
          size="sm"
          @click="$emit('toggleShowFilters')"
          class="flex items-center gap-1.5 text-muted-foreground hover:text-foreground transition-colors"
        >
          <span class="text-sm hidden sm:inline">{{
            showFilters ? "Hide Filters" : "Show Filters"
          }}</span>
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
                  <option v-for="v in vendors" :key="v.id" :value="v.id">
                    {{ v.vendor_name }}
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
                  <option v-for="s in woStatuses" :key="s.id" :value="s.id">
                    {{ s.name }}
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
          </div>

          <div class="flex justify-end space-x-2">
            <Button @click="$emit('resetFilters')" variant="ghost" size="sm">
              <Icon name="rotate-ccw" class="mr-2 h-4 w-4" />
              Reset Filters
            </Button>
            <Button @click="$emit('applyFilters')" variant="default" size="sm">
              <Icon name="filter" class="mr-2 h-4 w-4" />
              Apply Filters
            </Button>
          </div>
        </div>
      </CardContent>
    </Transition>
  </Card>
</template>

<script setup lang="ts">
import Icon from "@/components/Icon.vue";
import {
  Button,
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  Input,
  Label,
} from "@/components/ui";

defineProps<{
  showFilters: boolean;
  activeFilterBadges: string[];
  filter: any;
  vendors: any[];
  woStatuses: any[];
}>();

defineEmits<{
  (e: "toggleShowFilters"): void;
  (e: "resetFilters"): void;
  (e: "applyFilters"): void;
}>();
</script>
