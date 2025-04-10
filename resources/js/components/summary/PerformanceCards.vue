<template>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
    <!-- On-Time Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">On-Time Score</h3>
      <div class="text-sm text-muted-foreground mb-1">Total Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">99%</div>
      
      <div class="text-sm mb-2 flex justify-between">
        <span>Delays by reason</span>
        <span>Total</span>
      </div>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span>Driver arriving late</span>
          <span>2</span>
        </div>
        <div class="flex justify-between">
          <span>Mechanical tractor</span>
          <span>12</span>
        </div>
        <div class="flex justify-between">
          <span>Medical</span>
          <span>1</span>
        </div>
      </div>
      <div class="mt-3">
        <Badge variant="success" class="text-xs">Fantastic +</Badge>
      </div>
    </div>

    <!-- Acceptance Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">Acceptance Score</h3>
      <div class="text-sm text-muted-foreground mb-1">Total Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">99%</div>
      
      <div class="text-sm mb-2 flex justify-between">
        <span>Rejections by reason</span>
        <span>Total</span>
      </div>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span>Driver arriving late</span>
          <span>1</span>
        </div>
        <div class="flex justify-between">
          <span>Mechanical tractor</span>
          <span>1</span>
        </div>
        <div class="flex justify-between">
          <span>Medical</span>
          <span>2</span>
        </div>
        <div class="flex justify-between">
          <span>HOS</span>
          <span>2</span>
        </div>
      </div>
      <div class="mt-3">
        <Badge variant="success" class="text-xs">Fantastic +</Badge>
      </div>
    </div>

    <!-- Maintenance Metrics Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">Maintenance Metrics</h3>
      <div class="text-sm text-muted-foreground mb-1">Total Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">99%</div>
      
      <div class="text-sm mb-2">
        <div class="flex justify-between">
          <span>Cost per mile</span>
          <span>2</span>
        </div>
      </div>
      <div class="mt-3">
        <Badge variant="success" class="text-xs">Fantastic +</Badge>
      </div>
    </div>

    <!-- Safety Score Card -->
    <div class="bg-card rounded-lg border shadow-sm p-4">
      <h3 class="text-lg font-semibold mb-2">Safety Score</h3>
      <div class="text-sm text-muted-foreground mb-1">Green Zone Score</div>
      <div class="text-4xl font-bold text-indigo-600 mb-4">1040</div>
      
      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span>Traffic Light Violation</span>
          <span>51</span>
        </div>
        <div class="flex justify-between">
          <span>Speeding Violations</span>
          <span>51</span>
        </div>
        <div class="flex justify-between">
          <span>Following Distance Hard Brake</span>
          <span>51</span>
        </div>
        <div class="flex justify-between">
          <span>Driver Distraction</span>
          <span>51</span>
        </div>
        <div class="flex justify-between">
          <span>Sign Violations</span>
          <span>51</span>
        </div>
      </div>
      <div class="mt-3">
        <Badge variant="success" class="text-xs">Golden Tier</Badge>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
</script>