<template>
  <AuthLayout>
    <div>
      <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
        เข้าสู่ระบบ
      </h2>

      <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
        <p class="text-sm text-red-600">{{ error }}</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            อีเมล
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">
            รหัสผ่าน
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
          </button>
        </div>
      </form>

      <div class="mt-6">
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">ข้อมูลทดสอบ</span>
          </div>
        </div>

        <div class="mt-4 text-xs text-gray-600 space-y-1">
          <p><strong>ผู้ดูแลระบบ:</strong> admin@nexadon.com / password</p>
          <p><strong>ผู้จัดการ:</strong> manager@nexadon.com / password</p>
          <p><strong>แคชเชียร์:</strong> cashier@nexadon.com / password</p>
          <p><strong>พนักงานเสิร์ฟ:</strong> waiter@nexadon.com / password</p>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import AuthLayout from '@/layouts/AuthLayout.vue';

const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref<string | null>(null);

const handleLogin = async () => {
  loading.value = true;
  error.value = null;

  const result = await authStore.login(form.email, form.password);

  if (result.success) {
    router.visit('/dashboard');
  } else {
    error.value = result.error || 'เข้าสู่ระบบไม่สำเร็จ';
  }

  loading.value = false;
};
</script>
