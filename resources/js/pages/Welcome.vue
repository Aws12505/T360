<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>
  <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
    <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
      <nav class="flex items-center justify-end gap-4">
        <Link
          v-if="$page.props.auth.user"
          :href="route('dashboard')"
          class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
        >
          Dashboard
        </Link>
        <template v-else>
          <Link
            :href="route('login')"
            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
          >
            Log in
          </Link>
        </template>
      </nav>
    </header>
    
    <!-- Subscription Plans Section -->
    <section class="w-full max-w-[335px] lg:max-w-4xl">
      <h2 class="mb-4 text-xl font-bold">Subscription Plans</h2>
      <div class="flex flex-col gap-4">
        <a
          href="https://billing.zohosecure.com/subscribe/be5e52d21f0ce61633149104eb68f5871f563cd205dd5841ad482bf477cf719e/sadasd"
          class="block rounded-sm border border-[#19140035] px-5 py-3 text-center text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
          target="_blank"
          rel="noopener noreferrer"
        >
          Basic Plan
        </a>
        <a
          href="https://external-site.com/plan-premium"
          class="block rounded-sm border border-[#19140035] px-5 py-3 text-center text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
          target="_blank"
          rel="noopener noreferrer"
        >
          Premium Plan
        </a>
      </div>
    </section>
  </div>
</template>
