<template>
  <div
    class="mx-auto mb-6 grid max-w-[95vw] grid-cols-1 gap-4 md:max-w-[64vw] md:grid-cols-2 lg:max-w-full"
  >
    <div class="rounded-lg border bg-card shadow-sm">
      <div class="border-b p-4">
        <h3 class="text-lg font-semibold">Top 5 Frequent Repairs</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm">Parts:</span>
            <span class="text-sm">Work Orders:</span>
          </div>
          <li
            v-for="(area, index) in topAreasOfConcern"
            :key="index"
            class="flex items-center justify-between pr-4"
          >
            <span class="text-sm">{{ area.concern }}</span>
            <Badge variant="outline">{{ area.count }}</Badge>
          </li>
          <li
            v-if="topAreasOfConcern.length === 0"
            class="text-center text-muted-foreground"
          >
            No data available
          </li>
        </ul>
      </div>
    </div>

    <div class="rounded-lg border bg-card shadow-sm">
      <div class="border-b p-4">
        <h3 class="text-lg font-semibold">Top 5 Frequently Repaired Tractors</h3>
      </div>
      <div class="p-4">
        <ul class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm">Asset ID:</span>
            <span class="text-sm">Work Orders:</span>
          </div>
          <li
            v-for="(truck, index) in topWorkOrdersByTruck"
            :key="index"
            class="flex items-center justify-between pr-4"
          >
            <span class="text-sm">{{ truck.truckid }}</span>
            <Badge variant="outline">{{ truck.work_order_count }}</Badge>
          </li>
          <li
            v-if="topWorkOrdersByTruck.length === 0"
            class="text-center text-muted-foreground"
          >
            No data available
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Badge } from "@/components/ui";

defineProps<{
  topAreasOfConcern: any[];
  topWorkOrdersByTruck: any[];
}>();
</script>
