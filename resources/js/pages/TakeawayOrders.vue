<template>
    <DefaultLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">🥡 ออเดอร์กลับบ้าน</h1>
                    <button
                        @click="createNewOrder"
                        class="px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition shadow-lg flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        สร้างออเดอร์ใหม่
                    </button>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600"></div>
                    <p class="mt-4 text-gray-600">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Active Orders Grid -->
                <div v-else-if="activeOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="order in activeOrders"
                        :key="order.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
                    >
                        <!-- Order Header -->
                        <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4 rounded-t-lg">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="text-2xl font-bold">{{ order.customer_name }}</h3>
                                    <p class="text-orange-100 text-sm">{{ order.customer_phone }}</p>
                                </div>
                                <span class="bg-white text-orange-600 px-3 py-1 rounded-full text-sm font-semibold">
                                    #{{ order.id }}
                                </span>
                            </div>
                            <p class="text-orange-100 text-xs">{{ formatDateTime(order.created_at) }}</p>
                        </div>

                        <!-- Order Items -->
                        <div class="p-4">
                            <div class="space-y-2 mb-4 max-h-60 overflow-y-auto">
                                <div
                                    v-for="item in order.order_items"
                                    :key="item.id"
                                    class="flex justify-between items-start text-sm"
                                >
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">
                                            {{ item.menu_item.name }}
                                            <span class="text-gray-500">× {{ item.quantity }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ item.menu_item.category.name }}</p>
                                        <div v-if="item.notes" class="text-xs text-gray-500 italic mt-1">
                                            หมายเหตุ: {{ item.notes }}
                                        </div>
                                    </div>
                                    <span class="font-semibold text-gray-900">฿{{ item.subtotal }}</span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="mb-4">
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 text-sm font-semibold rounded-full',
                                        order.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        order.status === 'preparing' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-blue-100 text-blue-800'
                                    ]"
                                >
                                    {{ getStatusText(order.status) }}
                                </span>
                            </div>

                            <!-- Total Amount -->
                            <div class="border-t pt-4 mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900">ยอดรวม</span>
                                    <span class="text-2xl font-bold text-orange-600">
                                        ฿{{ parseFloat(order.total_amount).toFixed(2) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Payment Status -->
                            <div v-if="order.payment" class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-center text-green-800">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold">ชำระเงินแล้ว</p>
                                        <p class="text-xs">{{ formatPaymentMethod(order.payment.payment_method) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button
                                    v-if="!order.payment"
                                    @click="goToBilling(order.id)"
                                    class="flex-1 px-4 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    ชำระเงิน
                                </button>
                                <button
                                    v-if="!order.payment"
                                    @click="editOrder(order.id)"
                                    class="px-4 py-3 bg-orange-100 text-orange-700 font-semibold rounded-lg hover:bg-orange-200 transition flex items-center justify-center"
                                    title="แก้ไข/เพิ่มรายการ"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="viewOrderDetails(order)"
                                    class="px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition flex items-center justify-center"
                                    :class="order.payment ? 'flex-1' : ''"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-gray-400 text-6xl mb-4">🥡</div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">ยังไม่มีออเดอร์กลับบ้าน</h3>
                    <p class="text-gray-500 mb-6">คลิก "สร้างออเดอร์ใหม่" เพื่อเริ่มรับออเดอร์</p>
                    <button
                        @click="createNewOrder"
                        class="px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition"
                    >
                        สร้างออเดอร์ใหม่
                    </button>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <Teleport to="body">
            <div
                v-if="selectedOrder"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeModal"
            >
                <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-orange-600 text-white p-6 rounded-t-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold">{{ selectedOrder.customer_name }}</h2>
                                <p class="text-orange-100">{{ selectedOrder.customer_phone }}</p>
                                <p class="text-orange-100 text-sm mt-1">ออเดอร์ #{{ selectedOrder.id }}</p>
                            </div>
                            <button
                                @click="closeModal"
                                class="text-white hover:text-orange-100 text-3xl"
                            >
                                ×
                            </button>
                        </div>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-6">
                        <!-- Order Info -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-600">วันที่สร้าง</p>
                                <p class="font-semibold">{{ formatDateTime(selectedOrder.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">สถานะ</p>
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 text-sm font-semibold rounded-full',
                                        selectedOrder.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        selectedOrder.status === 'preparing' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-blue-100 text-blue-800'
                                    ]"
                                >
                                    {{ getStatusText(selectedOrder.status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">พนักงาน</p>
                                <p class="font-semibold">{{ selectedOrder.user.name }}</p>
                            </div>
                            <div v-if="selectedOrder.payment">
                                <p class="text-sm text-gray-600">การชำระเงิน</p>
                                <p class="font-semibold text-green-600">
                                    {{ formatPaymentMethod(selectedOrder.payment.payment_method) }}
                                </p>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3">รายการอาหาร</h3>
                            <div class="border rounded-lg overflow-hidden">
                                <div class="divide-y">
                                    <div
                                        v-for="item in selectedOrder.order_items"
                                        :key="item.id"
                                        class="p-4"
                                    >
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-900">
                                                    {{ item.menu_item.name }}
                                                    <span class="text-gray-500 font-normal">× {{ item.quantity }}</span>
                                                </h4>
                                                <p class="text-sm text-gray-600">{{ item.menu_item.category.name }}</p>
                                                
                                                <!-- Modifiers -->
                                                <div v-if="item.modifiers && item.modifiers.length > 0" class="mt-1">
                                                    <p class="text-xs text-gray-500">
                                                        ตัวเลือก:
                                                        <span v-for="(mod, index) in item.modifiers" :key="mod.id">
                                                            {{ mod.name }} (+฿{{ mod.price_change }}){{ index < item.modifiers.length - 1 ? ', ' : '' }}
                                                        </span>
                                                    </p>
                                                </div>

                                                <!-- Notes -->
                                                <div v-if="item.notes" class="mt-1">
                                                    <p class="text-xs text-gray-500 italic">หมายเหตุ: {{ item.notes }}</p>
                                                </div>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <p class="text-lg font-semibold text-gray-900">฿{{ item.subtotal }}</p>
                                                <p class="text-xs text-gray-500">฿{{ item.menu_item.price }} ต่อรายการ</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold">ยอดรวมทั้งหมด</span>
                                <span class="text-2xl font-bold text-orange-600">
                                    ฿{{ parseFloat(selectedOrder.total_amount).toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button
                                v-if="!selectedOrder.payment"
                                @click="goToBilling(selectedOrder.id)"
                                class="flex-1 px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition"
                            >
                                ชำระเงิน
                            </button>
                            <button
                                v-if="!selectedOrder.payment"
                                @click="editOrder(selectedOrder.id)"
                                class="px-6 py-3 bg-orange-100 text-orange-700 font-semibold rounded-lg hover:bg-orange-200 transition"
                            >
                                แก้ไข/เพิ่มรายการ
                            </button>
                            <button
                                @click="closeModal"
                                class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition"
                            >
                                ปิด
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </DefaultLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import api from '@/lib/api';

interface MenuItem {
    id: number;
    name: string;
    price: string;
    category: {
        id: number;
        name: string;
    };
}

interface Modifier {
    id: number;
    name: string;
    price_change: string;
}

interface OrderItem {
    id: number;
    quantity: number;
    notes: string | null;
    subtotal: string;
    menu_item: MenuItem;
    modifiers: Modifier[];
}

interface Order {
    id: number;
    status: string;
    order_type: string;
    customer_name: string;
    customer_phone: string;
    total_amount: string;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
    order_items: OrderItem[];
    payment?: {
        id: number;
        payment_method: string;
        amount: string;
    };
}

const activeOrders = ref<Order[]>([]);
const selectedOrder = ref<Order | null>(null);
const loading = ref(false);

// Fetch active takeaway orders
const fetchTakeawayOrders = async () => {
    loading.value = true;
    try {
        const response = await api.get('/orders', {
            params: {
                order_type: 'takeaway',
                status: 'pending,preparing,completed',
            }
        });
        
        // Filter only unpaid or recently completed orders
        activeOrders.value = response.data.data.filter((order: Order) => {
            if (!order.payment) return true; // Show unpaid orders
            
            // Show paid orders from today
            const orderDate = new Date(order.created_at);
            const today = new Date();
            return orderDate.toDateString() === today.toDateString();
        });
    } catch (error) {
        console.error('Failed to fetch takeaway orders:', error);
    } finally {
        loading.value = false;
    }
};

// Create new takeaway order
const createNewOrder = () => {
    router.visit('/takeaway/new');
};

// Edit existing order
const editOrder = (orderId: number) => {
    router.visit(`/takeaway/${orderId}/edit`);
};

// Go to billing page
const goToBilling = (orderId: number) => {
    router.visit(`/billing/${orderId}`);
};

// View order details
const viewOrderDetails = (order: Order) => {
    selectedOrder.value = order;
};

// Close modal
const closeModal = () => {
    selectedOrder.value = null;
};

// Format date time
const formatDateTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Get status text
const getStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
        'pending': 'รอดำเนินการ',
        'preparing': 'กำลังเตรียม',
        'completed': 'เสร็จสิ้น',
        'cancelled': 'ยกเลิก',
    };
    return statusMap[status] || status;
};

// Format payment method
const formatPaymentMethod = (method: string) => {
    const methodMap: Record<string, string> = {
        'Cash': 'เงินสด',
        'cash': 'เงินสด',
        'QR Payment': 'QR Payment',
        'qr_payment': 'QR Payment',
        'Credit Card': 'บัตรเครดิต',
        'credit_card': 'บัตรเครดิต',
    };
    return methodMap[method] || method;
};

// Auto refresh every 30 seconds
let refreshInterval: ReturnType<typeof setInterval>;

onMounted(() => {
    fetchTakeawayOrders();
    
    // Auto refresh
    refreshInterval = setInterval(() => {
        fetchTakeawayOrders();
    }, 30000);
});

// Cleanup on unmount
import { onUnmounted } from 'vue';
onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
