<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img src="/logo.svg" :class="className" style="background-color:white; border-radius: 50%; aspect-ratio: 1; object-fit: contain; padding: 4px;" v-bind="$attrs" alt="Logo" />
</template>
