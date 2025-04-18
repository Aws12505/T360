import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, defineAsyncComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { trackEvent } from '@/lib/tracking'; // Import the tracking helpers
import { initializeSession } from '@/lib/session-tracking';
import { router } from '@inertiajs/vue3';

// Extend ImportMeta interface for Vite
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
  const timeSpent = Date.now() - pageEnterTime;

  trackEvent('pageTime', {
    timeSpent,
    pageUrl: currentPageUrl,
    pageTitle: currentPageTitle,
  });

  pageEnterTime = Date.now();

  setTimeout(() => {
    currentPageUrl = window.location.href;
    currentPageTitle = document.title;

    trackEvent('pageView', {
      pageUrl: currentPageUrl,
      pageTitle: currentPageTitle,
      referrer: document.referrer,
      screenWidth: window.innerWidth,
      screenHeight: window.innerHeight,
    });
  }, 100);
});

// Initial page view tracking for the first page load
document.addEventListener('DOMContentLoaded', () => {
  currentPageUrl = window.location.href;
  currentPageTitle = document.title;

  trackEvent('pageView', {
    pageUrl: currentPageUrl,
    pageTitle: currentPageTitle,
    referrer: document.referrer,
    screenWidth: window.innerWidth,
    screenHeight: window.innerHeight,
  });
});

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue')
    ),
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      // Lazy-load heavy shared components
      .component(
        'Icon',
        defineAsyncComponent(() => import('@/Components/Icon.vue'))
      );

    vueApp.mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});

// This will set light / dark mode on page load
initializeTheme();
