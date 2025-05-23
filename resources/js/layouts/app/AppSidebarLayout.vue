<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import Breadcrumb from '@/components/ui/breadcrumb/Breadcrumb.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    tenantSlug?: string | null;
    permissions?: Array<string>;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    tenantSlug: null, 
    permissions: [],
});

// Enhanced responsive handling with multiple breakpoints
const screenSize = ref('desktop'); // default

// Function to check screen size with multiple breakpoints
const checkScreenSize = () => {
    const width = window.innerWidth;
    if (width < 640) {
        screenSize.value = 'mobile';
    } else if (width < 1024) {
        screenSize.value = 'tablet';
    } else if (width < 1280) {
        screenSize.value = 'laptop';
    } else {
        screenSize.value = 'desktop';
    }
};

// Set up event listeners for responsive behavior
onMounted(() => {
    checkScreenSize(); // Check on initial load
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :breadcrumbs="props.breadcrumbs" :tenantSlug="props.tenantSlug" :permissions="props.permissions"/>
        <AppContent 
            variant="sidebar" 
            :class="{
                'responsive-content': true,
                'mobile': screenSize === 'mobile',
                'tablet': screenSize === 'tablet',
                'laptop': screenSize === 'laptop',
                'desktop': screenSize === 'desktop'
            }"
        >
            <AppSidebarHeader :breadcrumbs="props.breadcrumbs" />
            <div class="content-wrapper">
                <slot />
            </div>
        </AppContent>
    </AppShell>
</template>

<style scoped>
.content-wrapper {
    width: 100%;
    overflow-x: hidden; /* Change from auto to hidden to prevent horizontal overflow */
    max-width: 100%;
    box-sizing: border-box; /* Ensure padding is included in width calculations */
}

.responsive-content {
    max-width: 100vw;
    transition: padding 0.3s ease;
    overflow: hidden; /* Add overflow hidden to prevent content from expanding beyond container */
    box-sizing: border-box; /* Ensure padding is included in width calculations */
}

/* Desktop styles (default) */
.responsive-content.desktop {
    padding: 0 2rem;
}

/* Laptop styles */
.responsive-content.laptop {
    padding: 0 1.5rem;
}

/* Tablet styles */
.responsive-content.tablet {
    padding: 0 1rem;
}

/* Mobile styles */
.responsive-content.mobile {
    padding: 0 0.5rem;
}

/* Add responsive styles for tables and other wide content */
:deep(table) {
    width: 100%;
    min-width: auto; /* Override any fixed min-width */
    table-layout: auto; /* Allow the table to adjust based on content */
    max-width: 100%; /* Ensure tables don't exceed container width */
}

/* Responsive table handling */
@media (max-width: 1279px) {
    :deep(table) {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
        max-width: 100%; /* Ensure tables don't exceed container width */
    }
}

/* Additional mobile optimizations */
@media (max-width: 639px) {
    :deep(.hide-on-mobile) {
        display: none;
    }
    
    :deep(button, .button) {
        padding: 0.5rem 0.75rem;
    }
}

/* Ensure images and other media are responsive */
:deep(img, video, iframe) {
    max-width: 100%;
    height: auto;
}

/* Improve form elements responsiveness */
:deep(input, select, textarea) {
    max-width: 100%;
    box-sizing: border-box;
}

/* Add strict containment for all elements */
:deep(*) {
    max-width: 100%;
    box-sizing: border-box;
    word-wrap: break-word; /* Allow long words to break */
    overflow-wrap: break-word;
}

/* Ensure text doesn't overflow */
:deep(p, h1, h2, h3, h4, h5, h6, span, div) {
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}
</style>
