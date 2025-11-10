<template>
    <AuthenticatedLayout>
        <div class="py-6 bg-gray-900 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-white">Kitchen Display System</h1>
                    <div class="text-white text-sm">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full animate-pulse mr-2"></span>
                        Live Updates
                    </div>
                </div>

                <!-- Active Orders Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="order in sortedOrders"
                        :key="order.id"
                        class="bg-white rounded-lg shadow-lg p-6"
                    >
                        <!-- Order Header -->
                        <div class="flex justify-between items-start mb-4 pb-4 border-b">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Order #{{ order.id }}</h2>
                                <p class="text-sm text-gray-600">Table: {{ order.table?.number }}</p>
                                <p class="text-sm text-gray-600">
                                    Time: {{ formatTime(order.created_at) }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    'px-3 py-1 rounded-full text-sm font-semibold',
                                    order.status === 'preparing'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : order.status === 'ready'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-blue-100 text-blue-800',
                                ]"
                            >
                                {{ order.status }}
                            </span>
                        </div>

                        <!-- Order Items -->
                        <div class="space-y-3 mb-4">
                            <div
                                v-for="item in order.order_items"
                                :key="item.id"
                                class="p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <p class="font-semibold text-gray-900">
                                        {{ item.menu_item?.name }}
                                    </p>
                                    <span class="text-lg font-bold text-blue-600">x{{ item.quantity }}</span>
                                </div>
                                <p v-if="item.notes" class="text-sm text-red-600 font-medium">
                                    Note: {{ item.notes }}
                                </p>
                                <div v-if="item.modifiers && item.modifiers.length > 0" class="mt-2">
                                    <p class="text-xs text-gray-500 font-semibold">Modifiers:</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span
                                            v-for="modifier in item.modifiers"
                                            :key="modifier.id"
                                            class="inline-block px-2 py-0.5 bg-gray-200 text-gray-700 text-xs rounded"
                                        >
                                            {{ modifier.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <button
                                v-if="order.status === 'pending'"
                                @click="updateOrderStatus(order.id, 'preparing')"
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg font-semibold"
                            >
                                Start Preparing
                            </button>
                            <button
                                v-if="order.status === 'preparing'"
                                @click="updateOrderStatus(order.id, 'ready')"
                                class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-semibold"
                            >
                                Mark as Ready
                            </button>
                            <button
                                v-if="order.status === 'ready'"
                                class="w-full bg-gray-300 text-gray-700 py-2 rounded-lg font-semibold cursor-not-allowed"
                                disabled
                            >
                                ✓ Ready for Pickup
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="ordersStore.activeOrders.length === 0" class="text-center py-12">
                    <p class="text-gray-400 text-lg">No active orders</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, computed } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { useOrdersStore } from '@/stores/orders';

const ordersStore = useOrdersStore();

// Computed property to always get the latest sorted orders
const sortedOrders = computed(() => {
    // Return a sorted copy to ensure reactivity
    return [...ordersStore.activeOrders];
});

const formatTime = (timestamp: string) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const updateOrderStatus = async (orderId: number, status: string) => {
    try {
        await ordersStore.updateOrder(orderId, [], status);
    } catch (error) {
        console.error('Failed to update order status:', error);
        alert('Failed to update order status');
    }
};

// Real-time updates with Laravel Echo (to be implemented)
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

let refreshInterval: number;

onMounted(async () => {
    // Load initial orders
    await ordersStore.fetchActiveOrders();

    // Setup polling for updates (replace with Laravel Echo)
    refreshInterval = window.setInterval(() => {
        ordersStore.fetchActiveOrders();
    }, 10000); // Refresh every 10 seconds

    // TODO: Setup Laravel Echo
    // window.Echo.private('kitchen-channel')
    //     .listen('NewOrderPlaced', (e) => {
    //         ordersStore.addNewOrder(e.order);
    //     });
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    // TODO: Leave Echo channel
    // window.Echo.leave('kitchen-channel');
});
</script>
