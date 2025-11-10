import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/lib/api';

export interface Order {
    id: number;
    table: {
        id: number;
        number: string;
        status: string;
    };
    user: {
        id: number;
        name: string;
        role: {
            name: string;
        };
    };
    status: string;
    total_amount: string;
    order_items: OrderItem[];
    payment: Payment | null;
    created_at: string;
    updated_at: string;
}

export interface OrderItem {
    id: number;
    menu_item: {
        id: number;
        name: string;
        price: string;
        category: {
            name: string;
        };
    };
    quantity: number;
    notes: string | null;
    modifiers: Array<{
        id: number;
        name: string;
        price_change: string;
    }>;
    subtotal: string;
}

export interface Payment {
    id: number;
    payment_method: string;
    amount: string;
    created_at: string;
}

export const useOrdersStore = defineStore('orders', () => {
    // State
    const activeOrders = ref<Order[]>([]);
    const currentOrder = ref<Order | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    // Actions
    const fetchActiveOrders = async () => {
        try {
            loading.value = true;
            error.value = null;
            const response = await api.get('/orders/active');
            // Sort by created_at descending (newest first)
            activeOrders.value = response.data.data.sort((a: Order, b: Order) => {
                return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
            });
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch orders';
        } finally {
            loading.value = false;
        }
    };

    const fetchOrder = async (orderId: number) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await api.get(`/orders/${orderId}`);
            currentOrder.value = response.data.data;
            return response.data.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch order';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createOrder = async (tableId: number, orderItems: any[]) => {
        try {
            loading.value = true;
            error.value = null;
            const response = await api.post('/orders', {
                table_id: tableId,
                order_items: orderItems,
            });
            
            // Add to active orders
            activeOrders.value.unshift(response.data.data);
            
            return { success: true, data: response.data.data };
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to create order';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    };

    const updateOrder = async (orderId: number, orderItems: any[], status?: string) => {
        try {
            loading.value = true;
            error.value = null;
            
            const payload: any = { order_items: orderItems };
            if (status) {
                payload.status = status;
            }
            
            const response = await api.put(`/orders/${orderId}`, payload);
            
            // Update in active orders list while maintaining sort order
            const index = activeOrders.value.findIndex(o => o.id === orderId);
            if (index !== -1) {
                activeOrders.value[index] = response.data.data;
                // Re-sort after update
                activeOrders.value.sort((a: Order, b: Order) => {
                    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
                });
            }
            
            // Update current order if it's the same
            if (currentOrder.value?.id === orderId) {
                currentOrder.value = response.data.data;
            }
            
            return { success: true, data: response.data.data };
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to update order';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    };

    const addNewOrder = (order: Order) => {
        // Add order if not already in list
        const exists = activeOrders.value.find(o => o.id === order.id);
        if (!exists) {
            activeOrders.value.unshift(order);
        }
    };

    const removeOrder = (orderId: number) => {
        activeOrders.value = activeOrders.value.filter(o => o.id !== orderId);
        if (currentOrder.value?.id === orderId) {
            currentOrder.value = null;
        }
    };

    const updateOrderStatus = (orderId: number, status: string) => {
        const order = activeOrders.value.find(o => o.id === orderId);
        if (order) {
            order.status = status;
        }
        if (currentOrder.value?.id === orderId) {
            currentOrder.value.status = status;
        }
    };

    return {
        // State
        activeOrders,
        currentOrder,
        loading,
        error,

        // Actions
        fetchActiveOrders,
        fetchOrder,
        createOrder,
        updateOrder,
        addNewOrder,
        removeOrder,
        updateOrderStatus,
    };
});
