<template>
    <DefaultLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">จัดการโต๊ะ</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            คลิกที่โต๊ะเพื่อดูหรือสร้างออเดอร์
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="goToTableManagement"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-700 focus:outline-none"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            ตั้งค่าโต๊ะ
                        </button>
                        <button
                            @click="fetchTables"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            รีเฟรช
                        </button>
                    </div>
                </div>

                <!-- Status Legend -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">สถานะโต๊ะ:</h3>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                            <span class="text-sm text-gray-600">ว่าง ({{ statusCounts.available }})</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                            <span class="text-sm text-gray-600">มีลูกค้า ({{ statusCounts.occupied }})</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-yellow-500 rounded mr-2"></div>
                            <span class="text-sm text-gray-600">จอง ({{ statusCounts.reserved }})</span>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">กำลังโหลดข้อมูลโต๊ะ...</p>
                </div>

                <!-- Tables Grid -->
                <div v-else-if="tables.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    <div
                        v-for="table in tables"
                        :key="table.id"
                        @click="handleTableClick(table)"
                        :class="[
                            'relative rounded-lg shadow-md p-6 cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-xl',
                            getTableStatusClass(table.status),
                        ]"
                    >
                        <!-- Table Icon -->
                        <div class="flex justify-center mb-3">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Table Number -->
                        <h3 class="text-center text-xl font-bold text-white mb-2">
                            {{ table.table_number }}
                        </h3>

                        <!-- Status Badge -->
                        <div class="text-center">
                            <span 
                                :class="[
                                    'inline-block px-3 py-1 text-xs font-bold rounded-full',
                                    table.status === 'available' ? 'bg-white text-green-700' :
                                    table.status === 'occupied' ? 'bg-white text-red-700' :
                                    'bg-white text-yellow-700'
                                ]"
                            >
                                {{ getStatusText(table.status) }}
                            </span>
                        </div>

                        <!-- Active Order Info -->
                        <div v-if="table.active_order" class="mt-3 pt-3 border-t border-white border-opacity-30">
                            <p class="text-xs text-white text-center">
                                ออเดอร์ #{{ table.active_order.id }}
                            </p>
                            <p class="text-xs text-white text-center font-semibold">
                                ฿{{ table.active_order.total_amount }}
                            </p>
                        </div>

                        <!-- Click Indicator -->
                        <div class="absolute top-2 right-2">
                            <svg class="w-5 h-5 text-white opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">ไม่พบโต๊ะ</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        กรุณาเพิ่มโต๊ะในระบบ
                    </p>
                </div>
            </div>
        </div>
    </DefaultLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import api from '@/lib/api';

interface ActiveOrder {
    id: number;
    status: string;
    total_amount: string;
    created_at: string;
    items_count: number;
}

interface Table {
    id: number;
    table_number: string;
    status: 'available' | 'occupied' | 'reserved';
    created_at: string;
    updated_at: string;
    active_order: ActiveOrder | null;
}

const tables = ref<Table[]>([]);
const loading = ref(false);

// Computed: Count tables by status
const statusCounts = computed(() => {
    return {
        available: tables.value.filter(t => t.status === 'available').length,
        occupied: tables.value.filter(t => t.status === 'occupied').length,
        reserved: tables.value.filter(t => t.status === 'reserved').length,
    };
});

// Fetch tables from API
const fetchTables = async () => {
    loading.value = true;
    try {
        const response = await api.get('/tables');
        tables.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch tables:', error);
        alert('ไม่สามารถโหลดข้อมูลโต๊ะได้ กรุณาลองใหม่อีกครั้ง');
    } finally {
        loading.value = false;
    }
};

// Navigate to table management page
const goToTableManagement = () => {
    router.visit('/tables/manage');
};

// Get background color class based on status
const getTableStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        available: 'bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700',
        occupied: 'bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700',
        reserved: 'bg-gradient-to-br from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700',
    };
    return classes[status] || 'bg-gray-500';
};

// Capitalize status text
const getStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
        available: 'ว่าง',
        occupied: 'มีลูกค้า',
        reserved: 'จอง',
    };
    return statusMap[status] || status;
};

// Handle table click
const handleTableClick = (table: Table) => {
    if (table.status === 'available') {
        // Navigate to order taking page with table_id
        router.visit(`/orders?table_id=${table.id}`);
    } else if (table.status === 'occupied' && table.active_order) {
        // Navigate to order taking page with existing order
        router.visit(`/orders?table_id=${table.id}&order_id=${table.active_order.id}`);
    } else if (table.status === 'reserved') {
        // Show info about reserved table
        alert(`โต๊ะ ${table.table_number} ถูกจองแล้ว กรุณาติดต่อผู้จัดการเพื่อเปลี่ยนสถานะ`);
    }
};

// Load tables on mount
onMounted(() => {
    fetchTables();
});
</script>
