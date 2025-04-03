import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { trackEvent, trackPageView } from '@/lib/tracking'; // Import the tracking helpers
import { initializeSession } from '@/lib/session-tracking';
import { router } from '@inertiajs/vue3';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize session tracking when the app starts
initializeSession();

// Record the time when the current page was entered.
let pageEnterTime: number = Date.now();
let currentPageUrl: string = window.location.href;
let currentPageTitle: string = document.title;

// ---------------------------------------------------------
// Tracking Integration - Consolidated implementation
// ---------------------------------------------------------

// Listen for Inertia navigation events to track page views and time spent.
router.on('navigate', () => {
    // Calculate the time spent on the previous page (in milliseconds)
    const timeSpent = Date.now() - pageEnterTime;

    // Track time spent on previous page with the correct previous page data
    trackEvent('pageTime', {
        timeSpent,
        pageUrl: currentPageUrl,
        pageTitle: currentPageTitle
    });

    // Update the enter time for the new page
    pageEnterTime = Date.now();

    // Small delay to ensure the page title and URL are updated
    setTimeout(() => {
        // Store current page info for next navigation
        currentPageUrl = window.location.href;
        currentPageTitle = document.title;
        
        // Track the new page view with enhanced data
        trackEvent('pageView', {
            pageUrl: currentPageUrl,
            pageTitle: currentPageTitle,
            referrer: document.referrer,
            screenWidth: window.innerWidth,
            screenHeight: window.innerHeight
        });
    }, 100); // Slightly longer delay to ensure title is updated
});

// Initial page view tracking for the first page load
document.addEventListener('DOMContentLoaded', () => {
    // Store initial page info
    currentPageUrl = window.location.href;
    currentPageTitle = document.title;
    
    // Track initial page view
    trackEvent('pageView', {
        pageUrl: currentPageUrl,
        pageTitle: currentPageTitle,
        referrer: document.referrer,
        screenWidth: window.innerWidth,
        screenHeight: window.innerHeight
    });
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();