<template>
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">ประวัติการสั่งอาหาร</h1>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ค้นหา</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="หมายเลขออเดอร์หรือโต๊ะ"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                @input="debouncedSearch"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                            <select
                                v-model="filters.status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                @change="() => loadOrders()"
                            >
                                <option value="all">ทั้งหมด</option>
                                <option value="pending">รอดำเนินการ</option>
                                <option value="completed">สำเร็จ</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">จากวันที่</label>
                            <input
                                v-model="filters.from"
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                @change="() => loadOrders()"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ถึงวันที่</label>
                            <input
                                v-model="filters.to"
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                @change="() => loadOrders()"
                            />
                        </div>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div v-if="loading" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">กำลังโหลดข้อมูล...</p>
                    </div>

                    <div v-else-if="orders.length === 0" class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">📋</div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">ไม่พบรายการสั่งอาหาร</h3>
                        <p class="text-gray-500">ลองปรับเปลี่ยนตัวกรองข้อมูล</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        หมายเลขออเดอร์
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        โต๊ะ
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        พนักงาน
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        สถานะ
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ยอดรวม
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        วันที่
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ดูรายละเอียด
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="order in orders"
                                    :key="order.id"
                                    class="hover:bg-gray-50 transition"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ order.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ order.table.table_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ order.user.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                                'bg-green-100 text-green-800': order.status === 'completed',
                                                'bg-red-100 text-red-800': order.status === 'cancelled'
                                            }"
                                        >
                                            {{ order.status === 'pending' ? 'รอดำเนินการ' : order.status === 'completed' ? 'สำเร็จ' : 'ยกเลิก' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ฿{{ parseFloat(order.total_amount).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button
                                            @click="viewOrderDetails(order.id)"
                                            class="text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            ดูรายละเอียด
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination.total > pagination.per_page" class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t">
                        <div class="text-sm text-gray-700">
                            แสดง {{ pagination.from }} ถึง {{ pagination.to }} จากทั้งหมด {{ pagination.total }} รายการ
                        </div>
                        <div class="flex space-x-2">
                            <button
                                v-for="page in visiblePages"
                                :key="page"
                                @click="goToPage(page)"
                                :disabled="page === pagination.current_page"
                                class="px-3 py-1 rounded text-sm transition"
                                :class="{
                                    'bg-blue-600 text-white': page === pagination.current_page,
                                    'bg-white text-gray-700 hover:bg-gray-100': page !== pagination.current_page
                                }"
                            >
                                {{ page }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <div
            v-if="selectedOrder"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeModal"
        >
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">ออเดอร์ #{{ selectedOrder.id }}</h2>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 text-2xl"
                    >
                        ×
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <!-- Order Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600">โต๊ะ</p>
                            <p class="font-semibold">{{ selectedOrder.table.table_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">พนักงาน</p>
                            <p class="font-semibold">{{ selectedOrder.waiter.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">สถานะ</p>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-yellow-100 text-yellow-800': selectedOrder.status === 'pending',
                                    'bg-green-100 text-green-800': selectedOrder.status === 'completed',
                                    'bg-red-100 text-red-800': selectedOrder.status === 'cancelled'
                                }"
                            >
                                {{ selectedOrder.status === 'pending' ? 'รอดำเนินการ' : selectedOrder.status === 'completed' ? 'สำเร็จ' : 'ยกเลิก' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">วันที่สร้าง</p>
                            <p class="font-semibold">{{ formatDate(selectedOrder.created_at) }}</p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">รายการอาหาร</h3>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">รายการ</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">หมวดหมู่</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">ราคา</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">จำนวน</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">รวม</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="item in selectedOrder.items" :key="item.id">
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ item.menu_item.name }}</div>
                                            <div v-if="item.modifiers.length > 0" class="text-xs text-gray-500">
                                                + {{ item.modifiers.map((m: any) => m.name).join(', ') }}
                                            </div>
                                            <div v-if="item.notes" class="text-xs text-gray-500 italic">
                                                หมายเหตุ: {{ item.notes }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ item.menu_item.category }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            ฿{{ parseFloat(item.menu_item.price).toFixed(2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-right">
                                            ฿{{ calculateItemTotal(item).toFixed(2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">ข้อมูลการชำระเงิน</h3>
                        <div class="border rounded-lg p-4 space-y-2">
                            <div v-for="payment in selectedOrder.payments" :key="payment.id" class="flex justify-between">
                                <span class="text-gray-600">
                                    {{ formatPaymentMethod(payment.payment_method) }}
                                    <span class="text-xs text-gray-400">{{ formatDate(payment.created_at) }}</span>
                                </span>
                                <span class="font-semibold">฿{{ parseFloat(payment.amount).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold">ยอดรวมทั้งหมด</span>
                            <span class="text-2xl font-bold text-blue-600">
                                ฿{{ parseFloat(selectedOrder.total_amount).toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import api from '@/lib/api';

interface Order {
    id: number;
    table: { id: number; table_number: string };
    user: { id: number; name: string };
    status: string;
    total_amount: string;
    created_at: string;
}

interface OrderDetail {
    id: number;
    table: { id: number; table_number: string };
    waiter: { id: number; name: string };
    status: string;
    total_amount: string;
    created_at: string;
    updated_at: string;
    items: any[];
    payments: any[];
}

const filters = ref({
    search: '',
    status: 'all',
    from: '',
    to: '',
});

const orders = ref<Order[]>([]);
const loading = ref(false);
const selectedOrder = ref<OrderDetail | null>(null);
const pagination = ref({
    current_page: 1,
    per_page: 20,
    total: 0,
    from: 0,
    to: 0,
    last_page: 1,
});

let searchTimeout: ReturnType<typeof setTimeout>;

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    
    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
    }
    
    return pages;
});

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadOrders();
    }, 500);
};

const loadOrders = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/orders/history', {
            params: {
                ...filters.value,
                page,
            },
        });
        
        orders.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to,
            last_page: response.data.last_page,
        };
    } catch (error) {
        console.error('Failed to load orders:', error);
    } finally {
        loading.value = false;
    }
};

const viewOrderDetails = async (orderId: number) => {
    try {
        const response = await api.get(`/orders/${orderId}/details`);
        selectedOrder.value = response.data.order;
    } catch (error) {
        console.error('Failed to load order details:', error);
        alert('Failed to load order details');
    }
};

const closeModal = () => {
    selectedOrder.value = null;
};

const goToPage = (page: number) => {
    loadOrders(page);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatPaymentMethod = (method: string) => {
    const methods: Record<string, string> = {
        cash: 'เงินสด',
        qr_payment: 'QR Payment',
        credit_card: 'บัตรเครดิต',
        debit_card: 'บัตรเดบิต',
    };
    return methods[method] || method;
};

const calculateItemTotal = (item: any) => {
    let total = parseFloat(item.menu_item.price) * item.quantity;
    
    if (item.modifiers && item.modifiers.length > 0) {
        item.modifiers.forEach((modifier: any) => {
            total += parseFloat(modifier.price_change) * item.quantity;
        });
    }
    
    return total;
};

onMounted(() => {
    loadOrders();
});
</script>
