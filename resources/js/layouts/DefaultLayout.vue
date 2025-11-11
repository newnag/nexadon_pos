<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <div
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center justify-center h-16 bg-gray-800 border-b border-gray-700">
        <Link href="/dashboard" class="text-xl font-bold text-white flex items-center">
          <svg class="w-8 h-8 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          Nexadon POS
        </Link>
      </div>

      <!-- Navigation -->
      <nav class="mt-5 px-2">
        <Link
          href="/dashboard"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/dashboard')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          หน้าหลัก
        </Link>

        <Link
          v-if="hasRole(['Admin', 'Manager'])"
          href="/menu"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/menu')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          จัดการเมนู
        </Link>

        <Link
          v-if="hasRole(['Admin', 'Manager'])"
          href="/modifiers"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/modifiers')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          ตัวเลือกเพิ่มเติม
        </Link>

        <Link
          v-if="hasRole(['Admin', 'Manager'])"
          href="/categories"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/categories')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
          หมวดหมู่อาหาร
        </Link>

        <Link
          href="/tables"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/tables')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          จัดการโต๊ะ
        </Link>

        <Link
          v-if="authStore.hasRole(['Admin', 'Manager'])"
          href="/kitchen"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/kitchen')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
          </svg>
          จอครัว
        </Link>

        <Link
          href="/order-history"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/order-history')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          ประวัติการสั่งอาหาร
        </Link>

        <Link
          v-if="hasRole(['Admin', 'Manager'])"
          href="/reports"
          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md mb-1"
          :class="isActive('/reports')"
        >
          <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          รายงาน
        </Link>
      </nav>

      <!-- User Info & Logout -->
      <div class="absolute bottom-0 left-0 right-0 p-4 bg-gray-800 border-t border-gray-700">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
              {{ authUser?.name?.charAt(0).toUpperCase() }}
            </div>
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-white">
              {{ authUser?.name }}
            </p>
            <p class="text-xs text-gray-400">
              {{ userRole }}
            </p>
          </div>
        </div>
        <button
          @click="handleLogout"
          class="mt-3 w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          ออกจากระบบ
        </button>
      </div>
    </div>

    <!-- Mobile menu button - Moved to top bar -->
    <div class="lg:hidden fixed top-3 left-3 z-50">
      <button
        @click="toggleSidebar"
        class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-700 bg-white shadow-lg hover:bg-gray-100 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
        aria-label="เปิดเมนู"
      >
        <!-- Animated Hamburger Icon -->
        <div class="w-5 h-4 flex flex-col justify-between">
          <span
            :class="{ 'rotate-45 translate-y-1.5': sidebarOpen }"
            class="block h-0.5 w-full bg-current transform transition duration-300 ease-in-out rounded-full"
          ></span>
          <span
            :class="{ 'opacity-0 scale-0': sidebarOpen }"
            class="block h-0.5 w-full bg-current transform transition duration-300 ease-in-out rounded-full"
          ></span>
          <span
            :class="{ '-rotate-45 -translate-y-1.5': sidebarOpen }"
            class="block h-0.5 w-full bg-current transform transition duration-300 ease-in-out rounded-full"
          ></span>
        </div>
      </button>
    </div>

    <!-- Overlay for mobile -->
    <div
      v-if="sidebarOpen"
      @click="toggleSidebar"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    ></div>

    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Top bar -->
      <div class="sticky top-0 z-10 bg-white shadow-sm border-b border-gray-200">
        <div class="h-14 sm:h-16 flex items-center justify-between px-3 sm:px-6 lg:px-8">
          <!-- Left: Spacer for mobile menu button + Title -->
          <div class="flex items-center min-w-0 flex-1">
            <!-- Spacer to prevent title overlap with hamburger on mobile -->
            <div class="lg:hidden w-12 flex-shrink-0"></div>
            <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900 truncate">
              {{ pageTitle }}
            </h1>
          </div>
          
          <!-- Right: Date & Time -->
          <div class="flex items-center flex-shrink-0 ml-4">
            <div class="text-right">
              <div class="text-xs sm:text-sm text-gray-500 leading-tight">
                {{ currentDateTime }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Page content -->
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { Link, router, usePage } from '@inertiajs/vue3';

const authStore = useAuthStore();
const page = usePage();

const currentPath = computed(() => page.url);

// Get user data from Inertia props (always up-to-date)
const authUser = computed(() => (page.props.auth as any)?.user || null);
const userRole = computed(() => authUser.value?.role?.name || null);

// Helper function to check if user has role
const hasRole = (roles: string | string[]): boolean => {
  if (!userRole.value) return false;
  const roleArray = Array.isArray(roles) ? roles : [roles];
  return roleArray.includes(userRole.value);
};

const sidebarOpen = ref(false);
const currentDateTime = ref('');

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    '/dashboard': 'หน้าหลัก',
    '/menu': 'จัดการเมนู',
    '/modifiers': 'ตัวเลือกเพิ่มเติม',
    '/categories': 'หมวดหมู่อาหาร',
    '/tables': 'จัดการโต๊ะ',
    '/kitchen': 'จอครัว',
    '/order-history': 'ประวัติการสั่งอาหาร',
    '/reports': 'รายงานและสถิติ',
  };
  return titles[currentPath.value] || 'Nexadon POS';
});

const isActive = (path: string) => {
  return currentPath.value === path
    ? 'bg-gray-800 text-white'
    : 'text-gray-300 hover:bg-gray-700 hover:text-white';
};

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleLogout = async () => {
  if (confirm('คุณต้องการออกจากระบบหรือไม่?')) {
    await authStore.logout();
    router.visit('/login');
  }
};

const updateDateTime = () => {
  const now = new Date();
  currentDateTime.value = now.toLocaleString('th-TH', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

let intervalId: number;

onMounted(() => {
  updateDateTime();
  intervalId = window.setInterval(updateDateTime, 60000); // Update every minute
});

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});
</script>
