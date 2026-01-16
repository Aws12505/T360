<template>
  <Alert v-if="successMessage" variant="success" class="animate-in fade-in duration-300">
    <AlertTitle class="flex items-center gap-2">
      <Icon name="check_circle" class="h-5 w-5 text-green-500" />
      Success
    </AlertTitle>
    <AlertDescription>{{ successMessage }}</AlertDescription>
  </Alert>

  <Alert
    v-if="errorMessage"
    variant="destructive"
    class="animate-in fade-in duration-300"
  >
    <AlertTitle class="flex items-center gap-2">
      <Icon name="alert_circle" class="h-5 w-5" />
      Error
    </AlertTitle>
    <AlertDescription>{{ errorMessage }}</AlertDescription>
  </Alert>
</template>

<script setup lang="ts">
import Icon from "@/components/Icon.vue";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui";

defineProps<{
  successMessage: string;
  errorMessage: string;
}>();
</script>
