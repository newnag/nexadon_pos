<template>
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">รายงานและสถิติ</h1>

                <!-- Date Range Filter -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                        {{ error }}
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">จากวันที่</label>
                            <input
                                v-model="dateRange.from"
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                :disabled="loading"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ถึงวันที่</label>
                            <input
                                v-model="dateRange.to"
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                :disabled="loading"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="loadReports"
                                :disabled="loading"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ loading ? 'กำลังโหลด...' : 'สร้างรายงาน' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">ยอดขายรวม</p>
                                <p class="text-2xl font-bold text-blue-600">฿{{ summary.totalRevenue }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">จำนวนออเดอร์</p>
                                <p class="text-2xl font-bold text-green-600">{{ summary.totalOrders }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">ยอดเฉลี่ย/ออเดอร์</p>
                                <p class="text-2xl font-bold text-purple-600">฿{{ summary.avgOrderValue }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">จำนวนรายการที่ขาย</p>
                                <p class="text-2xl font-bold text-orange-600">{{ summary.totalItemsSold }}</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Selling Items -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">รายการขายดี</h2>
                    <div v-if="topSellingItems.length === 0" class="text-center py-8 text-gray-500">
                        ไม่มีข้อมูลในช่วงวันที่เลือก
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4">อันดับ</th>
                                    <th class="text-left py-3 px-4">ชื่อรายการ</th>
                                    <th class="text-left py-3 px-4">หมวดหมู่</th>
                                    <th class="text-right py-3 px-4">จำนวนที่ขาย</th>
                                    <th class="text-right py-3 px-4">ยอดขาย</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, index) in topSellingItems"
                                    :key="item.id"
                                    class="border-b hover:bg-gray-50"
                                >
                                    <td class="py-3 px-4">{{ index + 1 }}</td>
                                    <td class="py-3 px-4 font-medium">{{ item.name }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ item.category }}</td>
                                    <td class="py-3 px-4 text-right">{{ item.quantity }}</td>
                                    <td class="py-3 px-4 text-right font-semibold">฿{{ item.revenue }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Revenue by Category -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">ยอดขายแยกตามหมวดหมู่</h2>
                    <div v-if="revenueByCategory.length === 0" class="text-center py-8 text-gray-500">
                        ไม่มีข้อมูลในช่วงวันที่เลือก
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="category in revenueByCategory"
                            :key="category.name"
                            class="flex items-center"
                        >
                            <div class="w-32 text-sm font-medium text-gray-700">{{ category.name }}</div>
                            <div class="flex-1 mx-4">
                                <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                                    <div
                                        class="bg-blue-600 h-full rounded-full"
                                        :style="{ width: `${category.percentage}%` }"
                                    ></div>
                                </div>
                            </div>
                            <div class="w-32 text-right text-sm font-semibold">฿{{ category.revenue }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import api from '@/lib/api';

const dateRange = ref({
    from: new Date(new Date().setDate(new Date().getDate() - 7)).toISOString().split('T')[0],
    to: new Date().toISOString().split('T')[0],
});

const summary = ref({
    totalRevenue: '0.00',
    totalOrders: 0,
    avgOrderValue: '0.00',
    totalItemsSold: 0,
});

const topSellingItems = ref<any[]>([]);
const revenueByCategory = ref<any[]>([]);
const loading = ref(false);
const error = ref('');

const loadReports = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const response = await api.get('/reports/sales', {
            params: {
                from: dateRange.value.from,
                to: dateRange.value.to,
            }
        });
        
        summary.value = response.data.summary;
        topSellingItems.value = response.data.topSellingItems;
        revenueByCategory.value = response.data.revenueByCategory;
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to load report data';
        console.error('Failed to load reports:', err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadReports();
});
</script>
