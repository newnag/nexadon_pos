<template>
  <GuestLayout>
    <div class="text-center">
      <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
      <p class="text-xl text-gray-600 mb-8">Page not found</p>
      <Link
        href="/dashboard"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
      >
        Go to Dashboard
      </Link>
    </div>
  </GuestLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';
</script>
