import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      ssr: 'resources/js/ssr.ts',
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy'),
    },
  },
  css: {
    postcss: {
      plugins: [tailwindcss, autoprefixer],
    },
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            if (id.includes('lucide-vue-next')) return 'vendor-icons';
            if (id.includes('d3') || id.includes('@emotion')) return 'vendor-d3';
            if (id.includes('radix-vue') || id.includes('floating-ui')) return 'vendor-ui';
            if (id.includes('axios')) return 'vendor-axios';
            if (id.includes('lodash')) return 'vendor-lodash';
            if (id.includes('@inertiajs') || id.includes('/vue')) return 'vendor-vue';
            return 'vendor';
          }
        },
      },
    },
  },
});
