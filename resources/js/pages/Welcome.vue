<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col">
    <!-- Header -->
    <header class="border-b border-gray-200 dark:border-gray-700">
      <div class="container mx-auto px-4 py-4 flex justify-end">
        <nav class="flex items-center gap-4">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded transition-colors duration-200 hover:border-gray-400 dark:hover:border-gray-500"
          >
            Dashboard
          </Link>
          <template v-else>
            <Link
              :href="route('login')"
              class="px-4 py-2 border border-transparent rounded transition-colors duration-200 hover:border-gray-300 dark:hover:border-gray-600"
            >
              Log in
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
      <section class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-center">Subscription Plans</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <a
            href="https://billing.zohosecure.com/subscribe/be5e52d21f0ce61633149104eb68f5871f563cd205dd5841ad482bf477cf719e/sadasd"
            target="_blank"
            rel="noopener noreferrer"
            class="block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200 text-center"
          >
            <h3 class="text-xl font-semibold mb-2">Basic Plan</h3>
          </a>
          <a
            href="https://external-site.com/plan-premium"
            target="_blank"
            rel="noopener noreferrer"
            class="block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-200 text-center"
          >
            <h3 class="text-xl font-semibold mb-2">Premium Plan</h3>
          </a>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="py-4 border-t border-gray-200 dark:border-gray-700 text-center">
      <p class="text-sm">&copy; 2025 Company Name. All rights reserved.</p>
    </footer>
  </div>
</template>
