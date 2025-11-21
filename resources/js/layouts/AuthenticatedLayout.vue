<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
        <div class="flex items-center justify-between h-14 sm:h-16">
          <!-- Left Section: Menu Button + Logo -->
          <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <!-- Mobile menu button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-700 hover:bg-gray-100 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
              aria-label="เปิดเมนู"
            >
              <!-- Hamburger Icon with animation -->
              <div class="w-5 h-4 flex flex-col justify-between">
                <span
                  :class="{ 'rotate-45 translate-y-1.5': mobileMenuOpen }"
                  class="block h-0.5 w-full bg-current transform transition duration-300 ease-in-out rounded-full"
                ></span>
                <span
                  :class="{ 'opacity-0 scale-0': mobileMenuOpen }"
                  class="block h-0.5 w-full bg-current transform transition duration-300 ease-in-out rounded-full"
                ></span>
                <span
                  :class="{ '-rotate-45 -translate-y-1.5': mobileMenuOpen }"
                  class="block h-0.5 w-full bg-current transform transition duration-300 ease-in-out rounded-full"
                ></span>
              </div>
            </button>

            <!-- Logo/Brand -->
            <Link 
              href="/dashboard" 
              class="flex-shrink-0 text-base sm:text-lg md:text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors truncate"
            >
              <span class="hidden sm:inline">Nexadon POS</span>
              <span class="sm:hidden">การโต๊ะ</span>
            </Link>
          </div>

          <!-- Desktop Navigation Links -->
          <div class="hidden md:flex md:items-center md:space-x-1 lg:space-x-2">
            <Link
              href="/dashboard"
              class="inline-flex items-center px-3 lg:px-4 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="isActive('/dashboard')"
            >
              หน้าหลัก
            </Link>

            <Link
              v-if="hasRole(['Admin', 'Manager'])"
              href="/menu"
              class="inline-flex items-center px-3 lg:px-4 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="isActive('/menu')"
            >
              จัดการเมนู
            </Link>

            <Link
              href="/tables"
              class="inline-flex items-center px-3 lg:px-4 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="isActive('/tables')"
            >
              จัดการโต๊ะ
            </Link>

            <Link
              href="/takeaway"
              class="inline-flex items-center px-3 lg:px-4 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="isActive('/takeaway')"
            >
              ออเดอร์กลับบ้าน
            </Link>

            <Link
              v-if="hasRole(['Admin', 'Manager'])"
              href="/kitchen"
              class="inline-flex items-center px-3 lg:px-4 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="isActive('/kitchen')"
            >
              จอครัว
            </Link>

            <Link
              v-if="hasRole(['Admin', 'Manager'])"
              href="/reports"
              class="inline-flex items-center px-3 lg:px-4 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="isActive('/reports')"
            >
              รายงาน
            </Link>
          </div>

          <!-- Right Section: Time + User Menu -->
          <div class="flex items-center gap-2 sm:gap-3">
            <!-- Current Time -->
            <div class="flex-shrink-0 text-right">
              <div class="text-xs text-gray-500 leading-tight">
                {{ currentTime }}
              </div>
            </div>

            <!-- Desktop User Menu -->
            <div class="hidden md:flex items-center gap-2 lg:gap-3">
              <div class="h-6 w-px bg-gray-300"></div>
              <div class="flex items-center gap-2">
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">{{ authUser?.name }}</div>
                  <div class="text-xs text-gray-500">{{ userRole }}</div>
                </div>
                <button
                  @click="handleLogout"
                  class="p-2 rounded-lg text-gray-600 hover:text-red-600 hover:bg-red-50 transition-colors"
                  title="ออกจากระบบ"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div
        v-show="mobileMenuOpen"
        class="md:hidden border-t border-gray-200 bg-white shadow-lg"
      >
        <div class="px-2 pt-2 pb-3 space-y-1">
          <Link
            href="/dashboard"
            @click="mobileMenuOpen = false"
            class="block px-3 py-2 rounded-md text-base font-medium"
            :class="isActiveMobile('/dashboard')"
          >
            หน้าหลัก
          </Link>

          <Link
            v-if="hasRole(['Admin', 'Manager'])"
            href="/menu"
            @click="mobileMenuOpen = false"
            class="block px-3 py-2 rounded-md text-base font-medium"
            :class="isActiveMobile('/menu')"
          >
            จัดการเมนู
          </Link>

          <Link
            href="/tables"
            @click="mobileMenuOpen = false"
            class="block px-3 py-2 rounded-md text-base font-medium"
            :class="isActiveMobile('/tables')"
          >
            จัดการโต๊ะ
          </Link>

          <Link
            href="/takeaway"
            @click="mobileMenuOpen = false"
            class="block px-3 py-2 rounded-md text-base font-medium"
            :class="isActiveMobile('/takeaway')"
          >
            ออเดอร์กลับบ้าน
          </Link>

          <Link
            v-if="hasRole(['Admin', 'Manager'])"
            href="/kitchen"
            @click="mobileMenuOpen = false"
            class="block px-3 py-2 rounded-md text-base font-medium"
            :class="isActiveMobile('/kitchen')"
          >
            จอครัว
          </Link>

          <Link
            v-if="hasRole(['Admin', 'Manager'])"
            href="/reports"
            @click="mobileMenuOpen = false"
            class="block px-3 py-2 rounded-md text-base font-medium"
            :class="isActiveMobile('/reports')"
          >
            รายงาน
          </Link>
        </div>

        <!-- Mobile User Menu -->
        <div class="pt-3 pb-3 border-t border-gray-200 bg-gray-50">
          <div class="px-4 mb-3">
            <div class="text-sm font-semibold text-gray-800">{{ authUser?.name }}</div>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">
                {{ userRole }}
              </span>
              <span class="text-xs text-gray-500">{{ authUser?.email }}</span>
            </div>
          </div>
          <div class="px-2">
            <button
              @click="handleLogout"
              class="flex items-center w-full px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              ออกจากระบบ
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="py-4 md:py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const authStore = useAuthStore();
const page = usePage();
const mobileMenuOpen = ref(false);
const currentTime = ref('');

// Get user data from Inertia props (always up-to-date)
const authUser = computed(() => (page.props.auth as any)?.user || null);
const userRole = computed(() => authUser.value?.role?.name || null);

// Helper function to check if user has role
const hasRole = (roles: string | string[]): boolean => {
  if (!userRole.value) return false;
  const roleArray = Array.isArray(roles) ? roles : [roles];
  return roleArray.includes(userRole.value);
};

// Update time every second
let timeInterval: number;

const updateTime = () => {
  const now = new Date();
  const day = now.getDate();
  const year = now.getFullYear() + 543; // Convert to Buddhist calendar
  const hours = now.getHours().toString().padStart(2, '0');
  const minutes = now.getMinutes().toString().padStart(2, '0');
  currentTime.value = `จ. ${day} พ.ย. ${year} ${hours}:${minutes}`;
};

onMounted(() => {
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval);
});

const currentPath = computed(() => page.url);

const isActive = (path: string) => {
  return currentPath.value === path
    ? 'bg-blue-50 text-blue-600'
    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50';
};

const isActiveMobile = (path: string) => {
  return currentPath.value === path
    ? 'bg-blue-50 text-blue-600'
    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
};

const handleLogout = async () => {
  await authStore.logout();
  router.visit('/login');
};
</script>
