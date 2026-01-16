<template>
  <Card class="shadow-sm border bg-card">
    <CardContent class="p-3 md:p-4">
      <div class="flex flex-wrap gap-2">
        <Button
          v-for="opt in dateOptions"
          :key="opt.value"
          size="sm"
          variant="outline"
          :class="{
            'border-primary bg-primary/10 text-primary': filter.dateFilter === opt.value,
          }"
          @click="$emit('selectDate', opt.value)"
        >
          {{ opt.label }}
        </Button>
      </div>

      <div v-if="dateRange" class="text-sm text-muted-foreground mt-2">
        <span v-if="dateFilter === 'yesterday' && dateRange.start">
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
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, Button } from "@/components/ui";

defineProps<{
  dateOptions: Array<{ label: string; value: string }>;
  filter: any;
  dateRange: any;
  dateFilter: string;
  weekNumberText: string;
  formatDate: (d: string) => string;
}>();

defineEmits<{
  (e: "selectDate", v: string): void;
}>();
</script>
