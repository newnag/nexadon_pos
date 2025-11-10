<template>
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Billing & Payment</h1>

                <!-- Order Selection -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Order</label>
                    <select
                        v-model="selectedOrderId"
                        @change="loadOrder"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    >
                        <option value="">-- Select an order --</option>
                        <option v-for="order in activeOrders" :key="order.id" :value="order.id">
                            Order #{{ order.id }} - Table {{ order.table?.table_number }}
                        </option>
                    </select>
                </div>

                <!-- Order Details -->
                <div v-if="currentOrder" class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Order Details</h2>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Order ID: <span class="font-semibold">#{{ currentOrder.id }}</span></p>
                        <p class="text-sm text-gray-600">Table: <span class="font-semibold">{{ currentOrder.table?.table_number }}</span></p>
                        <p class="text-sm text-gray-600">Status: <span class="font-semibold capitalize">{{ currentOrder.status }}</span></p>
                    </div>

                    <!-- Order Items -->
                    <div class="border-t pt-4">
                        <h3 class="font-semibold mb-3">Items</h3>
                        <div class="space-y-2">
                            <div
                                v-for="item in currentOrder.order_items"
                                :key="item.id"
                                class="flex justify-between items-center py-2"
                            >
                                <div class="flex-1">
                                    <p class="font-medium">{{ item.menu_item?.name }}</p>
                                    <p class="text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                                    <p v-if="item.notes" class="text-sm text-gray-500 italic">Note: {{ item.notes }}</p>
                                </div>
                                <p class="font-semibold">฿{{ calculateItemTotal(item) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-t mt-4 pt-4">
                        <div class="flex justify-between items-center text-2xl font-bold">
                            <span>Total:</span>
                            <span class="text-blue-600">฿{{ currentOrder.total_amount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div v-if="currentOrder && !currentOrder.payment" class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Process Payment</h2>
                    
                    <form @submit.prevent="processPayment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select
                                v-model="paymentMethod"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                            >
                                <option value="">-- Select payment method --</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="qr_payment">QR Payment</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                            <input
                                v-model.number="paymentAmount"
                                type="number"
                                step="0.01"
                                required
                                readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="submitting"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold disabled:opacity-50"
                        >
                            {{ submitting ? 'Processing...' : 'Process Payment' }}
                        </button>
                    </form>
                </div>

                <!-- Already Paid -->
                <div v-if="currentOrder && currentOrder.payment" class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-green-800 mb-2">✓ Payment Completed</h2>
                    <p class="text-green-700">This order has been paid.</p>
                    <p class="text-sm text-green-600 mt-2">
                        Payment Method: <span class="font-semibold">{{ currentOrder.payment.payment_method }}</span>
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import api from '@/lib/api';

interface Order {
    id: number;
    status: string;
    total_amount: string;
    table?: {
        table_number: string;
    };
    order_items: OrderItem[];
    payment?: {
        payment_method: string;
        amount: string;
    };
}

interface OrderItem {
    id: number;
    quantity: number;
    notes?: string;
    menu_item?: {
        name: string;
        price: string;
    };
}

const activeOrders = ref<Order[]>([]);
const currentOrder = ref<Order | null>(null);
const selectedOrderId = ref('');
const paymentMethod = ref('');
const paymentAmount = ref(0);
const submitting = ref(false);

const fetchActiveOrders = async () => {
    try {
        const response = await api.get('/orders/active');
        activeOrders.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch active orders:', error);
    }
};

const loadOrder = async () => {
    if (!selectedOrderId.value) {
        currentOrder.value = null;
        return;
    }

    try {
        const response = await api.get(`/api/orders/${selectedOrderId.value}`);
        currentOrder.value = response.data.data;
        if (currentOrder.value) {
            paymentAmount.value = parseFloat(currentOrder.value.total_amount);
        }
    } catch (error) {
        console.error('Failed to load order:', error);
        alert('Failed to load order details');
    }
};

const calculateItemTotal = (item: OrderItem) => {
    if (!item.menu_item) return '0.00';
    const price = parseFloat(item.menu_item.price);
    return (price * item.quantity).toFixed(2);
};

const processPayment = async () => {
    if (!currentOrder.value || !paymentMethod.value) return;

    submitting.value = true;
    try {
        const paymentData = {
            order_id: currentOrder.value.id,
            payment_method: paymentMethod.value,
            amount: paymentAmount.value,
        };

        await api.post('/payments', paymentData);

        alert('Payment processed successfully!');
        
        // Reload order to show payment details
        await loadOrder();
        
        // Reset form
        paymentMethod.value = '';
        
        // Refresh active orders list
        await fetchActiveOrders();
    } catch (error: any) {
        console.error('Failed to process payment:', error);
        const message = error.response?.data?.message || 'Failed to process payment';
        alert(message);
    } finally {
        submitting.value = false;
    }
};

watch(paymentAmount, (newAmount) => {
    if (currentOrder.value) {
        currentOrder.value.total_amount = newAmount.toFixed(2);
    }
});

onMounted(() => {
    fetchActiveOrders();
});
</script>
