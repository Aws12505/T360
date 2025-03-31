<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
// Importing ShadCN UI components (adjust the import paths based on your project structure)
import Card from '@/components/ui/card/Card.vue';
import Button from '@/components/ui/button/Button.vue';
</script>

<template>
  <!-- Page Head with title and external font stylesheet -->
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col">
    <!-- Header -->
    <header class="border-b border-gray-200 dark:border-gray-700">
      <div class="container mx-auto px-4 py-4 flex justify-end">
        <nav class="flex items-center gap-4">
          <!-- Dashboard or Login button using ShadCN Button component -->
          <template v-if="$page.props.auth.user">
            <Link :href="route('dashboard')" class="block">
              <Button variant="outline" class="px-4 py-2">
                Dashboard
              </Button>
            </Link>
          </template>
          <template v-else>
            <Link :href="route('login')" class="block">
              <Button variant="default" class="px-4 py-2">
                Log in
              </Button>
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
      <section class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold mb-8 text-center">Subscription Plans</h2>
        <!-- Use a grid layout for plan cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Subscription Plan Card using ShadCN Card component -->
          <Card class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <h3 class="text-xl font-semibold mb-2 text-center">Basic Plan</h3>
            <div class="flex justify-center">
              <a
  href="https://billing.zohosecure.com/subscribe/be5e52d21f0ce61633149104eb68f5871f563cd205dd5841ad482bf477cf719e/sadasd"
  target="_blank"
  rel="noopener noreferrer"
>
  <Button variant="default" class="mt-4">
    Choose Plan
  </Button>
</a>
            </div>
          </Card>
          <!-- Second Subscription Plan Card -->
          <Card class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
            <h3 class="text-xl font-semibold mb-2 text-center">Premium Plan</h3>
            <div class="flex justify-center">
              <Link
                href="https://external-site.com/plan-premium"
                target="_blank"
                rel="noopener noreferrer"
              >
                <Button variant="default" class="mt-4">
                  Choose Plan
                </Button>
              </Link>
            </div>
          </Card>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="py-4 border-t border-gray-200 dark:border-gray-700 text-center">
      <p class="text-sm">&copy; 2025 Company Name. All rights reserved.</p>
    </footer>
  </div>
</template>
